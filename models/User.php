<?php
declare(strict_types=1);

class User
{
    private const SAFE_COLUMNS = 'userID, firstName, lastName, userEmail, isActive, lastActive, avatar';

    public function __construct(private PDO $pdo)
    {
    }

    public function emailExists(string $email, ?int $exceptUserID = null): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM User WHERE userEmail = ? AND userID <> ?');
        $stmt->execute([$email, $exceptUserID ?? 0]);
        return $stmt->fetchColumn() > 0;
    }

    public function register(string $firstName, string $lastName, string $email, string $password): bool
    {
        if ($this->emailExists($email)) {
            return false;
        }

        try {
            $stmt = $this->pdo->prepare('
                INSERT INTO User (firstName, lastName, userEmail, passwordHash, isActive, lastActive)
                VALUES (?, ?, ?, ?, 1, NULL)
            ');
            return $stmt->execute([$firstName, $lastName, $email, PasswordHasher::hash($password)]);
        } catch (PDOException $e) {
            if ($this->isUniqueViolation($e)) { // lost a race with a concurrent registration
                return false;
            }
            throw $e;
        }
    }

    /**
     * Verify credentials only. Whether the account may log in (active? admin?) is policy
     * and lives in LoginService. Upgrades an outdated password hash on success.
     */
    public function authenticate(string $email, string $password): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM User WHERE userEmail = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !PasswordHasher::verify($password, $user['passwordHash'])) {
            return null;
        }

        if (PasswordHasher::needsRehash($user['passwordHash'])) {
            $this->pdo->prepare('UPDATE User SET passwordHash = ? WHERE userID = ?')
                ->execute([PasswordHasher::hash($password), $user['userID']]);
        }

        return $user;
    }

    public function touchLastActive(int $userID): void
    {
        $this->pdo->prepare('UPDATE User SET lastActive = NOW() WHERE userID = ?')->execute([$userID]);
    }

    public function getAllUsers(): array
    {
        return $this->pdo->query('SELECT ' . self::SAFE_COLUMNS . ' FROM User ORDER BY userID ASC')
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserById(int $userID): ?array
    {
        $stmt = $this->pdo->prepare('SELECT ' . self::SAFE_COLUMNS . ' FROM User WHERE userID = ?');
        $stmt->execute([$userID]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /** @return bool false if the email already belongs to another user */
    public function updateUser(int $userID, string $firstName, string $lastName, string $email, string $avatar): bool
    {
        if ($this->emailExists($email, $userID)) {
            return false;
        }

        try {
            return $this->pdo->prepare('
                UPDATE User SET firstName = ?, lastName = ?, userEmail = ?, avatar = ? WHERE userID = ?
            ')->execute([$firstName, $lastName, $email, $avatar, $userID]);
        } catch (PDOException $e) {
            if ($this->isUniqueViolation($e)) {
                return false;
            }
            throw $e;
        }
    }

    /** Accounts are deactivated, never hard-deleted (bookings and tickets reference them). */
    public function deactivateUser(int $userID): bool
    {
        return $this->setActive($userID, 0);
    }

    public function activateUser(int $userID): bool
    {
        return $this->setActive($userID, 1);
    }

    private function setActive(int $userID, int $active): bool
    {
        return $this->pdo->prepare('UPDATE User SET isActive = ? WHERE userID = ?')->execute([$active, $userID]);
    }

    private function isUniqueViolation(PDOException $e): bool
    {
        return ($e->errorInfo[0] ?? $e->getCode()) === '23000';
    }
}
