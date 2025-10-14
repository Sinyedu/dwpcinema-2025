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
                <?php if (!empty($_SESSION['user_avatar'])): ?>
                    <img src="<?= htmlspecialchars($_SESSION['user_avatar']) ?>" 
                         alt="Avatar" class="w-8 h-8 rounded-full border object-cover">
                <?php else: ?>
                    <div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center text-gray-600">
                        <span class="text-xs font-bold">
                            <?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?>
                        </span>
                    </div>
                <?php endif; ?>
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
