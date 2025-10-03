<?php
require_once "../database/connection.php";
require_once "../hash/PasswordHasher.php";

$firstName = "Admin";
$lastName  = "User";
$email     = "admin@example.com"; 
$password  = "Admin123";          

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
x