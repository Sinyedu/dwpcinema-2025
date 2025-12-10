<?php
session_start();
require_once __DIR__ . '/classes/Database.php';

$pdo = Database::getInstance();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userID = $_SESSION['user_id'];
$showingID = $_POST['showingID'] ?? null;
$seatIDs = $_POST['seatIDs'] ?? [];

if (!$showingID || empty($seatIDs)) {
    die("No seats selected.");
}

// Only way to calculate total price is to query the database
$placeholders = implode(',', array_fill(0, count($seatIDs), '?'));

$priceQuery = $pdo->prepare("
    SELECT SUM(st.basePrice) AS totalAmount
    FROM Seat s
    JOIN SeatTier st ON s.tierID = st.tierID
    WHERE s.seatID IN ($placeholders)
");
$priceQuery->execute($seatIDs);
$totalAmount = $priceQuery->fetchColumn();

if ($totalAmount === null) {
    die("Failed to calculate total price.");
}

$checkBooked = $pdo->prepare("
    SELECT seatID
    FROM Booking_Seat bs
    JOIN Booking b ON bs.bookingID = b.bookingID
    WHERE b.showingID = ?
    AND bs.seatID IN ($placeholders)
");
$checkBooked->execute(array_merge([$showingID], $seatIDs));
$alreadyBooked = $checkBooked->fetchAll(PDO::FETCH_COLUMN);

if (!empty($alreadyBooked)) {
    die("One or more selected seats are already booked.");
}

$pdo->beginTransaction();

try {
    $stmt = $pdo->prepare("
        INSERT INTO Booking (userID, showingID, totalAmount, bookingDate)
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute([$userID, $showingID, $totalAmount]);
    $bookingID = $pdo->lastInsertId();

    $stmt2 = $pdo->prepare("
        INSERT INTO Booking_Seat (bookingID, seatID)
        VALUES (?, ?)
    ");

    foreach ($seatIDs as $seatID) {
        $stmt2->execute([$bookingID, $seatID]);
    }

    $pdo->commit();

    header("Location: booking_success.php?bookingID=$bookingID");
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    die("Booking failed: " . $e->getMessage());
}
