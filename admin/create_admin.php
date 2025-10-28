<?php
require_once "../database/connection.php";
require_once "../hash/passwordHasher.php";

$firstName = "Super";
$lastName  = "User";
$email     = "DWPEsports@gmail.com"; 
$password  = "Dxa2@as!1";          

$hashedPw = PasswordHasher::hash($password);

$stmt = $pdo->prepare(
    "INSERT INTO User (firstName, lastName, userEmail, passwordHash, isAdmin)
     VALUES (?, ?, ?, ?, 1)"
);

try {
    $stmt->execute([$firstName, $lastName, $email, $hashedPw]);
    echo "Admin user created successfully!";
} catch (PDOException $e) {
    echo "Error creating admin: " . $e->getMessage();
}
