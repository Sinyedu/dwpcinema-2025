<?php
require_once __DIR__ . '/../classes/Hasher.php';

class User
{
    private $pdo;
    private $table = "User";

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function emailExists($email)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM {$this->table} WHERE userEmail = ?");
        $stmt->execute([$email]);
        return $stmt->fetchColumn() > 0;
    }

    public function register($firstName, $lastName, $email, $password)
    {
        if ($this->emailExists($email)) {
            return false;
        }

        $hashedPw = PasswordHasher::hash($password);

        $stmt = $this->pdo->prepare("
            INSERT INTO {$this->table} (firstName, lastName, userEmail, passwordHash)
            VALUES (?, ?, ?, ?)
        ");

        return $stmt->execute([$firstName, $lastName, $email, $hashedPw]);
    }

    public function login($email, $password)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE userEmail = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && PasswordHasher::verify($password, $user['passwordHash'])) {
            return $user;
        }
        return false;
    }

    public function getAllUsers()
    {
        $stmt = $this->pdo->prepare("
            SELECT userID, firstName, lastName, userEmail
            FROM {$this->table}
            ORDER BY userID ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserById(int $userID): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE userID = ?");
        $stmt->execute([$userID]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function updateUser(int $userID, string $firstName, string $lastName, string $email, string $avatar): bool
    {
        $stmt = $this->pdo->prepare("
        UPDATE {$this->table} 
        SET firstName = ?, lastName = ?, userEmail = ?, avatar = ?
        WHERE userID = ?
    ");
        return $stmt->execute([$firstName, $lastName, $email, $avatar, $userID]);
    }

    public function deactivateUser(int $userID): bool
    {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET isActive = 0 WHERE userID = ?");
        return $stmt->execute([$userID]);
    }

    public function activateUser(int $userID): bool
    {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET isActive = 1 WHERE userID = ?");
        return $stmt->execute([$userID]);
    }


    public function deleteUser($userID)
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE userID = ?");
        return $stmt->execute([$userID]);
    }
}
