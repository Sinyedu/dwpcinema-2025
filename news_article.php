<?php
require_once __DIR__ . '/includes/bootstrap.php';

$newsID = filter_input(INPUT_GET, 'newsID', FILTER_VALIDATE_INT);
$newsItem = $newsID ? (new News(db()))->getNewsById($newsID) : null;

if (!$newsItem) {
    redirect('news.php');
}

$pageTitle = $newsItem['newsTitle'] . ' - DWP Esports Cinema';
include __DIR__ . '/includes/head.php';
?>

<body class="bg-neutral-900 text-white font-sans">
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <main class="max-w-4xl mx-auto px-6 py-16">
        <h1 class="text-3xl md:text-4xl font-bold mb-4 text-white">
            <?= e($newsItem['newsTitle']) ?>
        </h1>

        <p class="text-gray-400 text-sm mb-6">
            By <?= e($newsItem['newsAuthor']) ?> | <?= e(date('F j, Y', strtotime($newsItem['newsCreatedAt']))) ?>
        </p>

        <?php if ($newsItem['newsImage']): ?>
            <img src="<?= e($newsItem['newsImage']) ?>"
                alt="<?= e($newsItem['newsTitle']) ?>"
                class="w-full h-64 md:h-80 object-cover mb-8 rounded-lg shadow-md">
        <?php endif; ?>

        <div class="prose max-w-full text-gray-300 leading-relaxed">
            <?= nl2br(e($newsItem['newsContent'])) ?>
        </div>

        <a href="news.php" class="inline-block mt-12 px-5 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-500 transition">
            &larr; Back to News
        </a>
    </main>

</body>

</html>
