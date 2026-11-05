<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class LoginServiceTest extends TestCase
{
    private PDO $pdo;
    private array $session = [];
    private int $now = 1_000_000;

    protected function setUp(): void
    {
        $this->pdo = TestDatabase::create();
        $this->session = [];
        $this->now = 1_000_000;
    }

    private function service(): LoginService
    {
        return new LoginService(
            new User($this->pdo),
            new Auth($this->session, fn() => null),
            $this->session,
            fn(): int => $this->now
        );
    }

    public function testValidUserLogsIn(): void
    {
        Users::insert($this->pdo, 'a@example.com', 'correct horse');

        $error = $this->service()->attempt('a@example.com', 'correct horse');

        $this->assertNull($error);
        $this->assertNotNull($this->session['user_id']);
    }

    public function testPasswordsWithHtmlCharactersWork(): void
    {
        Users::insert($this->pdo, 'a@example.com', 'p&ss<w>"rd\'1');

        $this->assertNull($this->service()->attempt('a@example.com', 'p&ss<w>"rd\'1'));
    }

    public function testWrongPasswordAndUnknownEmailGiveTheSameMessage(): void
    {
        Users::insert($this->pdo, 'a@example.com', 'correct horse');

        $wrong = $this->service()->attempt('a@example.com', 'nope');
        $unknown = $this->service()->attempt('ghost@example.com', 'nope');

        $this->assertSame('Invalid email or password.', $wrong);
        $this->assertSame($wrong, $unknown);
        $this->assertArrayNotHasKey('user_id', $this->session);
    }

    public function testDeactivatedAccountCannotLogIn(): void
    {
        Users::insert($this->pdo, 'a@example.com', 'correct horse', ['isActive' => 0]);

        $error = $this->service()->attempt('a@example.com', 'correct horse');

        $this->assertStringContainsString('deactivated', (string)$error);
        $this->assertArrayNotHasKey('user_id', $this->session);
    }

    public function testAdminMustUseTheAdminPortal(): void
    {
        Users::insert($this->pdo, 'boss@example.com', 'correct horse', ['isAdmin' => 1]);

        $error = $this->service()->attempt('boss@example.com', 'correct horse');

        $this->assertStringContainsString('admin portal', (string)$error);
        $this->assertArrayNotHasKey('user_id', $this->session);
    }

    public function testAdminPortalAcceptsAdminsAndSetsAdminSession(): void
    {
        Users::insert($this->pdo, 'boss@example.com', 'correct horse', ['isAdmin' => 1]);

        $error = $this->service()->attempt('boss@example.com', 'correct horse', asAdmin: true);

        $this->assertNull($error);
        $this->assertNotNull($this->session['admin_id']);
        $this->assertArrayNotHasKey('user_id', $this->session);
    }

    public function testAdminPortalRejectsOrdinaryUsersWithTheGenericMessage(): void
    {
        Users::insert($this->pdo, 'a@example.com', 'correct horse');

        $error = $this->service()->attempt('a@example.com', 'correct horse', asAdmin: true);

        $this->assertSame('Invalid email or password.', $error);
        $this->assertArrayNotHasKey('admin_id', $this->session);
    }

    public function testDeactivatedAdminCannotUseTheAdminPortal(): void
    {
        Users::insert($this->pdo, 'boss@example.com', 'correct horse', ['isAdmin' => 1, 'isActive' => 0]);

        $this->assertNotNull($this->service()->attempt('boss@example.com', 'correct horse', asAdmin: true));
        $this->assertArrayNotHasKey('admin_id', $this->session);
    }

    public function testLocksOutAfterFiveFailuresEvenForTheCorrectPassword(): void
    {
        Users::insert($this->pdo, 'a@example.com', 'correct horse');
        $service = $this->service();

        for ($i = 0; $i < 5; $i++) {
            $service->attempt('a@example.com', 'wrong');
        }
        $error = $service->attempt('a@example.com', 'correct horse');

        $this->assertStringContainsString('Too many', (string)$error);
        $this->assertArrayNotHasKey('user_id', $this->session);
    }

    public function testLockoutExpires(): void
    {
        Users::insert($this->pdo, 'a@example.com', 'correct horse');
        $service = $this->service();
        for ($i = 0; $i < 5; $i++) {
            $service->attempt('a@example.com', 'wrong');
        }

        $this->now += 15 * 60 + 1;

        $this->assertNull($service->attempt('a@example.com', 'correct horse'));
    }

    public function testSuccessfulLoginResetsTheFailureCount(): void
    {
        Users::insert($this->pdo, 'a@example.com', 'correct horse');
        $service = $this->service();
        for ($i = 0; $i < 4; $i++) {
            $service->attempt('a@example.com', 'wrong');
        }
        $service->attempt('a@example.com', 'correct horse');

        for ($i = 0; $i < 4; $i++) {
            $service->attempt('a@example.com', 'wrong');
        }

        $this->assertNull($service->attempt('a@example.com', 'correct horse'));
    }

    public function testLoginRecordsLastActive(): void
    {
        $user = Users::insert($this->pdo, 'a@example.com', 'correct horse');

        $this->service()->attempt('a@example.com', 'correct horse');

        $stmt = $this->pdo->prepare('SELECT lastActive FROM "User" WHERE userID = ?');
        $stmt->execute([$user['userID']]);
        $this->assertNotNull($stmt->fetchColumn());
    }

    public function testOutdatedPasswordHashIsUpgradedOnLogin(): void
    {
        $user = Users::insert($this->pdo, 'a@example.com', 'correct horse');
        $before = $user['passwordHash']; // cost 4, below the app's cost

        $this->service()->attempt('a@example.com', 'correct horse');

        $stmt = $this->pdo->prepare('SELECT passwordHash FROM "User" WHERE userID = ?');
        $stmt->execute([$user['userID']]);
        $after = $stmt->fetchColumn();
        $this->assertNotSame($before, $after);
        $this->assertTrue(password_verify('correct horse', $after));
    }
}
