<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_user();

$showingID = filter_input(INPUT_GET, 'showingID', FILTER_VALIDATE_INT);
$bookings = new Booking(db());
$showing = $showingID ? $bookings->showingSummary($showingID) : null;

if (!$showing) {
    http_response_code(404);
    exit('Showing not found.');
}

$seats = $bookings->seatMap($showingID);
$tiers = $bookings->seatTiers();

$tierColors = [3 => 'bg-green-400', 2 => 'bg-blue-400', 1 => 'bg-yellow-400'];
$tierPrices = array_column($tiers, 'basePrice', 'tierID');

$seatsByRow = [];
foreach ($seats as $seat) {
    $seatsByRow[$seat['seatRow']][] = $seat;
}

$pageTitle = 'Seat Selection - ' . $showing['matchName'];
include __DIR__ . '/includes/head.php';
?>

<body class="bg-neutral-900 text-white min-h-screen">
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <div class="max-w-5xl mx-auto px-6 py-10">
        <h1 class="text-3xl font-bold mb-2"><?= e($showing['matchName']) ?></h1>
        <p class="text-white mb-4"><?= e($showing['tournamentName']) ?> | <?= e($showing['hallName']) ?></p>
        <p class="text-white mb-6">Date: <?= e(date('F j, Y', strtotime($showing['showingDate']))) ?> | Time: <?= e(date('H:i', strtotime($showing['showingTime']))) ?></p>
        <p class="text-white mb-6">
            This hall contains Standard and VIP seats. BOX seating is available only on request via the admin; use the Contact form to inquire about special seating arrangements.
        </p>
        <p class="text-gray-300 mb-6 text-sm">Select up to <?= SeatRules::MAX_SEATS ?> seats, side by side in one row.</p>

        <?php if (!empty($_SESSION['booking_error'])): ?>
            <p class="mb-4 p-2 bg-red-100 text-red-800 rounded"><?= e($_SESSION['booking_error']) ?></p>
            <?php unset($_SESSION['booking_error']); ?>
        <?php endif; ?>

        <form method="POST" action="booking_confirm.php" id="seatForm">
            <?= $csrf->field() ?>
            <input type="hidden" name="showingID" value="<?= (int)$showingID ?>">

            <div class="flex flex-col items-center mb-6">
                <div class="w-full max-w-5xl h-6 bg-gray-300 rounded mb-4 text-center flex items-center justify-center">
                    <span class="text-gray-700 font-semibold">SCREEN</span>
                </div>

                <div id="seatContainer" class="flex flex-col gap-2">
                    <?php foreach ($seatsByRow as $rowSeats): ?>
                        <div class="flex flex-wrap justify-center gap-1">
                            <?php foreach ($rowSeats as $seat):
                                $colorClass = $seat['booked'] ? 'bg-gray-400 cursor-not-allowed' : ($tierColors[$seat['tierID']] ?? 'bg-gray-200');
                            ?>
                                <label class="text-center cursor-pointer">
                                    <input type="checkbox" name="seatIDs[]" value="<?= (int)$seat['seatID'] ?>" data-tier="<?= (int)$seat['tierID'] ?>" <?= $seat['booked'] ? 'disabled' : '' ?> class="hidden peer seatCheckbox">
                                    <div class="w-6 h-6 rounded flex items-center justify-center text-xs font-semibold <?= e($colorClass) ?> peer-checked:ring-4 peer-checked:ring-yellow-500">
                                        <?= e($seat['seatLabel']) ?>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="text-lg font-semibold mb-4">
                Total Price: $<span id="totalPrice">0.00</span>
            </div>

            <button type="submit" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-500">Confirm Booking</button>
        </form>

        <div class="mt-6 grid grid-cols-4 gap-4">
            <?php foreach ($tiers as $tier): ?>
                <p class="flex items-center text-white">
                    <span class="inline-block w-5 h-5 <?= e($tierColors[$tier['tierID']] ?? 'bg-gray-200') ?> mr-2"></span>
                    <?= e($tier['tierName']) ?> ($<?= number_format((float)$tier['basePrice'], 2) ?>)
                </p>
            <?php endforeach; ?>
            <p class="flex items-center text-white"><span class="inline-block w-5 h-5 bg-gray-400 mr-2"></span>Booked</p>
        </div>
    </div>

    <script>
        const tierPrices = <?= json_encode($tierPrices) ?>;
    </script>
    <script src="public/js/seat_selection.js"></script>
</body>

</html>
