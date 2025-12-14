<?php
session_start();
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/controllers/AboutUsController.php';

$pdo = Database::getInstance();
$ctrl = new AboutUsController($pdo);
$aboutItems = $ctrl->getAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>About Us - DWP</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-white text-gray-900">
    <main class="max-w-6xl mx-auto px-6 py-16">
        <h1 class="text-3xl font-bold mb-8 text-center">About Us</h1>
        <?php foreach ($aboutItems as $item): ?>
            <section class="bg-gray-100 p-6 rounded shadow mb-6">
                <h2 class="text-xl font-semibold mb-2"><?= htmlspecialchars($item['aboutTitle']) ?></h2>
                <p class="text-gray-700 mb-2"><?= nl2br(htmlspecialchars($item['aboutContent'])) ?></p>
                <?php if ($item['aboutFooter']): ?>
                    <p class="text-gray-500 text-sm"><?= htmlspecialchars($item['aboutFooter']) ?></p>
                <?php endif; ?>
            </section>
        <?php endforeach; ?>
    </main>
</body>

</html>