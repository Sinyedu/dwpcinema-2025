<?php
require_once "../models/User.php";

class AdminController {
    private $userModel;

    public function __construct($pdo) {
        $this->userModel = new User($pdo);
    }

    public function login($email, $password) {
        $user = $this->userModel->login($email, $password);
        if ($user && $user['isAdmin'] == 1) {
            return $user;
        }
        return false;
    }

    public function getAllUsers() {
        return $this->userModel->getAllUsers();
    }
}
?>
