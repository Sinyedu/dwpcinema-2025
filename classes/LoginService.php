<?php
declare(strict_types=1);

/**
 * The one place that decides whether a login attempt succeeds, for both the
 * user portal and the admin portal. Includes brute-force throttling.
 *
 * Throttling is per-session, so it slows scripted guessing from one client but is
 * not a substitute for an IP/account-keyed limiter (needs a table; see docs).
 */
final class LoginService
{
    public const MAX_FAILURES = 5;
    public const LOCKOUT_SECONDS = 900;
    private const GENERIC_ERROR = 'Invalid email or password.';
    // A valid bcrypt hash verified against when the email is unknown, so response
    // timing doesn't reveal which emails are registered.
    private const DUMMY_HASH = '$2y$12$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG';

    private array $session;
    /** @var callable */
    private $now;

    public function __construct(private User $users, private Auth $auth, array &$session, ?callable $now = null)
    {
        $this->session = &$session;
        $this->now = $now ?? 'time';
    }

    /** @return string|null an error message to show the user, or null on success */
    public function attempt(string $email, string $password, bool $asAdmin = false): ?string
    {
        if ($this->isLockedOut()) {
            return 'Too many failed attempts. Please try again in a few minutes.';
        }

        $user = $this->users->authenticate($email, $password);
        if ($user === null) {
            PasswordHasher::verify($password, self::DUMMY_HASH);
            return $this->fail();
        }

        $isAdmin = (int)($user['isAdmin'] ?? 0) === 1;
        if ($asAdmin && !$isAdmin) {
            return $this->fail();
        }
        if (!(int)($user['isActive'] ?? 1)) {
            return 'Your account has been deactivated. Please contact the site administrator.';
        }
        if (!$asAdmin && $isAdmin) {
            return 'Admins must log in through the admin portal.';
        }

        unset($this->session['login_throttle']);
        $this->users->touchLastActive((int)$user['userID']);
        $asAdmin ? $this->auth->loginAdmin($user) : $this->auth->loginUser($user);
        return null;
    }

    private function isLockedOut(): bool
    {
        $until = $this->session['login_throttle']['locked_until'] ?? 0;
        return $until > ($this->now)();
    }

    private function fail(): string
    {
        $state = $this->session['login_throttle'] ?? ['fails' => 0, 'locked_until' => 0];
        $state['fails']++;
        if ($state['fails'] >= self::MAX_FAILURES) {
            $state = ['fails' => 0, 'locked_until' => ($this->now)() + self::LOCKOUT_SECONDS];
        }
        $this->session['login_throttle'] = $state;
        return self::GENERIC_ERROR;
    }
}
