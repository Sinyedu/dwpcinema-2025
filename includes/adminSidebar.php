<?php
/** Admin navigation. Included by every page in views/. */
$current_page = basename($_SERVER['PHP_SELF']);
$adminLinks = [
    'admin_dashboard.php' => 'Dashboard',
    'news.php' => 'Manage News',
    'tournaments.php' => 'Manage Tournaments',
    'games.php' => 'Manage Games',
    'users.php' => 'Manage Users',
    'openinghours.php' => 'Manage Opening Hours',
    'locations.php' => 'Manage Locations',
    'support.php' => 'Manage Support Tickets',
    'aboutus.php' => 'Manage About Us',
    'bookings.php' => 'Bookings Overview',
];
?>

<?php if ($flash = flash_pull()): ?>
    <div class="fixed top-5 right-5 z-50 px-4 py-2 rounded shadow text-white <?= $flash['type'] === 'success' ? 'bg-green-600' : 'bg-red-600' ?>">
        <?= e($flash['message']) ?>
    </div>
<?php endif; ?>

<aside class="bg-neutral-800 text-white w-64 min-h-screen fixed top-0 left-0 flex flex-col">
    <div class="p-6 text-2xl font-bold border-b border-gray-700">
        <a href="admin_dashboard.php" class="hover:text-blue-400">Admin Panel</a>
    </div>

    <nav class="flex-1 p-4 space-y-2">
        <?php foreach ($adminLinks as $file => $label): ?>
            <a href="<?= e($file) ?>" class="block px-4 py-2 rounded hover:bg-neutral-700 <?= $current_page === $file ? 'bg-neutral-500' : '' ?>"><?= e($label) ?></a>
        <?php endforeach; ?>

        <div class="p-4 border-t border-gray-700">
            <?= logout_button('Logout', 'block w-full bg-red-600 hover:bg-red-500 text-center py-2 rounded') ?>
        </div>
    </nav>
</aside>
