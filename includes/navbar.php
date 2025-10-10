<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<nav class="flex gap-6 text-sm items-center bg-white shadow px-6 py-3 rounded-b-lg">
    <a href="index.php" class="hover:text-gray-800 font-medium">Home</a>
    <a href="tournaments.php" class="hover:text-gray-800 font-medium">Tournaments</a>
    <a href="news.php" class="hover:text-gray-800 font-medium">News</a>
    <?php if(isset($_SESSION['user_id'])): ?>
        <a href="users.php" class="hover:text-gray-800 font-medium">Community</a>
        <span class="text-gray-600">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
        <a href="logout.php" class="hover:text-gray-800 font-medium">Logout</a>
    <?php else: ?>
        <a href="admin_login.php" class="hover:text-gray-800 font-medium">Admin Login</a>
        <a href="login.php" class="hover:text-gray-800 font-medium">Login</a>
        <a href="register.php" class="hover:text-gray-800 font-medium">Register</a>
    <?php endif; ?>
</nav>
