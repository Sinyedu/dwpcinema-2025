<?php
session_start();
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../controllers/BookingController.php';
include __DIR__ . '/../includes/adminSidebar.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$pdo = Database::getInstance();

$bookingModel = new Booking($pdo);
$bookingController = new BookingController($bookingModel);

$bookings = $bookingController->getAllBookings();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Bookings</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-neutral-900 min-h-screen flex">

    <?php include "../includes/adminSidebar.php"; ?>

    <div class="flex-1 ml-64 p-8">

        <header class="flex justify-between items-center mb-8 border-b border-neutral-700 pb-4">
            <h1 class="text-2xl text-white font-semibold">Bookings</h1>
            <a href="admin_dashboard.php"
                class="text-sm text-blue-500 hover:underline">
                Back to dashboard
            </a>
        </header>

        <div class="bg-neutral-800 rounded-lg shadow overflow-x-auto">
            <table class="min-w-full text-sm text-left text-gray-300">
                <thead class="bg-neutral-700 text-gray-200 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">Booking ID</th>
                        <th class="px-4 py-3">User ID</th>
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
                                <td class="px-4 py-3 font-medium text-white">
                                    <?= $b['bookingID'] ?>
                                </td>

                                <td class="px-4 py-3">
                                    <?= $b['userID'] ?>
                                </td>

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
                                    <?= number_format($b['totalAmount'], 2) ?> kr.
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

</body>

</html>