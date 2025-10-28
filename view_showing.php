<?php
session_start();
include __DIR__ . '/includes/navbar.php';

$pdo = new PDO("mysql:host=mysql119.unoeuro.com;dbname=simonnyblom_com_db;charset=utf8", "simonnyblom_com", "hwEG2df9rADt5gab4kFp");

if (!isset($_SESSION['user_id'])) header("Location: login.php");
if (!isset($_GET['showingID'])) die("No showing selected.");

$showingID = (int)$_GET['showingID'];

$showingStmt = $pdo->prepare("
    SELECT s.showingID, s.showingDate, s.showingTime, h.hallID, h.hallName, t.tournamentName
    FROM Showing s
    JOIN Hall h ON s.hallID = h.hallID
    JOIN `Match` m ON s.matchID = m.matchID
    JOIN Tournament t ON m.tournamentID = t.tournamentID
    WHERE s.showingID = ?
");
$showingStmt->execute([$showingID]);
$showing = $showingStmt->fetch(PDO::FETCH_ASSOC);

$seatStmt = $pdo->prepare("
    SELECT seatID, seatRow, seatNumber, tierID
    FROM Seat
    WHERE hallID = ?
    ORDER BY seatRow, seatNumber
");
$seatStmt->execute([$showing['hallID']]);
$seats = $seatStmt->fetchAll(PDO::FETCH_ASSOC);

$bookedStmt = $pdo->prepare("
    SELECT seatID FROM Booking_Seat bs
    JOIN Booking b ON bs.bookingID = b.bookingID
    WHERE b.showingID = ?
");
$bookedStmt->execute([$showingID]);
$bookedSeats = $bookedStmt->fetchAll(PDO::FETCH_COLUMN);

$tiersStmt = $pdo->query("SELECT tierID, tierName, basePrice FROM SeatTier");
$tiers = $tiersStmt->fetchAll(PDO::FETCH_ASSOC);

$tierColors = [1 => 'bg-yellow-400', 2 => 'bg-blue-400', 3 => 'bg-green-400'];
$tierPrices = [];
foreach ($tiers as $tier) {
    $tierPrices[$tier['tierID']] = $tier['basePrice'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Seat Selection - <?= htmlspecialchars($showing['tournamentName']) ?></title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen">

<div class="max-w-4xl mx-auto px-6 py-10">
    <h1 class="text-2xl font-bold mb-2"><?= htmlspecialchars($showing['tournamentName']) ?> - <?= htmlspecialchars($showing['hallName']) ?></h1>
    <p class="mb-6">Date: <?= htmlspecialchars($showing['showingDate']) ?> | Time: <?= htmlspecialchars($showing['showingTime']) ?></p>

    <form method="POST" action="booking_confirm.php" id="seatForm">
        <input type="hidden" name="showingID" value="<?= $showingID ?>">
        <div id="seatContainer" class="grid gap-1 justify-center">
        <?php
        $currentRow = '';
        foreach ($seats as $seat):
            if ($currentRow != $seat['seatRow']):
                if ($currentRow != '') echo '</div>';
                $currentRow = $seat['seatRow'];
                echo '<div class="flex gap-1 mb-2 justify-center">';
            endif;

            $isBooked = in_array($seat['seatID'], $bookedSeats);
            $colorClass = $isBooked ? 'bg-gray-400 cursor-not-allowed' : ($tierColors[$seat['tierID']] ?? 'bg-gray-200');
        ?>
            <label class="block text-center cursor-pointer">
                <input type="checkbox" name="seatIDs[]" value="<?= $seat['seatID'] ?>" data-tier="<?= $seat['tierID'] ?>" <?= $isBooked ? 'disabled' : '' ?> class="hidden peer seatCheckbox">
                <div class="w-10 h-10 rounded flex items-center justify-center text-sm font-semibold <?= $colorClass ?> peer-checked:ring-4 peer-checked:ring-green-500">
                    <?= $seat['seatRow'] . $seat['seatNumber'] ?>
                </div>
            </label>
        <?php endforeach; ?>
        </div>

        <div class="mt-4 text-lg">
            Total Price: $<span id="totalPrice">0.00</span>
        </div>

        <button type="submit" class="mt-4 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-500 w-full">Confirm Booking</button>
    </form>

    <div class="mt-6 grid grid-cols-4 gap-4 max-w-xs">
        <?php foreach ($tiers as $tier): ?>
            <p class="flex items-center">
                <span class="inline-block w-5 h-5 <?= $tierColors[$tier['tierID']] ?? 'bg-gray-200' ?> mr-2"></span>
                <?= htmlspecialchars($tier['tierName']) ?> ($<?= number_format($tier['basePrice'],2) ?>)
            </p>
        <?php endforeach; ?>
        <p class="flex items-center"><span class="inline-block w-5 h-5 bg-gray-400 mr-2"></span> Booked</p>
    </div>
</div>

<script src="js/seat_selection.js"></script>
<script>
    const tierPrices = <?= json_encode($tierPrices) ?>;
</script>
</body>
</html>
