<?php
session_start();
include __DIR__ . '/../includes/navbar.php';

$pdo = new PDO("mysql:host=localhost;dbname=dwpcinemaDB;charset=utf8", "root", "");

$stmt = $pdo->query("
    SELECT t.tournamentID, 
           t.tournamentName, 
           t.tournamentDescription, 
           t.startDate, 
           g.gameName,
           g.gameGenre,
           (SELECT COUNT(*) FROM `Match` m 
            JOIN Showing s ON m.matchID = s.matchID
            WHERE m.tournamentID = t.tournamentID
            AND s.showingDate >= CURDATE()) AS upcomingShowings,
           (SELECT MIN(s.showingDate) FROM `Match` m 
            JOIN Showing s ON m.matchID = s.matchID
            WHERE m.tournamentID = t.tournamentID
            AND s.showingDate >= CURDATE()) AS nextShowing
    FROM Tournament t
    JOIN Game g ON t.gameID = g.gameID
    ORDER BY t.startDate DESC
    LIMIT 3
");
$featured = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt2 = $pdo->query("
    SELECT newsID, newsTitle, newsContent, newsAuthor, newsImage, newsCreatedAt
    FROM News
    ORDER BY newsCreatedAt DESC
    LIMIT 3
");
$news = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>DWP Esports Cinema</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white text-gray-900">

<section class="relative bg-gray-900 text-white">
    <img src="https://via.placeholder.com/1600x600" alt="Hero" class="w-full h-[400px] object-cover opacity-80">
    <div class="absolute inset-0 flex flex-col justify-center items-center text-center px-6">
        <h2 class="text-4xl font-bold">Esports on the Big Screen</h2>
        <p class="mt-4 text-lg text-gray-200 max-w-2xl">
            Book your seat for the biggest esports tournaments and join the community experience.
        </p>
        <a href="register.php" class="mt-6 inline-block px-6 py-3 rounded-md font-medium bg-blue-600 hover:bg-blue-500 transition">
            Get Started
        </a>
    </div>
</section>

<section id="tournaments" class="max-w-6xl mx-auto px-6 py-16">
    <h3 class="text-2xl font-semibold text-center mb-6">Featured Tournaments</h3>
    <div class="grid md:grid-cols-3 gap-6">
        <?php foreach($featured as $t): ?>
        <div class="bg-gray-100 rounded-lg overflow-hidden shadow hover:shadow-lg transition">
            <img src="https://via.placeholder.com/600x400" alt="<?= htmlspecialchars($t['tournamentName']) ?>" class="w-full h-48 object-cover">
            <div class="p-4">
                <h4 class="font-semibold text-lg"><?= htmlspecialchars($t['tournamentName']) ?></h4>
                <p class="text-sm text-gray-600 mt-1"><?= htmlspecialchars($t['gameName']) ?> — <?= htmlspecialchars($t['startDate']) ?></p>
                <p class="text-gray-700 text-sm mt-2"><?= htmlspecialchars(substr($t['tournamentDescription'], 0, 120)) ?>...</p>
                
                <?php if ($t['upcomingShowings'] > 0): ?>
                    <p class="text-green-600 text-sm font-medium mt-1">Next Showing: <?= htmlspecialchars($t['nextShowing']) ?></p>
                    <a href="showings.php?tournamentID=<?= $t['tournamentID'] ?>" class="mt-2 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-500 font-medium">
                        View Showings
                    </a>
                <?php else: ?>
                    <p class="text-red-600 font-medium mt-2">No upcoming showings</p>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<section id="news" class="bg-gray-50 border-t border-gray-200 py-16">
    <div class="max-w-6xl mx-auto px-6">
        <h3 class="text-2xl font-semibold text-center mb-6">Latest News</h3>
        <div class="grid md:grid-cols-3 gap-6">
            <?php foreach($news as $n): ?>
            <div class="bg-white rounded-lg shadow hover:shadow-lg transition overflow-hidden">
                <?php if($n['newsImage']): ?>
                    <img src="<?= htmlspecialchars($n['newsImage']) ?>" alt="<?= htmlspecialchars($n['newsTitle']) ?>" class="w-full h-40 object-cover">
                <?php endif; ?>
                <div class="p-4">
                    <h4 class="font-semibold text-lg"><?= htmlspecialchars($n['newsTitle']) ?></h4>
                    <p class="text-gray-700 text-sm mt-1"><?= htmlspecialchars(substr($n['newsContent'], 0, 100)) ?>...</p>
                    <p class="text-gray-500 text-xs mt-2">By <?= htmlspecialchars($n['newsAuthor']) ?> | <?= date('M d, Y', strtotime($n['newsCreatedAt'])) ?></p>
                    <a href="news.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium mt-1 inline-block">Read More</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

</body>
</html>
