<?php

$current_page = basename($_SERVER['PHP_SELF']);
?>

<aside class="bg-neutral-800 text-white w-64 min-h-screen fixed top-0 left-0 flex flex-col">
    <div class="p-6 text-2xl font-bold border-b border-gray-700">
        <a href="admin_dashboard.php" class="hover:text-blue-400">Admin Panel</a>
    </div>

    <nav class="flex-1 p-4 space-y-2">
        <a href="admin_dashboard.php" class="block px-4 py-2 rounded hover:bg-neutral-700 <?= $current_page === 'dashboard.php' ? 'bg-neutral-500' : '' ?>">Dashboard</a>
        <a href="news.php" class="block px-4 py-2 rounded hover:bg-neutral-700 <?= $current_page === 'news.php' ? 'bg-neutral-500' : '' ?>">Manage News</a>
        <a href="tournaments.php" class="block px-4 py-2 rounded hover:bg-neutral-700 <?= $current_page === 'tournaments.php' ? 'bg-neutral-500' : '' ?>">Manage Tournaments</a>
        <a href="games.php" class="block px-4 py-2 rounded hover:bg-neutral-700 <?= $current_page === 'games.php' ? 'bg-neutral-500' : '' ?>">Manage Games</a>
        <a href="users.php" class="block px-4 py-2 rounded hover:bg-neutral-700 <?= $current_page === 'users.php' ? 'bg-neutral-500' : '' ?>">Manage Users</a>
        <a href="openinghours.php" class="block px-4 py-2 rounded hover:bg-neutral-700 <?= $current_page === 'openinghours.php' ? 'bg-neutral-500' : '' ?>">Manage Opening Hours</a>
        <a href="locations.php" class="block px-4 py-2 rounded hover:bg-neutral-700 <?= $current_page === 'locations.php' ? 'bg-neutral-500' : '' ?>">Manage Locations</a>
        <a href="support.php" class="block px-4 py-2 rounded hover:bg-neutral-700 <?= $current_page === 'support.php' ? 'bg-neutral-500' : '' ?>">Manage Support Tickets</a>
        <a href="aboutus.php" class="block px-4 py-2 rounded hover:bg-neutral-700 <?= $current_page === 'aboutus.php' ? 'bg-neutral-500' : '' ?>">Manage About Us</a>
        <a href="bookings.php" class="block px-4 py-2 rounded hover:bg-neutral-700 <?= $current_page === 'bookings.php' ? 'bg-neutral-500' : '' ?>">Bookings Overview</a>

        <div class="p-4 border-t border-gray-700">
            <a href="logout.php" class="block bg-red-600 hover:bg-red-500 text-center py-2 rounded">Logout</a>
        </div>
</aside>