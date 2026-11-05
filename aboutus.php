<?php
require_once __DIR__ . '/includes/bootstrap.php';

$aboutItems = (new AboutUs(db()))->getAll();

$pageTitle = 'About Us - DWP';
include __DIR__ . '/includes/head.php';
?>

<body class="bg-neutral-900 text-white">
    <?php include __DIR__ . '/includes/navbar.php'; ?>
    <main class="max-w-6xl mx-auto px-6 py-16">
        <h1 class="text-3xl font-bold mb-8 text-center">About Us</h1>
        <?php foreach ($aboutItems as $item): ?>
            <section class="bg-neutral-800 p-6 rounded shadow mb-6">
                <h2 class="text-xl font-semibold mb-2"><?= e($item['aboutTitle']) ?></h2>
                <p class="text-white mb-2"><?= nl2br(e($item['aboutContent'])) ?></p>
                <?php if ($item['aboutFooter']): ?>
                    <p class="text-gray-200 text-sm"><?= e($item['aboutFooter']) ?></p>
                <?php endif; ?>
            </section>
        <?php endforeach; ?>
    </main>
    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>

</html>
