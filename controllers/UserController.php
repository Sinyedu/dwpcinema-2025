<?php
require_once "models/User.php";
require_once "controllers/SecurityController.php";
class UserController {
    private $userModel;

    public function __construct($pdo) {
        $this->userModel = new User($pdo);
    }

    public function register($data) {
        $firstName = SecurityController::sanitizeInput($data['firstName']);
        $lastName  = SecurityController::sanitizeInput($data['lastName']);
        $email     = SecurityController::sanitizeInput($data['email']);
        $password  = SecurityController::sanitizeInput($data['password']);

        if ($this->userModel->emailExists($email)) {
            return false; 
        }

        return $this->userModel->register($firstName, $lastName, $email, $password);
    }

    public function login($data) {
        return $this->userModel->login($data['email'], $data['password']);
    }

    public function listUsers() {
        return $this->userModel->getAllUsers();
    }
}
?>
