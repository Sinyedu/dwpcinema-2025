<?php
session_start();
$pdo = new PDO("mysql:host=localhost;dbname=dwpcinemaDB;charset=utf8", "root", "");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['showingID'])) {
    echo "No showing selected.";
    exit;
}

$showingID = (int)$_GET['showingID'];

$stmt = $pdo->prepare("
    SELECT s.showingID, s.showingDate, s.showingTime, h.hallID, h.hallName, h.totalSeats, m.tournamentID, t.tournamentName
    FROM Showing s
    JOIN Hall h ON s.hallID = h.hallID
    JOIN `Match` m ON s.matchID = m.matchID
    JOIN Tournament t ON m.tournamentID = t.tournamentID
    WHERE s.showingID = ?
");
$stmt->execute([$showingID]);
$showing = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$showing) {
    echo "Showing not found.";
    exit;
}

$stmt2 = $pdo->prepare("
    SELECT COUNT(bs.seatID) AS bookedSeats
    FROM Booking b
    JOIN Booking_Seat bs ON b.bookingID = bs.bookingID
    WHERE b.showingID = ?
");
$stmt2->execute([$showingID]);
$bookedSeats = (int)$stmt2->fetchColumn();

$availableSeats = $showing['totalSeats'] - $bookedSeats;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Showing - <?= htmlspecialchars($showing['tournamentName']) ?></title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen">

<div class="max-w-3xl mx-auto px-6 py-10">
    <h1 class="text-2xl font-bold mb-4"><?= htmlspecialchars($showing['tournamentName']) ?> - Showing Details</h1>

    <div class="bg-white shadow rounded p-6 mb-6">
        <p><strong>Hall:</strong> <?= htmlspecialchars($showing['hallName']) ?></p>
        <p><strong>Date:</strong> <?= htmlspecialchars($showing['showingDate']) ?></p>
        <p><strong>Time:</strong> <?= htmlspecialchars($showing['showingTime']) ?></p>
        <p><strong>Total Seats:</strong> <?= $showing['totalSeats'] ?></p>
        <p><strong>Booked Seats:</strong> <?= $bookedSeats ?></p>
        <p><strong>Available Seats:</strong> <?= $availableSeats ?></p>
    </div>

    <?php if ($availableSeats > 0): ?>
        <form method="POST" action="booking.php">
            <input type="hidden" name="showingID" value="<?= $showingID ?>">
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-500">Book This Showing</button>
        </form>
    <?php else: ?>
        <p class="text-red-600 font-semibold">Sorry, this showing is fully booked.</p>
    <?php endif; ?>

</div>
</body>
</html>
