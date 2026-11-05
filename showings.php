<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_user();

$selectedTournamentID = filter_input(INPUT_GET, 'tournamentID', FILTER_VALIDATE_INT) ?: null;
$tournaments = (new Tournament(db()))->getVisibleOptions();
$showings = (new Showing(db()))->upcoming($selectedTournamentID);

$pageTitle = 'Showings - DWP Cinema';
include __DIR__ . '/includes/head.php';
?>

<body class="bg-neutral-900 min-h-screen text-white">
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <div class="max-w-6xl mx-auto px-6 py-10">
        <h1 class="text-2xl font-bold mb-6">Upcoming Showings</h1>

        <form method="GET" class="mb-8">
            <label for="tournamentID" class="block mb-2 font-semibold">Filter by Tournament:</label>
            <select name="tournamentID" id="tournamentID" class="w-full border px-3 py-2 rounded mb-2 bg-neutral-800 text-white">
                <option value="">-- All Tournaments --</option>
                <?php foreach ($tournaments as $t): ?>
                    <option value="<?= (int)$t['tournamentID'] ?>" <?= ((int)$t['tournamentID'] === $selectedTournamentID) ? 'selected' : '' ?>>
                        <?= e($t['tournamentName']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-500">Filter</button>
        </form>

        <?php if ($showings): ?>
            <div class="grid md:grid-cols-3 gap-6">
                <?php foreach ($showings as $s):
                    $availableSeats = $s['totalSeats'] - $s['bookedSeats'];
                ?>
                    <div class="bg-neutral-800 p-4 rounded shadow hover:shadow-lg transition">
                        <h2 class="font-semibold text-lg"><?= e($s['matchName']) ?></h2>
                        <p class="text-sm text-gray-400"><?= e($s['tournamentName']) ?></p>
                        <p class="text-sm text-gray-400"><?= e(date('F j, Y', strtotime($s['showingDate']))) ?> at <?= e(date('H:i', strtotime($s['showingTime']))) ?></p>
                        <p class="text-sm text-gray-400">Hall: <?= e($s['hallName']) ?></p>
                        <p class="text-sm text-gray-400">Seats: <?= (int)$availableSeats ?> available / <?= (int)$s['totalSeats'] ?> total</p>

                        <?php if ($availableSeats > 0): ?>
                            <form method="GET" action="view_showing.php" class="mt-3">
                                <input type="hidden" name="showingID" value="<?= (int)$s['showingID'] ?>">
                                <button type="submit" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-500">View &amp; Book Seats</button>
                            </form>
                        <?php else: ?>
                            <p class="mt-3 text-red-600 font-semibold">Sold Out</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-gray-400 mt-4">No upcoming showings available<?= $selectedTournamentID ? ' for this tournament' : '' ?>.</p>
        <?php endif; ?>
    </div>
</body>

</html>
