<?php
require_once __DIR__ . '/../models/Admin.php';
//TODO Add more admin functionalities as needed
class AdminController
{
    private Admin $adminModel;

    public function __construct()
    {
        $this->adminModel = new Admin();
    }

    public function login(string $email, string $password)
    {
        return $this->adminModel->login($email, $password);
    }

    public function getAllUsers(): array
    {
        return $this->adminModel->getAllUsers();
    }

    public function deleteUser(int $userID): bool
    {
        return $this->adminModel->deleteUser($userID);
    }
}
