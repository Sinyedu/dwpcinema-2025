<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<nav class="flex flex-wrap justify-between items-center bg-white shadow px-6 py-3 rounded-b-lg">

    <div class="flex items-center space-x-6">
        <a href="index.php" class="text-lg font-semibold text-gray-800 hover:text-blue-600">
            DWP Esports Cinema
        </a>
        <a href="tournaments.php" class="hover:text-blue-600 font-medium text-sm">Tournaments</a>
        <a href="news.php" class="hover:text-blue-600 font-medium text-sm">News</a>
        <a href="users.php" class="hover:text-blue-600 font-medium text-sm">Community</a>
    </div>

    <div class="flex items-center space-x-4">
        <?php if (isset($_SESSION['user_id'])): ?>

            <a href="user_profile.php" class="flex items-center space-x-2 hover:text-blue-600 font-medium text-sm">
               <img src="/dwpcinema-2025/public/<?= htmlspecialchars($_SESSION['user_avatar'] ?? 'uploads/avatars/default.png') ?>" 
                alt="Avatar" 
                class="w-8 h-8 rounded-full border object-cover">
                <span><?= htmlspecialchars($_SESSION['user_name']) ?></span>
            </a>

            <a href="logout.php" class="hover:text-red-600 font-medium text-sm">Logout</a>

        <?php else: ?>
            <a href="admin_login.php" class="hover:text-blue-600 font-medium text-sm">Admin Login</a>
            <a href="login.php" class="hover:text-blue-600 font-medium text-sm">Login</a>
            <a href="register.php" class="hover:text-blue-600 font-medium text-sm">Register</a>
        <?php endif; ?>
    </div>

</nav>
