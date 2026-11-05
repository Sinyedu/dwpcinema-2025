<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class UserModelTest extends TestCase
{
    private PDO $pdo;
    private User $users;

    protected function setUp(): void
    {
        $this->pdo = TestDatabase::create();
        $this->users = new User($this->pdo);
    }

    public function testRegisterStoresAHashNotThePassword(): void
    {
        $this->assertTrue($this->users->register('Ada', 'Lovelace', 'ada@example.com', 'secret-pass'));

        $row = $this->pdo->query('SELECT * FROM "User"')->fetch();
        $this->assertNotSame('secret-pass', $row['passwordHash']);
        $this->assertTrue(password_verify('secret-pass', $row['passwordHash']));
    }

    public function testRegisterStoresNamesVerbatim(): void
    {
        $this->users->register('Sinéad', "O'Brien & Sons", 'a@example.com', 'secret-pass');

        $row = $this->pdo->query('SELECT firstName, lastName FROM "User"')->fetch();
        $this->assertSame('Sinéad', $row['firstName']);
        $this->assertSame("O'Brien & Sons", $row['lastName']);
    }

    public function testRegisterRefusesDuplicateEmail(): void
    {
        $this->users->register('Ada', 'L', 'ada@example.com', 'secret-pass');

        $this->assertFalse($this->users->register('Other', 'Person', 'ada@example.com', 'secret-pass'));
    }

    public function testAuthenticateReturnsTheUserForCorrectCredentials(): void
    {
        $this->users->register('Ada', 'L', 'ada@example.com', 'secret-pass');

        $user = $this->users->authenticate('ada@example.com', 'secret-pass');

        $this->assertSame('ada@example.com', $user['userEmail']);
    }

    public function testAuthenticateReturnsNullOtherwise(): void
    {
        $this->users->register('Ada', 'L', 'ada@example.com', 'secret-pass');

        $this->assertNull($this->users->authenticate('ada@example.com', 'wrong'));
        $this->assertNull($this->users->authenticate('ghost@example.com', 'secret-pass'));
    }

    public function testUpdateUserRefusesAnEmailTakenBySomeoneElse(): void
    {
        $this->users->register('Ada', 'L', 'ada@example.com', 'secret-pass');
        $this->users->register('Bob', 'B', 'bob@example.com', 'secret-pass');
        $bob = $this->users->authenticate('bob@example.com', 'secret-pass');

        $this->assertFalse($this->users->updateUser($bob['userID'], 'Bob', 'B', 'ada@example.com', 'a.png'));
    }

    public function testUpdateUserKeepsOwnEmail(): void
    {
        $this->users->register('Bob', 'B', 'bob@example.com', 'secret-pass');
        $bob = $this->users->authenticate('bob@example.com', 'secret-pass');

        $this->assertTrue($this->users->updateUser($bob['userID'], 'Robert', 'B', 'bob@example.com', 'a.png'));
        $this->assertSame('Robert', $this->users->getUserById($bob['userID'])['firstName']);
    }

    public function testDeactivateAndActivate(): void
    {
        $this->users->register('Ada', 'L', 'ada@example.com', 'secret-pass');
        $id = $this->users->authenticate('ada@example.com', 'secret-pass')['userID'];

        $this->users->deactivateUser($id);
        $this->assertSame(0, (int)$this->users->getUserById($id)['isActive']);

        $this->users->activateUser($id);
        $this->assertSame(1, (int)$this->users->getUserById($id)['isActive']);
    }

    public function testListingUsersNeverExposesPasswordHashes(): void
    {
        $this->users->register('Ada', 'L', 'ada@example.com', 'secret-pass');

        $this->assertArrayNotHasKey('passwordHash', $this->users->getAllUsers()[0]);
    }
}
