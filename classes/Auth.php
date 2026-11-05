<?php
declare(strict_types=1);

/**
 * Who is logged in. User and admin logins are independent keys in the same session.
 * Holds the session array by reference and takes the id-regeneration step as a callable,
 * so it is testable without PHP's real session machinery.
 */
final class Auth
{
    public const DEFAULT_AVATAR = 'uploads/avatars/default.png';

    private array $session;
    /** @var callable */
    private $regenerate;

    public function __construct(array &$session, ?callable $regenerate = null)
    {
        $this->session = &$session;
        $this->regenerate = $regenerate ?? static function (): void {
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_regenerate_id(true);
            }
        };
    }

    public function isUser(): bool
    {
        return isset($this->session['user_id']);
    }

    public function isAdmin(): bool
    {
        return isset($this->session['admin_id']);
    }

    public function userId(): ?int
    {
        return isset($this->session['user_id']) ? (int)$this->session['user_id'] : null;
    }

    public function adminId(): ?int
    {
        return isset($this->session['admin_id']) ? (int)$this->session['admin_id'] : null;
    }

    public function loginUser(array $user): void
    {
        $this->startFreshSession();
        $this->session['user_id'] = (int)$user['userID'];
        $this->session['user_name'] = $user['firstName'] . ' ' . $user['lastName'];
        $this->session['user_avatar'] = $user['avatar'] ?: self::DEFAULT_AVATAR;
    }

    public function loginAdmin(array $user): void
    {
        $this->startFreshSession();
        $this->session['admin_id'] = (int)$user['userID'];
        $this->session['admin_name'] = $user['firstName'];
    }

    public function updateProfile(string $displayName, string $avatar): void
    {
        $this->session['user_name'] = $displayName;
        $this->session['user_avatar'] = $avatar;
    }

    /** Clears the session array. Destroying the cookie/session is the caller's (page's) job. */
    public function logout(): void
    {
        $this->session = [];
    }

    private function startFreshSession(): void
    {
        ($this->regenerate)();
        // A new login invalidates any CSRF token issued to the anonymous session.
        (new Csrf($this->session))->rotate();
    }
}
