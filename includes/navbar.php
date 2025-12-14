<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<nav class="flex flex-wrap justify-between items-center bg-neutral-900 shadow px-6 py-3">

    <div class="flex items-center space-x-6">
        <a href="index.php" class="text-lg font-semibold text-white hover:text-blue-600">
            DWP Esports Cinema
        </a>
        <a href="tournaments.php" class="text-white hover:text-blue-600 font-medium text-sm">Tournaments</a>
        <a href="news.php" class="text-white hover:text-blue-600 font-medium text-sm">News</a>
        <a href="contactform.php" class="text-white hover:text-blue-600 font-medium text-sm">Contact & Reservations</a>
        <a href="support.php" class="text-white hover:text-blue-600 font-medium text-sm relative" id="supportLink">
            Support
            <span id="supportUnreadBadge"
                class="absolute -top-2 -right-3 bg-red-600 text-white text-xs w-5 h-5 flex items-center justify-center rounded-full hidden">
                0
            </span>
        </a>
        <a href="aboutus.php" class="text-white hover:text-blue-600 font-medium text-sm">About Us</a>
    </div>

    <div class="flex items-center space-x-4">
        <?php if (isset($_SESSION['user_id'])): ?>

            <a href="user_profile.php" class="flex items-center space-x-2 text-white hover:text-blue-600 font-medium text-sm">
                <img src="/public/<?= htmlspecialchars($_SESSION['user_avatar'] ?? 'uploads/avatars/default.png') ?>"
                    alt="Avatar"
                    class="w-8 h-8 rounded-full border object-cover">
                <span><?= htmlspecialchars($_SESSION['user_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>

            </a>

            <a href="logout.php" class="text-white hover:text-red-600 font-medium text-sm">Logout</a>

        <?php else: ?>
            <a href="admin_login.php" class="text-white hover:text-blue-600 font-medium text-sm">Admin Login</a>
            <a href="login.php" class="text-white hover:text-blue-600 font-medium text-sm">Login</a>
            <a href="register.php" class="text-white hover:text-blue-600 font-medium text-sm">Register</a>
        <?php endif; ?>
    </div>
    <script src="public/js/notification.js" defer></script>
</nav>