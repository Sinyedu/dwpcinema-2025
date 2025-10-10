<?php
session_start();
$pdo = new PDO("mysql:host=localhost;dbname=dwpcinemaDB;charset=utf8", "root", "");

$stmt = $pdo->query("
    SELECT newsID, 
           newsTitle, 
           newsContent, 
           newsAuthor, 
           newsImage, 
           newsCreatedAt
    FROM News
    ORDER BY newsCreatedAt DESC
");
$news = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>News - DWP Esports Cinema</title>
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
    <h2 class="text-2xl font-semibold mb-6 text-center">Latest News</h2>
    <div class="grid md:grid-cols-3 gap-6">
        <?php foreach ($news as $n): ?>
            <div class="bg-white rounded-lg shadow hover:shadow-lg transition overflow-hidden">
                <?php if ($n['newsImage']): ?>
                    <img src="<?= htmlspecialchars($n['newsImage']) ?>" alt="<?= htmlspecialchars($n['newsTitle']) ?>" class="w-full h-40 object-cover">
                <?php endif; ?>
                <div class="p-4">
                    <h3 class="font-semibold text-lg"><?= htmlspecialchars($n['newsTitle']) ?></h3>
                    <p class="text-gray-700 text-sm mt-1"><?= htmlspecialchars(substr($n['newsContent'], 0, 120)) ?>...</p>
                    <p class="text-gray-500 text-xs mt-2">By <?= htmlspecialchars($n['newsAuthor']) ?> | <?= htmlspecialchars($n['newsCreatedAt']) ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

</body>
</html>
