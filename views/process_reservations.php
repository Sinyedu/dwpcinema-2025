<?php
require_once __DIR__ . '/../classes/Database.php';
$pdo = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['contactFormid']);
    $status = $_POST['status'] ?? 'Pending';

    $stmt = $pdo->prepare("UPDATE ContactForm SET status = :status WHERE contactFormid = :id");
    $stmt->execute([
        ':status' => $status,
        ':id' => $id,
    ]);

    header("Location: reservations.php");
    exit;
}
