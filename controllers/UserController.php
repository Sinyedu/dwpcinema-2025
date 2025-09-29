<?php
require_once "../models/User.php";

class UserController {
    private $userModel;

    public function __construct($pdo) {
        $this->userModel = new User($pdo);
    }

    public function register($data) {
        return $this->userModel->register(
            $data['firstName'],
            $data['lastName'],
            $data['email'],
            $data['password']
        );
    }

    public function login($data) {
        return $this->userModel->login(
            $data['email'],
            $data['password']
        );
    }

    public function listUsers() {
        return $this->userModel->getAllUsers();
}

}
?>
