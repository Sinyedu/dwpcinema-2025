<?php
class User {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function register($firstName, $lastName, $email, $password) {
        $hashedPw = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare(
            "INSERT INTO User (firstName, lastName, userEmail, passwordHash) VALUES (?, ?, ?, ?)"
        );
        return $stmt->execute([$firstName, $lastName, $email, $hashedPw]);
    }

    public function login($email, $password) {
        $stmt = $this->pdo->prepare("SELECT * FROM User WHERE userEmail = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['passwordHash'])) {
            return $user;
        }
        return false;
    }

    public function getAllUsers() {
        $stmt = $this->pdo->query("SELECT userID, firstName, lastName, userEmail FROM User ORDER BY firstName ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

}
?>
