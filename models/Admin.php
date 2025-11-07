<?php
require_once __DIR__ . '/User.php';

class Admin
{
    private User $userModel;
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->userModel = new User($pdo);
    }

    public function login(string $email, string $password)
    {
        $user = $this->userModel->login($email, $password);
        if ($user && !empty($user['isAdmin']) && $user['isAdmin'] == 1) {
            return $user;
        }
        return false;
    }

    public function getAllUsers(): array
    {
        return $this->userModel->getAllUsers();
    }

    public function deleteUser(int $userID): bool
    {
        return $this->userModel->deleteUser($userID);
    }
}
