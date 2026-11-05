<?php
/** Public site navbar. Requires includes/bootstrap.php to have been loaded by the page. */
$navLinks = [
    'tournaments.php' => 'Tournaments',
    'news.php' => 'News',
    'contactform.php' => 'Contact & Reservations',
    'aboutus.php' => 'About Us',
];
$linkClass = 'text-white hover:text-blue-600 font-medium text-sm';
?>
<nav class="flex flex-wrap justify-between items-center bg-neutral-900 shadow px-6 py-3">

    <div class="flex items-center space-x-6">
        <a href="index.php" class="text-lg font-semibold text-white hover:text-blue-600">
            DWP Esports Cinema
        </a>
        <?php foreach ($navLinks as $file => $label): ?>
            <a href="<?= e($file) ?>" class="<?= $linkClass ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
        <a href="support.php" class="<?= $linkClass ?> relative" id="supportLink">
            Support
            <span id="supportUnreadBadge"
                class="absolute -top-2 -right-3 bg-red-600 text-white text-xs w-5 h-5 flex items-center justify-center rounded-full hidden">
                0
            </span>
        </a>
    </div>

    <div class="flex items-center space-x-4">
        <?php if ($auth->isUser()): ?>
            <a href="user_profile.php" class="flex items-center space-x-2 <?= $linkClass ?>">
                <img src="<?= e(url('public/' . ($_SESSION['user_avatar'] ?? Auth::DEFAULT_AVATAR))) ?>"
                    alt="Avatar"
                    class="w-8 h-8 rounded-full border object-cover">
                <span><?= e($_SESSION['user_name'] ?? '') ?></span>
            </a>
            <?= logout_button('Logout', 'text-white hover:text-red-600 font-medium text-sm') ?>
        <?php else: ?>
            <a href="admin_login.php" class="<?= $linkClass ?>">Admin Login</a>
            <a href="login.php" class="<?= $linkClass ?>">Login</a>
            <a href="register.php" class="<?= $linkClass ?>">Register</a>
        <?php endif; ?>
    </div>
    <?php if ($auth->isUser()): ?>
        <script src="public/js/notification.js" defer></script>
    <?php endif; ?>
</nav>
