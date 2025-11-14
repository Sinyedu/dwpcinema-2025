<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit;
}

$current_page = basename($_SERVER['PHP_SELF']);
?>

<aside class="bg-gray-900 text-white w-64 min-h-screen fixed top-0 left-0 flex flex-col">
    <div class="p-6 text-2xl font-bold border-b border-gray-700">
        <a href="admin_dashboard.php" class="hover:text-blue-400">Admin Panel</a>
    </div>

    <nav class="flex-1 p-4 space-y-2">
        <a href="admin_dashboard.php" class="block px-4 py-2 rounded hover:bg-gray-700 <?= $current_page === 'dashboard.php' ? 'bg-gray-800' : '' ?>">Dashboard</a>
        <a href="news.php" class="block px-4 py-2 rounded hover:bg-gray-700 <?= $current_page === 'news.php' ? 'bg-gray-800' : '' ?>">Manage News</a>
        <a href="tournaments.php" class="block px-4 py-2 rounded hover:bg-gray-700 <?= $current_page === 'tournaments.php' ? 'bg-gray-800' : '' ?>">Manage Tournaments</a>
        <a href="games.php" class="block px-4 py-2 rounded hover:bg-gray-700 <?= $current_page === 'games.php' ? 'bg-gray-800' : '' ?>">Manage Games</a>
        <a href="users.php" class="block px-4 py-2 rounded hover:bg-gray-700 <?= $current_page === 'users.php' ? 'bg-gray-800' : '' ?>">Manage Users</a>
    </nav>

    <div class="p-4 border-t border-gray-700">
        <a href="../views/logout.php" class="block bg-red-600 hover:bg-red-500 text-center py-2 rounded">Logout</a>
    </div>
</aside>