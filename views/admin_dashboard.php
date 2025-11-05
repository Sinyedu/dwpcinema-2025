<?php
session_start();
require_once "../controllers/AdminController.php";
require_once __DIR__ . '/../classes/Database.php';
require_once "../controllers/TournamentController.php";
require_once "../controllers/NewsController.php";
include __DIR__ . '/../includes/adminSidebar.php';

$pdo = Database::getInstance();

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../public/admin_login.php");
    exit;
}

$adminController = new AdminController($pdo);
$tournamentController = new TournamentController($pdo);
$newsController = new NewsController($pdo);

$users = $adminController->getAllUsers();
$tournaments = $tournamentController->getAllTournaments();
$news = $newsController->getAllNews();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - DWP Esports Cinema</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex">

<?php include "../includes/adminSidebar.php"; ?>

<div class="flex-1 ml-64 p-8">
    <header class="flex justify-between items-center mb-8 border-b pb-4">
        <h1 class="text-2xl font-semibold">Admin Dashboard</h1>
        <a href="../../public/logout.php" class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-500 text-sm">Logout</a>
    </header>

    <section class="grid md:grid-cols-3 gap-6 mb-10">
        <div class="bg-white p-6 rounded-lg shadow text-center">
            <h2 class="text-gray-500 text-sm uppercase mb-2">Tournaments</h2>
            <p class="text-3xl font-bold"><?= count($tournaments) ?></p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow text-center">
            <h2 class="text-gray-500 text-sm uppercase mb-2">News Articles</h2>
            <p class="text-3xl font-bold"><?= count($news) ?></p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow text-center">
            <h2 class="text-gray-500 text-sm uppercase mb-2">Registered Users</h2>
            <p class="text-3xl font-bold"><?= count($users) ?></p>
        </div>
    </section>

    <h2 class="text-xl font-semibold mb-6">Manage Content</h2>
    <div class="grid md:grid-cols-3 gap-6">

        <div class="bg-white rounded shadow p-6">
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-lg font-semibold">Tournaments</h3>
                <a href="tournaments.php" class="text-blue-600 hover:underline text-sm">Manage</a>
            </div>
            <ul class="space-y-2 text-sm text-gray-700 max-h-72 overflow-y-auto">
                <?php foreach ($tournaments as $t): ?>
                    <li>
                        <span class="font-medium"><?= htmlspecialchars($t['tournamentName']) ?></span>
                        <span class="text-gray-500">(<?= htmlspecialchars($t['startDate']) ?>)</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- NEWS PREVIEW -->
        <div class="bg-white rounded shadow p-6">
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-lg font-semibold">News</h3>
                <a href="news.php" class="text-blue-600 hover:underline text-sm">Manage</a>
            </div>
            <ul class="space-y-2 text-sm text-gray-700 max-h-72 overflow-y-auto">
                <?php foreach ($news as $n): ?>
                    <li><?= htmlspecialchars($n['newsTitle']) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- USERS PREVIEW -->
        <div class="bg-white rounded shadow p-6">
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-lg font-semibold">Users</h3>
                <a href="users.php" class="text-blue-600 hover:underline text-sm">Manage</a>
            </div>
            <ul class="space-y-2 text-sm text-gray-700 max-h-72 overflow-y-auto">
                <?php foreach ($users as $u): ?>
                    <li>
                        <?= htmlspecialchars($u['firstName'] . ' ' . $u['lastName']) ?>
                        <span class="text-gray-400">(<?= htmlspecialchars($u['userEmail']) ?>)</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

    </div>
</div>

</body>
</html>
