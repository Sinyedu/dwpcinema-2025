<?php
session_start();
require_once "../database/connection.php";
require_once "../controllers/AdminController.php";
require_once "../controllers/TournamentController.php";
require_once "../controllers/NewsController.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../public/admin_login.php");
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
<body class="bg-gray-100 text-gray-900 min-h-screen">

  <header class="bg-white border-b border-gray-200">
    <div class="max-w-6xl mx-auto px-6 py-4 flex justify-between items-center">
      <h1 class="text-xl font-semibold">Admin Dashboard</h1>
      <a href="../../public/logout.php" class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-500 text-sm">Logout</a>
    </div>
  </header>

  <main class="max-w-6xl mx-auto px-6 py-10">
    <h2 class="text-2xl font-semibold mb-8">Manage Content</h2>

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

      <div class="bg-white rounded shadow p-6">
        <div class="flex justify-between items-center mb-3">
          <h3 class="text-lg font-semibold">Users</h3>
          <a href="users.php" class="text-blue-600 hover:underline text-sm">Manage</a>
        </div>
        <ul class="space-y-2 text-sm text-gray-700 max-h-72 overflow-y-auto">
          <?php foreach ($users as $u): ?>
            <li><?= htmlspecialchars($u['firstName'] . ' ' . $u['lastName']) ?> <span class="text-gray-400">(<?= htmlspecialchars($u['userEmail']) ?>)</span></li>
          <?php endforeach; ?>
        </ul>
      </div>

    </div>
  </main>

</body>
</html>
