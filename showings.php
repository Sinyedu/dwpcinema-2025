<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include __DIR__ . '/includes/navbar.php';
require_once __DIR__ . '/classes/Database.php';

$pdo = Database::getInstance();

$tournaments = $pdo->query("SELECT tournamentID, tournamentName FROM Tournament ORDER BY startDate ASC")
    ->fetchAll(PDO::FETCH_ASSOC);

$selectedTournamentID = $_GET['tournamentID'] ?? null;

$where = "";
$params = [];
if (!empty($selectedTournamentID)) {
    $where = "WHERE m.tournamentID = ?";
    $params[] = $selectedTournamentID;
}

$stmt = $pdo->prepare("
    SELECT 
        s.showingID,
        s.showingDate,
        s.showingTime,
        h.hallName,
        h.totalSeats,
        m.tournamentID,
        t.tournamentName,
        m.matchName,
        IFNULL((
            SELECT COUNT(DISTINCT bs.seatID)
            FROM Booking_Seat bs
            JOIN Booking b ON bs.bookingID = b.bookingID
            WHERE b.showingID = s.showingID
        ), 0) AS bookedSeats
    FROM Showing s
    JOIN Hall h ON s.hallID = h.hallID
    JOIN `Match` m ON s.matchID = m.matchID
    JOIN Tournament t ON m.tournamentID = t.tournamentID
    $where
    ORDER BY s.showingDate ASC, s.showingTime ASC
");


$stmt->execute($params);
$showings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Showings - DWP Cinema</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 min-h-screen text-gray-900">

    <div class="max-w-6xl mx-auto px-6 py-10">
        <h1 class="text-2xl font-bold mb-6">Showings</h1>

        <form method="GET" class="mb-8">
            <label for="tournamentID" class="block mb-2 font-semibold">Filter by Tournament:</label>
            <select name="tournamentID" id="tournamentID" class="w-full border px-3 py-2 rounded mb-2">
                <option value="">-- All Tournaments --</option>
                <?php foreach ($tournaments as $t): ?>
                    <option value="<?= (int)$t['tournamentID'] ?>" <?= ($t['tournamentID'] == $selectedTournamentID) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t['tournamentName']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-500">Filter</button>
        </form>

        <?php if (!empty($showings)): ?>
            <div class="grid md:grid-cols-3 gap-6">
                <?php foreach ($showings as $s):
                    $availableSeats = $s['totalSeats'] - $s['bookedSeats'];
                ?>
                    <div class="bg-white p-4 rounded shadow hover:shadow-lg transition">
                        <h2 class="font-semibold text-lg"><?= htmlspecialchars($s['matchName']) ?></h2>
                        <p class="text-sm text-gray-500"><?= htmlspecialchars($s['tournamentName']) ?></p>
                        <p class="text-sm text-gray-600"><?= date('F j, Y', strtotime($s['showingDate'])) ?> at <?= date('H:i', strtotime($s['showingTime'])) ?></p>
                        <p class="text-sm text-gray-600">Hall: <?= htmlspecialchars($s['hallName']) ?></p>
                        <p class="text-sm text-gray-600">Seats: <?= $availableSeats ?> available / <?= $s['totalSeats'] ?> total</p>

                        <?php if ($availableSeats > 0): ?>
                            <form method="GET" action="view_showing.php" class="mt-3">
                                <input type="hidden" name="showingID" value="<?= $s['showingID'] ?>">
                                <button type="submit" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-500">View & Book Seats</button>
                            </form>
                        <?php else: ?>
                            <p class="mt-3 text-red-600 font-semibold">Sold Out</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-gray-700 mt-4">No showings available for this tournament.</p>
        <?php endif; ?>
    </div>

</body>

</html>