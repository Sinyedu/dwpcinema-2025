<?php
session_start();
include __DIR__ . '/includes/navbar.php';
require_once __DIR__ . '/classes/Database.php';

$pdo = Database::getInstance();

if (!isset($_SESSION['user_id'])) header("Location: login.php");
if (!isset($_GET['showingID'])) die("No showing selected.");

$showingID = (int)$_GET['showingID'];

$showingStmt = $pdo->prepare("
    SELECT s.showingID, s.showingDate, s.showingTime, h.hallID, h.hallName, m.matchName, t.tournamentName
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

$tierColors = [3 => 'bg-green-400', 2 => 'bg-blue-400', 1 => 'bg-yellow-400'];
$tierPrices = [];
foreach ($tiers as $tier) {
    $tierPrices[$tier['tierID']] = $tier['basePrice'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Seat Selection - <?= htmlspecialchars($showing['matchName']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 text-gray-900 min-h-screen">

    <div class="max-w-5xl mx-auto px-6 py-10">
        <h1 class="text-3xl font-bold mb-2"><?= htmlspecialchars($showing['matchName']) ?></h1>
        <p class="text-gray-700 mb-4"><?= htmlspecialchars($showing['tournamentName']) ?> | <?= htmlspecialchars($showing['hallName']) ?></p>
        <p class="text-gray-600 mb-6">Date: <?= date('F j, Y', strtotime($showing['showingDate'])) ?> | Time: <?= date('H:i', strtotime($showing['showingTime'])) ?></p>
        <p class="text-gray-700 mb-6">
            This hall contains 500 standard seats and VIP. Premium or BOX seating is available only on request via the admin, use the Contact form to inquire about special seating arrangements.
        </p>

        <form method="POST" action="booking_confirm.php" id="seatForm">
            <input type="hidden" name="showingID" value="<?= $showingID ?>">

            <div class="flex flex-col items-center mb-6">
                <div class="w-full max-w-5xl h-6 bg-gray-300 rounded mb-4 text-center flex items-center justify-center">
                    <span class="text-gray-700 font-semibold">SCREEN</span>
                </div>

                <div id="seatContainer" class="flex flex-col gap-2">
                    <?php
                    $currentRow = '';
                    foreach ($seats as $seat):
                        if ($currentRow != $seat['seatRow']):
                            if ($currentRow != '') echo '</div>';
                            $currentRow = $seat['seatRow'];
                            echo '<div class="flex flex-wrap justify-center gap-1">';
                        endif;

                        $isBooked = in_array($seat['seatID'], $bookedSeats);
                        $colorClass = $isBooked ? 'bg-gray-400 cursor-not-allowed' : ($tierColors[$seat['tierID']] ?? 'bg-gray-200');
                    ?>
                        <label class="text-center cursor-pointer">
                            <input type="checkbox" name="seatIDs[]" value="<?= $seat['seatID'] ?>" data-tier="<?= $seat['tierID'] ?>" <?= $isBooked ? 'disabled' : '' ?> class="hidden peer seatCheckbox">
                            <div class="w-6 h-6 rounded flex items-center justify-center text-xs font-semibold <?= $colorClass ?> peer-checked:ring-4 peer-checked:ring-green-500">
                                <?= $seat['seatRow'] . $seat['seatNumber'] ?>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
    </div>

    <div class="text-lg font-semibold mb-4">
        Total Price: $<span id="totalPrice">0.00</span>
    </div>

    <button type="submit" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-500">Confirm Booking</button>
    </form>


    <div class="mt-6 grid grid-cols-4 gap-4 max-w-xs">
        <?php foreach ($tiers as $tier): ?>
            <p class="flex items-center text-gray-700">
                <span class="inline-block w-5 h-5 <?= $tierColors[$tier['tierID']] ?? 'bg-gray-200' ?> mr-2"></span>
                <?= htmlspecialchars($tier['tierName']) ?> ($<?= number_format($tier['basePrice'], 2) ?>)
            </p>
        <?php endforeach; ?>
        <p class="flex items-center text-gray-700"><span class="inline-block w-5 h-5 bg-gray-400 mr-2"></span>Booked</p>
    </div>
    </div>

    <script>
        const tierPrices = <?= json_encode($tierPrices) ?>;
        const checkboxes = document.querySelectorAll('.seatCheckbox');
        const totalPriceEl = document.getElementById('totalPrice');

        function updateTotal() {
            let total = 0;
            checkboxes.forEach(cb => {
                if (cb.checked) {
                    const tier = cb.dataset.tier;
                    total += tierPrices[tier] || 0;
                }
            });
            totalPriceEl.textContent = total.toFixed(2);
        }

        checkboxes.forEach(cb => cb.addEventListener('change', updateTotal));
        updateTotal();
    </script>
</body>

</html>