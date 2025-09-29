<?php
require_once "../models/User.php";

class UserController {
    private $userModel;

    public function __construct($pdo) {
        $this->userModel = new User($pdo);
    }

    public function register($data) {
        $firstName = $data['firstName'];
        $lastName  = $data['lastName'];
        $email     = $data['email'];
        $password  = $data['password'];

        if ($this->userModel->emailExists($email)) {
            return false; // Email already used
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
