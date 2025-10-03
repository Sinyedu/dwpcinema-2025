<?php
session_start();
$pdo = new PDO("mysql:host=localhost;dbname=dwpcinemaDB;charset=utf8", "root", "");

$stmt = $pdo->query("
    SELECT t.tournamentID, t.tournamentName, t.tournamentDescription, t.startDate, t.endDate, g.gameName
    FROM Tournament t
    JOIN Game g ON t.gameID = g.gameID
    ORDER BY t.startDate ASC
");
$tournaments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Tournaments - DWP Esports Cinema</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white text-gray-900">

<header class="bg-gray-100 border-b border-gray-300">
    <div class="max-w-6xl mx-auto px-6 py-4 flex justify-between items-center">
        <h1 class="text-xl font-bold">DWP Esports Cinema</h1>
        <nav class="flex gap-6 text-sm items-center">
            <a href="index.php" class="hover:text-gray-800 font-medium">Home</a>
            <a href="tournaments.php" class="hover:text-gray-800 font-medium">Tournaments</a>
            <a href="news.php" class="hover:text-gray-800 font-medium">News</a>
        </nav>
    </div>
</header>

<main class="max-w-6xl mx-auto px-6 py-16">
    <h2 class="text-2xl font-semibold mb-6 text-center">All Tournaments</h2>
    <div class="grid md:grid-cols-3 gap-6">
        <?php foreach ($tournaments as $t): ?>
            <div class="bg-gray-100 rounded-lg shadow hover:shadow-lg transition overflow-hidden">
                <div class="p-4">
                    <h3 class="font-semibold text-lg"><?= htmlspecialchars($t['tournamentName']) ?></h3>
                    <p class="text-gray-600 text-sm mt-1"><?= htmlspecialchars($t['gameName']) ?> | <?= htmlspecialchars($t['startDate']) ?> - <?= htmlspecialchars($t['endDate']) ?></p>
                    <p class="mt-2 text-gray-700 text-sm"><?= htmlspecialchars(substr($t['tournamentDescription'], 0, 120)) ?>...</p>
                    <a href="booking.php?tournamentID=<?= $t['tournamentID'] ?>" class="mt-2 inline-block text-blue-600 hover:text-blue-800 font-medium">Book Tickets</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

</body>
</html>
