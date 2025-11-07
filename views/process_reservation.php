<?php
require_once __DIR__ . '/../classes/Database.php';
$pdo = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['contactFormid']);
    $tournamentID = !empty($_POST['tournament_id']) ? intval($_POST['tournament_id']) : null;
    $status = $_POST['status'] ?? 'Pending';

    $stmt = $pdo->prepare("UPDATE ContactForm SET tournamentID = :tournamentID, status = :status WHERE contactFormid = :id");
    $stmt->execute([
        ':tournamentID' => $tournamentID,
        ':status' => $status,
        ':id' => $id,
    ]);

    header("Location: admin_reservations.php");
    exit;
}
