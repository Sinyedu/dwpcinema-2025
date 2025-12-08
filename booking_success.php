<?php
session_start();
include __DIR__ . '/includes/navbar.php';

require_once __DIR__ . '/classes/Database.php';

$pdo = Database::getInstance();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['bookingID'])) {
    die("No booking selected.");
}

$bookingID = (int)$_GET['bookingID'];

$stmt = $pdo->prepare("
    SELECT b.bookingID, b.bookingDate, s.showingDate, s.showingTime,
           h.hallName, t.tournamentName, g.gameName
    FROM Booking b
    JOIN Showing s ON b.showingID = s.showingID
    JOIN Hall h ON s.hallID = h.hallID
    JOIN `Match` m ON s.matchID = m.matchID
    JOIN Tournament t ON m.tournamentID = t.tournamentID
    JOIN Game g ON t.gameID = g.gameID
    WHERE b.bookingID = ? AND b.userID = ?
");
$stmt->execute([$bookingID, $_SESSION['user_id']]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    die("Booking not found.");
}

$stmt2 = $pdo->prepare("
    SELECT st.tierName, st.basePrice, CONCAT(s.seatRow, s.seatNumber) AS seatLabel
    FROM Booking_Seat bs
    JOIN Seat s ON bs.seatID = s.seatID
    JOIN SeatTier st ON s.tierID = st.tierID
    WHERE bs.bookingID = ?
    ORDER BY s.seatRow, s.seatNumber
");
$stmt2->execute([$bookingID]);
$seats = $stmt2->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
foreach ($seats as $s) {
    $total += $s['basePrice'];
}

$tierColors = [
    'VIP' => 'bg-yellow-400',
    'Premium' => 'bg-blue-400',
    'Standard' => 'bg-green-400'
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Booking Success - <?= htmlspecialchars($booking['tournamentName']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 text-gray-900 min-h-screen">

    <div class="max-w-4xl mx-auto px-6 py-10">
        <h1 class="text-2xl font-bold mb-4 text-green-600">Booking Confirmed!</h1>

        <div class="bg-white rounded shadow p-6 mb-6">
            <h2 class="text-xl font-semibold mb-2"><?= htmlspecialchars($booking['tournamentName']) ?></h2>
            <p class="text-gray-700 mb-1"><?= htmlspecialchars($booking['gameName']) ?></p>
            <p class="text-gray-700 mb-1">Hall: <?= htmlspecialchars($booking['hallName']) ?></p>
            <p class="text-gray-700 mb-1">Date: <?= htmlspecialchars($booking['showingDate']) ?> | Time: <?= htmlspecialchars($booking['showingTime']) ?></p>
            <p class="text-gray-700 mb-1">Booking Made: <?= htmlspecialchars($booking['bookingDate']) ?></p>
        </div>

        <div class="bg-white rounded shadow p-6 mb-6">
            <h3 class="text-lg font-semibold mb-2">Seats Booked</h3>
            <div class="flex flex-wrap gap-2 mb-2">
                <?php foreach ($seats as $s):
                    $color = $tierColors[$s['tierName']] ?? 'bg-gray-300';
                ?>
                    <span class="px-2 py-1 rounded text-white font-semibold <?= $color ?>"><?= htmlspecialchars($s['seatLabel']) ?> (<?= htmlspecialchars($s['tierName']) ?>)</span>
                <?php endforeach; ?>
            </div>
            <p class="font-semibold text-lg">Total Price: $<?= number_format($total, 2) ?></p>
        </div>

        <a href="showings.php" class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-500">Back to Showings</a>
    </div>


</body>

</html>