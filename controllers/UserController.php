<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/SecurityController.php';
require_once __DIR__ . '/../classes/ImageUploader.php';
require_once __DIR__ . '/../classes/Database.php';
//! Potential refactor feels like it is handling too much logic, consider moving some to User model
class UserController
{
    private User $userModel;

    public function __construct()
    {
        $pdo = Database::getInstance();
        $this->userModel = new User($pdo);
    }

    public function register(array $data): bool
    {
        $firstName = SecurityController::sanitizeInput($data['firstName']);
        $lastName = SecurityController::sanitizeInput($data['lastName']);
        $email = SecurityController::sanitizeInput($data['email']);
        $password = SecurityController::sanitizeInput($data['password']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new Exception("Invalid email address.");
        if ($this->userModel->emailExists($email)) return false;

        return $this->userModel->register($firstName, $lastName, $email, $password);
    }

    public function login(array $data)
    {
        try {
            return $this->userModel->login($data['email'], $data['password']);
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function listUsers(): array
    {
        return $this->userModel->getAllUsers();
    }

    public function getProfile(int $userID)
    {
        return $this->userModel->getUserById($userID);
    }

    public function updateProfile(int $userID, array $data, array $files): bool
    {
        $firstName = SecurityController::sanitizeInput($data['firstName']);
        $lastName = SecurityController::sanitizeInput($data['lastName']);
        $email = SecurityController::sanitizeInput($data['email']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new Exception("Invalid email address.");

        $user = $this->userModel->getUserById($userID);
        $avatarPath = $user['avatar'] ?? 'uploads/avatars/default.png';

        if (!empty($files['avatar']['name'])) {
            $uploader = new ImageUploader();
            $avatarPath = $uploader->upload($files['avatar']);
        }

        $this->userModel->updateUser($userID, $firstName, $lastName, $email, $avatarPath);

        $_SESSION['user_name'] = $firstName . ' ' . $lastName;
        $_SESSION['user_avatar'] = '/' . $avatarPath;

        return true;
    }

    public function deactivateUser(int $userID): bool
    {
        return $this->userModel->deactivateUser($userID);
    }

    public function activateUser(int $userID): bool
    {
        return $this->userModel->activateUser($userID);
    }

    public function deleteUser(int $userID): bool
    {
        return $this->userModel->deleteUser($userID);
    }
}
