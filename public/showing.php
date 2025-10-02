<?php
session_start();
$pdo = new PDO("mysql:host=localhost;dbname=dwpcinemaDB;charset=utf8", "root", "");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['showingID'])) {
    $userID = $_SESSION['user_id'];
    $showingID = (int)$_POST['showingID'];

    $stmt = $pdo->prepare("INSERT INTO Booking (userID, showingID, bookingDate) VALUES (?, ?, NOW())");
    if ($stmt->execute([$userID, $showingID])) {
        $success = "Booking successful!";
    } else {
        $error = "Booking failed. Try again.";
    }
}

$tournaments = $pdo->query("SELECT tournamentID, tournamentName FROM Tournament ORDER BY startDate ASC")->fetchAll(PDO::FETCH_ASSOC);

$selectedTournamentID = $_GET['tournamentID'] ?? null;

$where = "";
$params = [];
if ($selectedTournamentID) {
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
        (SELECT COUNT(*) FROM Booking b WHERE b.showingID = s.showingID) AS bookedSeats
    FROM Showing s
    JOIN Hall h ON s.hallID = h.hallID
    JOIN `Match` m ON s.matchID = m.matchID
    JOIN Tournament t ON m.tournamentID = t.tournamentID
    $where
    ORDER BY s.showingDate, s.showingTime
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

    <?php if (!empty($success)): ?>
        <p class="mb-4 p-2 bg-green-100 text-green-800 rounded"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <p class="mb-4 p-2 bg-red-100 text-red-800 rounded"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="GET" class="mb-8">
        <label for="tournamentID" class="block mb-2 font-semibold">Filter by Tournament:</label>
        <select name="tournamentID" id="tournamentID" class="w-full border px-3 py-2 rounded mb-2">
            <option value="">-- All Tournaments --</option>
            <?php foreach ($tournaments as $t): ?>
                <option value="<?= $t['tournamentID'] ?>" <?= ($t['tournamentID'] == $selectedTournamentID) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($t['tournamentName']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-500">Filter</button>
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

            <?php if($availableSeats > 0): ?>
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
