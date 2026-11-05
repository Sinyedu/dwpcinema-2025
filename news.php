<?php
require_once __DIR__ . '/includes/bootstrap.php';

$news = (new News(db()))->getAll();

$pageTitle = 'News - DWP Esports Cinema';
include __DIR__ . '/includes/head.php';
?>

<body class="bg-neutral-900 text-white">
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <main class="max-w-6xl mx-auto px-6 py-16">
        <h2 class="text-2xl font-semibold mb-6 text-center">Latest News</h2>
        <div class="grid md:grid-cols-3 gap-6">
            <?php foreach ($news as $n): ?>
                <a href="news_article.php?newsID=<?= (int)$n['newsID'] ?>" class="block bg-neutral-900 rounded-lg shadow hover:shadow-lg transition overflow-hidden">
                    <?php if ($n['newsImage']): ?>
                        <img src="<?= e($n['newsImage']) ?>" alt="<?= e($n['newsTitle']) ?>" class="w-full h-40 object-cover">
                    <?php endif; ?>
                    <div class="p-4">
                        <h3 class="font-semibold text-lg"><?= e($n['newsTitle']) ?></h3>
                        <p class="text-white text-sm mt-1"><?= e(mb_substr((string)$n['newsContent'], 0, 120)) ?>...</p>
                        <p class="text-gray-500 text-xs mt-2">By <?= e($n['newsAuthor']) ?> | <?= e($n['newsCreatedAt']) ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </main>

</body>

</html>
