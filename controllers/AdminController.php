<?php
require_once __DIR__ . '/../models/Admin.php';
require_once __DIR__ . '/../models/ContactForm.php';

class AdminController
{
    private Admin $adminModel;
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->adminModel = new Admin($pdo);
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

    public function getReservations(): array
    {
        $contactForm = new ContactForm($this->pdo);
        return $contactForm->getReservations();
    }
}
