<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/SecurityController.php';
require_once __DIR__ . '/../classes/ImageUploader.php';

class UserController {
    private User $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function register(array $data): bool {
        $firstName = SecurityController::sanitizeInput($data['firstName']);
        $lastName = SecurityController::sanitizeInput($data['lastName']);
        $email = SecurityController::sanitizeInput($data['email']);
        $password = SecurityController::sanitizeInput($data['password']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new Exception("Invalid email address.");
        if ($this->userModel->emailExists($email)) return false;

        return $this->userModel->register($firstName, $lastName, $email, $password);
    }

    public function login(array $data) {
        return $this->userModel->login($data['email'], $data['password']);
    }

    public function listUsers(): array {
        return $this->userModel->getAllUsers();
    }

    public function getProfile(int $userID) {
        return $this->userModel->getUserById($userID);
    }

    public function updateProfile(int $userID, array $data, array $files): bool {
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
}
