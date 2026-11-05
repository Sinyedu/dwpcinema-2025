<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_user();

$bookingID = filter_input(INPUT_GET, 'bookingID', FILTER_VALIDATE_INT);
// Scoped to the logged-in user: other people's booking ids look exactly like missing ones.
$booking = $bookingID ? (new Booking(db()))->getDetailsForUser($bookingID, $auth->userId()) : null;

if (!$booking) {
    http_response_code(404);
    exit('Booking not found.');
}

$tierColors = ['VIP' => 'bg-blue-400', 'Standard' => 'bg-green-400'];

$pageTitle = 'Booking Success - ' . $booking['tournamentName'];
include __DIR__ . '/includes/head.php';
?>

<body class="bg-neutral-900 text-white min-h-screen">
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <div class="max-w-4xl mx-auto px-6 py-10">
        <h1 class="text-2xl font-bold mb-4 text-green-600">Booking Confirmed!</h1>

        <div class="bg-neutral-800 rounded shadow p-6 mb-6">
            <h2 class="text-xl font-semibold mb-2"><?= e($booking['tournamentName']) ?></h2>
            <p class="text-gray-300 mb-1"><?= e($booking['gameName']) ?></p>
            <p class="text-gray-300 mb-1">Hall: <?= e($booking['hallName']) ?></p>
            <p class="text-gray-300 mb-1">Date: <?= e($booking['showingDate']) ?> | Time: <?= e($booking['showingTime']) ?></p>
            <p class="text-gray-300 mb-1">Booking Made: <?= e($booking['bookingDate']) ?></p>
        </div>

        <div class="bg-neutral-800 rounded shadow p-6 mb-6">
            <h3 class="text-lg font-semibold mb-2 text-white">Seats Booked</h3>
            <div class="flex flex-wrap gap-2 mb-2">
                <?php foreach ($booking['seats'] as $s): ?>
                    <span class="px-2 py-1 rounded text-white font-semibold <?= e($tierColors[$s['tierName']] ?? 'bg-gray-300') ?>">
                        <?= e($s['seatLabel']) ?> (<?= e($s['tierName']) ?>)
                    </span>
                <?php endforeach; ?>
            </div>
            <p class="font-semibold text-lg text-white">Total Price: $<?= number_format((float)$booking['totalAmount'], 2) ?></p>
        </div>

        <a href="showings.php" class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-500">Back to Showings</a>
    </div>
</body>

</html>
