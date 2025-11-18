<?php
require_once __DIR__ . '/../classes/Hasher.php';
require_once __DIR__ . '/../classes/Database.php';

class User
{
    private PDO $pdo;
    private string $table = 'User';

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM {$this->table} WHERE userEmail = ?");
        $stmt->execute([$email]);
        return $stmt->fetchColumn() > 0;
    }

    public function register(string $firstName, string $lastName, string $email, string $password): bool
    {
        if ($this->emailExists($email)) return false;

        $hashedPw = PasswordHasher::hash($password);
        $stmt = $this->pdo->prepare(
            "INSERT INTO {$this->table} (firstName, lastName, userEmail, passwordHash) VALUES (?, ?, ?, ?)"
        );
        return $stmt->execute([$firstName, $lastName, $email, $hashedPw]);
    }

    public function login(string $email, string $password)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE userEmail = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !PasswordHasher::verify($password, $user['passwordHash'])) {
            throw new Exception("Invalid email or password.");
        }

        return $user;
    }

    public function getAllUsers(): array
    {
        $stmt = $this->pdo->query("
            SELECT userID, firstName, lastName, userEmail, avatar
            FROM {$this->table}
            ORDER BY userID DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserById(int $userID)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE userID = ?");
        $stmt->execute([$userID]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateUser(int $userID, string $firstName, string $lastName, string $email, string $avatarPath): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE {$this->table}
            SET firstName = ?, lastName = ?, userEmail = ?, avatar = ?
            WHERE userID = ?
        ");
        return $stmt->execute([$firstName, $lastName, $email, $avatarPath, $userID]);
    }

    public function deleteUser(int $userID): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE userID = ?");
        return $stmt->execute([$userID]);
    }
}
