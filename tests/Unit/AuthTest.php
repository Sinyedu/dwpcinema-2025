<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class AuthTest extends TestCase
{
    private int $regenerations = 0;

    private function auth(array &$session): Auth
    {
        return new Auth($session, function (): void {
            $this->regenerations++;
        });
    }

    private function user(array $extra = []): array
    {
        return array_merge([
            'userID' => 7, 'firstName' => 'Ada', 'lastName' => 'Lovelace',
            'userEmail' => 'ada@example.com', 'avatar' => null,
        ], $extra);
    }

    public function testStartsLoggedOut(): void
    {
        $session = [];
        $auth = $this->auth($session);

        $this->assertFalse($auth->isUser());
        $this->assertFalse($auth->isAdmin());
        $this->assertNull($auth->userId());
    }

    public function testLoginUserStoresIdentityAndRegeneratesTheSessionId(): void
    {
        $session = [];
        $auth = $this->auth($session);

        $auth->loginUser($this->user());

        $this->assertTrue($auth->isUser());
        $this->assertFalse($auth->isAdmin());
        $this->assertSame(7, $auth->userId());
        $this->assertSame('Ada Lovelace', $session['user_name']);
        $this->assertSame('uploads/avatars/default.png', $session['user_avatar']);
        $this->assertSame(1, $this->regenerations, 'session id must be regenerated to prevent fixation');
    }

    public function testLoginRotatesTheCsrfToken(): void
    {
        $session = [];
        $csrf = new Csrf($session);
        $before = $csrf->token();

        $this->auth($session)->loginUser($this->user());

        $this->assertFalse($csrf->validate($before));
    }

    public function testLoginAdminIsSeparateFromUserLogin(): void
    {
        $session = [];
        $auth = $this->auth($session);

        $auth->loginAdmin($this->user(['userID' => 1]));

        $this->assertTrue($auth->isAdmin());
        $this->assertFalse($auth->isUser());
        $this->assertSame(1, $auth->adminId());
        $this->assertSame(1, $this->regenerations);
    }

    public function testLogoutClearsEverythingForBothRoles(): void
    {
        $session = [];
        $auth = $this->auth($session);
        $auth->loginUser($this->user());
        $auth->loginAdmin($this->user(['userID' => 1]));

        $auth->logout();

        $this->assertFalse($auth->isUser());
        $this->assertFalse($auth->isAdmin());
        $this->assertArrayNotHasKey('user_id', $session);
        $this->assertArrayNotHasKey('admin_id', $session);
    }

    public function testRefreshingProfileUpdatesDisplayDataOnly(): void
    {
        $session = [];
        $auth = $this->auth($session);
        $auth->loginUser($this->user());

        $auth->updateProfile('Grace Hopper', 'uploads/avatars/new.png');

        $this->assertSame('Grace Hopper', $session['user_name']);
        $this->assertSame('uploads/avatars/new.png', $session['user_avatar']);
        $this->assertSame(7, $auth->userId());
    }
}
