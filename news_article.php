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
    <title><?= htmlspecialchars($newsItem['newsTitle'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> - DWP Esports Cinema</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-white text-gray-900 font-sans">

    <main class="max-w-4xl mx-auto px-6 py-16">
        <h1 class="text-3xl md:text-4xl font-bold mb-4 text-gray-900">
            <?= htmlspecialchars($newsItem['newsTitle'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        </h1>

        <p class="text-gray-500 text-sm mb-6">
            By <?= htmlspecialchars($newsItem['newsAuthor'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> | <?= date('F j, Y', strtotime($newsItem['newsCreatedAt'])) ?>
        </p>

        <?php if ($newsItem['newsImage']): ?>
            <img src="<?= htmlspecialchars($newsItem['newsImage'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                alt="<?= htmlspecialchars($newsItem['newsTitle'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                class="w-full h-64 md:h-80 object-cover mb-8 rounded-lg shadow-md">
        <?php endif; ?>

        <div class="prose max-w-full text-gray-800 leading-relaxed">
            <?= $newsItem['newsContent'] ?>
        </div>

        <a href="news.php" class="inline-block mt-12 px-5 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            &larr; Back to News
        </a>
    </main>

</body>

</html>