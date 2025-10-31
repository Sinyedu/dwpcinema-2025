<?php
require_once __DIR__ . '/../classes/Controller.php';

class AdminController extends Controller {
    private $userModel;

    public function __construct($pdo = null) {
        parent::__construct($pdo);
        $this->userModel = $this->model('User');
    }

    public function login($email, $password) {
        $user = $this->userModel->login($email, $password);
        if ($user && isset($user['isAdmin']) && $user['isAdmin'] == 1) {
            return $user;
        }
        return false;
    }

    public function getAllUsers() {
        return $this->userModel->getAllUsers();
    }

    public function deleteUser($userID) {
        return $this->userModel->deleteUser($userID);
    }
}
