<?php
session_start();
include __DIR__ . '/includes/navbar.php';
require_once __DIR__ . '/classes/Database.php';

if (!isset($_GET['newsID']) || !is_numeric($_GET['newsID'])) {
    header('Location: news.php');
    exit;
}

$newsID = (int)$_GET['newsID'];
$pdo = Database::getInstance();

$stmt = $pdo->prepare("
    SELECT newsTitle, newsContent, newsAuthor, newsImage, newsCreatedAt
    FROM News
    WHERE newsID = ?
");
$stmt->execute([$newsID]);
$newsItem = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$newsItem) {
    header('Location: news.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($newsItem['newsTitle']) ?> - DWP Esports Cinema</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-white text-gray-900">

    <main class="max-w-4xl mx-auto px-6 py-16">
        <h1 class="text-3xl font-bold mb-4"><?= htmlspecialchars($newsItem['newsTitle']) ?></h1>
        <p class="text-gray-500 text-sm mb-6">By <?= htmlspecialchars($newsItem['newsAuthor']) ?> | <?= htmlspecialchars($newsItem['newsCreatedAt']) ?></p>

        <?php if ($newsItem['newsImage']): ?>
            <img src="<?= htmlspecialchars($newsItem['newsImage']) ?>" alt="<?= htmlspecialchars($newsItem['newsTitle']) ?>" class="w-full h-64 object-cover mb-6 rounded">
        <?php endif; ?>

        <div class="prose max-w-full">
            <?= nl2br(htmlspecialchars($newsItem['newsContent'])) ?>
        </div>

        <a href="news.php" class="inline-block mt-8 text-blue-600 hover:underline">&larr; Back to News</a>
    </main>

</body>

</html>