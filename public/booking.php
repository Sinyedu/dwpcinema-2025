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
        header("Location: view_showing.php?showingID=" . $showingID . "&success=1");
        exit; 
    } else {
        $error = "Booking failed. Try again.";
    }
}

$tournaments = $pdo->query("SELECT tournamentID, tournamentName FROM Tournament ORDER BY startDate ASC")->fetchAll(PDO::FETCH_ASSOC);

$showings = [];
$selectedTournamentID = $_GET['tournamentID'] ?? null;

if ($selectedTournamentID) {
    $stmt = $pdo->prepare("
        SELECT s.showingID, s.showingDate, s.showingTime, h.hallName, m.matchID
        FROM Showing s
        JOIN `Match` m ON s.matchID = m.matchID
        JOIN Hall h ON s.hallID = h.hallID
        WHERE m.tournamentID = ?
        ORDER BY s.showingDate, s.showingTime
    ");
    $stmt->execute([$selectedTournamentID]);
    $showings = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
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

    <?php if (!empty($success)): ?>
        <p class="mb-4 p-2 bg-green-100 text-green-800 rounded"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
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

    <?php if (!empty($showings)): ?>
        <form method="POST">
            <label for="showingID" class="block mb-2 font-semibold">Select Showing:</label>
            <select name="showingID" id="showingID" class="w-full border px-3 py-2 rounded mb-4">
                <?php foreach ($showings as $s): ?>
                    <option value="<?= $s['showingID'] ?>">
                        <?= htmlspecialchars($s['showingDate']) ?> <?= htmlspecialchars($s['showingTime']) ?> — <?= htmlspecialchars($s['hallName']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-500">Book Now</button>
        </form>
    <?php elseif ($selectedTournamentID): ?>
        <p class="text-gray-700">No showings available for this tournament.</p>
    <?php endif; ?>
</div>

</body>
</html>
