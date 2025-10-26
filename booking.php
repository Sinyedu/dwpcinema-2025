<?php
session_start();
include __DIR__ . '/../includes/navbar.php';

$pdo = new PDO("mysql:host=mysql119.unoeuro.com;dbname=simonnyblom_com_db;charset=utf8", "simonnyblom_com", "hwEG2df9rADt5gab4kFp");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once "../models/Showing.php";
require_once "../models/Booking.php";
require_once "../controllers/BookingController.php";

$showingModel = new Showing($pdo);
$bookingModel = new Booking($pdo);
$controller = new BookingController($showingModel, $bookingModel);

$success = $error = '';
$selectedTournamentID = $_GET['tournamentID'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['showingID'])) {
    $userID = $_SESSION['user_id'];
    $showingID = (int)$_POST['showingID'];

    if ($controller->book($userID, $showingID)) {
        $success = "Booking successful!";
    } else {
        $error = "Booking failed. Try again.";
    }
}

$tournaments = $pdo->query("SELECT tournamentID, tournamentName FROM Tournament ORDER BY startDate ASC")->fetchAll(PDO::FETCH_ASSOC);

$showings = $controller->listShowings($selectedTournamentID);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Book a Showing - DWP Cinema</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen text-gray-900">

<div class="max-w-3xl mx-auto px-6 py-10">
    <h1 class="text-2xl font-bold mb-6">Book a Showing</h1>

    <?php if ($success): ?>
        <p class="mb-4 p-2 bg-green-100 text-green-800 rounded"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p class="mb-4 p-2 bg-red-100 text-red-800 rounded"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="GET" class="mb-6">
        <label for="tournamentID" class="block mb-2 font-semibold">Select Tournament:</label>
        <select name="tournamentID" id="tournamentID" class="w-full border px-3 py-2 rounded mb-2">
            <option value="">-- Choose a Tournament --</option>
            <?php foreach ($tournaments as $t): ?>
                <option value="<?= $t['tournamentID'] ?>" <?= ($t['tournamentID'] == $selectedTournamentID) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($t['tournamentName']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-500">View Showings</button>
    </form>

    <div class="grid md:grid-cols-3 gap-6">
        <?php foreach ($showings as $s):
            $availableSeats = $s['totalSeats'] - $s['bookedSeats'];
        ?>
        <div class="bg-white p-4 rounded shadow hover:shadow-lg transition">
            <h2 class="font-semibold text-lg"><?= htmlspecialchars($s['tournamentName']) ?></h2>
            <p class="text-sm text-gray-600"><?= htmlspecialchars($s['showingDate']) ?> at <?= htmlspecialchars($s['showingTime']) ?></p>
            <p class="text-sm text-gray-600">Hall: <?= htmlspecialchars($s['hallName']) ?></p>
            <p class="text-sm text-gray-600">Seats: <?= $availableSeats ?> available / <?= $s['totalSeats'] ?> total</p>

            <?php if ($availableSeats > 0): ?>
            <form method="POST" class="mt-3">
                <input type="hidden" name="showingID" value="<?= $s['showingID'] ?>">
                <button type="submit" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-500">Book Now</button>
            </form>
            <?php else: ?>
            <p class="mt-3 text-red-600 font-semibold">Sold Out</p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($showings)): ?>
        <p class="text-gray-700 mt-4">No showings available for this tournament.</p>
    <?php endif; ?>
</div>
</body>
</html>
