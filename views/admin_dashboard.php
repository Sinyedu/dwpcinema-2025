<?php
session_start();
require_once __DIR__ . '/../controllers/AdminSupportController.php';
require_once "../controllers/AdminController.php";
require_once __DIR__ . '/../classes/Database.php';
require_once "../controllers/TournamentController.php";
require_once "../controllers/NewsController.php";
require_once "../controllers/BookingController.php";
require_once "../models/Booking.php";
require_once "../controllers/GameController.php";
require_once __DIR__ . '/../controllers/LocationController.php';
require_once __DIR__ . '/../controllers/OpeningHoursController.php';
include __DIR__ . '/../includes/adminSidebar.php';

$pdo = Database::getInstance();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}
$locationController = new LocationController($pdo);
$bookingModel = new Booking($pdo);
$bookingController = new BookingController($bookingModel);
$adminController = new AdminController($pdo);
$reservationsStmt = $pdo->query("SELECT * FROM ContactForm WHERE category = 'Reservation' ORDER BY createdAt DESC");
$reservations = $reservationsStmt->fetchAll(PDO::FETCH_ASSOC);
$tournamentController = new TournamentController($pdo);
$newsController = new NewsController($pdo);
$gameController = new GameController($pdo);
$adminSupport = new AdminSupportController($pdo);
$openingHoursController = new OpeningHoursController($pdo);

$reservationsStmt = $pdo->query("
    SELECT *
    FROM ContactForm
    WHERE category = 'Reservation'
    ORDER BY createdAt DESC
");
$reservations = $reservationsStmt->fetchAll(PDO::FETCH_ASSOC);
$tickets = $adminSupport->getAllTickets();
$bookings = $bookingController->getLatestBookings(5);
$users = $adminController->getAllUsers();
$totalBookings = $bookingController->getTotalBookingCount();
$tournaments = $tournamentController->getAllTournaments();
$news = $newsController->getAllNews();
$games = $gameController->getAllGames();
$locations = $locationController->getAllLocations();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['days'] as $day => $values) {
        $openingController->updateDay([
            'dayOfWeek' => $day,
            'openTime' => $values['open'],
            'closeTime' => $values['close'],
            'isClosed' => $values['closed'] ?? 0
        ]);
    }
    $success = "Opening hours updated successfully!";
}

$openingHours = $openingHoursController->getAll();

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
            <div class="bg-neutral-800 p-6 rounded-lg shadow text-center">
                <h2 class="text-gray-400 text-sm uppercase mb-2">Games</h2>
                <p class="text-3xl font-bold text-white"><?= count($games) ?></p>
            </div>
            <div class="bg-neutral-800 p-6 rounded-lg shadow text-center">
                <h2 class="text-gray-400 text-sm uppercase mb-2">Support Tickets</h2>
                <p class="text-3xl font-bold text-white"><?= count($tickets) ?></p>
            </div>
            <div class="bg-neutral-800 p-6 rounded-lg shadow text-center">
                <h2 class="text-gray-400 text-sm uppercase mb-2">Total Bookings</h2>
                <p class="text-3xl font-bold text-white"><?= $totalBookings ?></p>
            </div>
            <div class="bg-neutral-800 p-6 rounded-lg shadow mb-6">
                <h2 class="text-white text-lg font-semibold mb-4">Opening Hours</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <?php foreach ($openingHours as $h): ?>
                        <div class="bg-neutral-700 rounded p-3 text-center">
                            <span class="block font-medium text-white"><?= htmlspecialchars($h['dayOfWeek']) ?></span>
                            <?php if ($h['isClosed']): ?>
                                <span class="text-red-400 font-semibold">Closed</span>
                            <?php else: ?>
                                <span class="text-gray-300"><?= htmlspecialchars($h['openTime']) ?> - <?= htmlspecialchars($h['closeTime']) ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-4 text-center">
                    <a href="openinghours.php" class="text-blue-500 hover:underline">Manage Opening Hours</a>
                </div>
            </div>
            <div class="bg-neutral-800 p-6 rounded-lg shadow mb-6">
                <h2 class="text-white text-lg font-semibold mb-4">Locations</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($locations as $loc): ?>
                        <div class="bg-neutral-700 rounded p-3 text-center">
                            <span class="block font-medium text-white"><?= htmlspecialchars($loc['locationName']) ?></span>
                            <span class="text-gray-300 text-sm"><?= htmlspecialchars($loc['city']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-4 text-center">
                    <a href="locations.php" class="text-blue-500 hover:underline">Manage Locations</a>
                </div>
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
                            <span class="font-medium text-white"><?= htmlspecialchars($t['tournamentName']) ?></span>
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
                        <li class="text-white"><?= htmlspecialchars($n['newsTitle']) ?></li>
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
                        <li class="text-white">
                            <?= htmlspecialchars($u['firstName'] . ' ' . $u['lastName']) ?>
                            <span class="text-gray-400">(<?= htmlspecialchars($u['userEmail']) ?>)</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="bg-neutral-800 rounded shadow p-6">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-lg text-white font-semibold">Games</h3>
                    <a href="games.php" class="text-blue-600 hover:underline text-sm">Manage</a>
                </div>
                <ul class="space-y-2 text-sm text-gray-700 max-h-72 overflow-y-auto">
                    <?php foreach ($games as $g): ?>
                        <li>
                            <span class="font-medium text-white"><?= htmlspecialchars($g['gameName']) ?></span>
                            <?php if (!empty($g['genre'])): ?>
                                <span class="text-gray-500">(<?= htmlspecialchars($g['genre']) ?>)</span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>


            <div class="bg-neutral-800 rounded-lg shadow overflow-x-auto">
                <h2 class="text-xl text-white font-semibold mt-4 mb-6 text-center">Recent Bookings</h2>
                <div class="flex justify-end px-6 mb-4">
                    <a href="bookings.php" class="text-blue-500 hover:underline text-sm">
                        View all bookings
                    </a>
                </div>
                <table class="min-w-full text-sm text-left text-gray-300">
                    <thead class="bg-neutral-700 text-gray-200 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3">User</th>
                            <th class="px-4 py-3">Showing</th>
                            <th class="px-4 py-3">Seats</th>
                            <th class="px-4 py-3">Total</th>
                            <th class="px-4 py-3">Booked At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($bookings)): ?>
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-gray-400">
                                    No bookings found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($bookings as $b): ?>
                                <tr class="border-b border-neutral-700 hover:bg-neutral-700">
                                    <td class="px-4 py-3"><?= $b['bookingID'] ?></td>
                                    <td class="px-4 py-3">User #<?= $b['userID'] ?></td>
                                    <td class="px-4 py-3">
                                        <?= htmlspecialchars($b['showingDate']) ?>
                                        <?= htmlspecialchars($b['showingTime']) ?>
                                        <br>
                                        <span class="text-gray-400">
                                            <?= htmlspecialchars($b['hallName']) ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?= htmlspecialchars($b['seats']) ?>
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-white">
                                        <?= number_format($b['totalAmount'], 2) ?> $
                                    </td>
                                    <td class="px-4 py-3">
                                        <?= htmlspecialchars($b['bookingDate']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

            </div>



        </div>
    </div>

</body>

</html>