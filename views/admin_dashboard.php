<?php
session_start();
require_once __DIR__ . '/../controllers/AdminSupportController.php';
require_once "../controllers/AdminController.php";
require_once __DIR__ . '/../classes/Database.php';
require_once "../controllers/TournamentController.php";
require_once "../controllers/NewsController.php";
require_once "../controllers/GameController.php";
include __DIR__ . '/../includes/adminSidebar.php';

$pdo = Database::getInstance();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$adminController = new AdminController($pdo);
$reservationsStmt = $pdo->query("SELECT * FROM ContactForm WHERE category = 'Reservation' ORDER BY createdAt DESC");
$reservations = $reservationsStmt->fetchAll(PDO::FETCH_ASSOC);
$tournamentController = new TournamentController($pdo);
$newsController = new NewsController($pdo);
$gameController = new GameController($pdo);
$adminSupport = new AdminSupportController($pdo);
$tickets = $adminSupport->getAllTickets();


$users = $adminController->getAllUsers();
$tournaments = $tournamentController->getAllTournaments();
$news = $newsController->getAllNews();
$games = $gameController->getAllGames();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - DWP Esports Cinema</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-neutral-900 min-h-screen flex">

    <?php include "../includes/adminSidebar.php"; ?>

    <div class="flex-1 ml-64 p-8">
        <header class="flex justify-between items-center mb-8 border-b pb-4">
            <h1 class="text-2xl text-white font-semibold">Admin Dashboard</h1>
            <a href="logout.php" class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-500 text-sm">Logout</a>
        </header>

        <section class="grid md:grid-cols-3 gap-6 mb-10">
            <div class="bg-neutral-800 p-6 rounded-lg shadow text-center">
                <h2 class="text-gray-400 text-sm uppercase mb-2">Tournaments</h2>
                <p class="text-3xl font-bold text-white"><?= count($tournaments) ?></p>
            </div>
            <div class="bg-neutral-800 p-6 rounded-lg shadow text-center">
                <h2 class="text-gray-400 text-sm uppercase mb-2">News Articles</h2>
                <p class="text-3xl font-bold text-white"><?= count($news) ?></p>
            </div>
            <div class="bg-neutral-800 p-6 rounded-lg shadow text-center">
                <h2 class="text-gray-400 text-sm uppercase mb-2">Registered Users</h2>
                <p class="text-3xl font-bold text-white"><?= count($users) ?></p>
            </div>
        </section>

        <h2 class="text-xl text-white font-semibold mb-6">Manage Content</h2>
        <div class="grid md:grid-cols-3 gap-6">

            <div class="bg-neutral-800 rounded shadow p-6">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-lg text-white font-semibold">Tournaments</h3>
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

            <div class="bg-neutral-800 rounded shadow p-6">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-lg text-white font-semibold">News</h3>
                    <a href="news.php" class="text-blue-600 hover:underline text-sm">Manage</a>
                </div>
                <ul class="space-y-2 text-sm text-gray-700 max-h-72 overflow-y-auto">
                    <?php foreach ($news as $n): ?>
                        <li><?= htmlspecialchars($n['newsTitle']) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="bg-neutral-800 rounded shadow p-6">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-lg text-white font-semibold">Users</h3>
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
            <div class="bg-white rounded shadow p-6">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-lg font-semibold">Games</h3>
                    <a href="games.php" class="text-blue-600 hover:underline text-sm">Manage</a>
                </div>
                <ul class="space-y-2 text-sm text-gray-700 max-h-72 overflow-y-auto">
                    <?php foreach ($games as $g): ?>
                        <li>
                            <span class="font-medium"><?= htmlspecialchars($g['gameName']) ?></span>
                            <?php if (!empty($g['genre'])): ?>
                                <span class="text-gray-500">(<?= htmlspecialchars($g['genre']) ?>)</span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>



        </div>
    </div>

</body>

</html>