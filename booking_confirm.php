<?php
session_start();
require_once __DIR__ . '/classes/Database.php';

$pdo = Database::getInstance();

if (!isset($_SESSION['user_id'])) header("Location: login.php");

$userID = $_SESSION['user_id'];
$showingID = $_POST['showingID'];
$seatIDs = $_POST['seatIDs'] ?? [];
$totalAmount = $_POST ['totalAmount'];

if (!$seatIDs) die("No seats selected.");

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare("INSERT INTO Booking (userID, showingID, bookingDate, totalAmount) VALUES (?, ?, NOW(), ?)");
    $stmt->execute([$userID, $showingID, $totalAmount]);
    $bookingID = $pdo->lastInsertId();

    $stmt2 = $pdo->prepare("INSERT INTO Booking_Seat (bookingID, seatID) VALUES (?, ?)");
    foreach ($seatIDs as $seatID) {
        $stmt2->execute([$bookingID, $seatID]);
    }

    $pdo->commit();
    header("Location: booking_success.php?bookingID=$bookingID");
} catch (Exception $e) {
    $pdo->rollBack();
    die("Booking failed: " . $e->getMessage()); 
}
?>
