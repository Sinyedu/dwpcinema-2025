<?php
session_start();
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../controllers/OpeningHoursController.php';
include __DIR__ . '/../includes/adminSidebar.php';

$pdo = Database::getInstance();
$openingController = new OpeningHoursController($pdo);

$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        foreach ($_POST['days'] as $day => $values) {
            $openingController->updateDay([
                'dayOfWeek' => $day,
                'openTime' => $values['open'],
                'closeTime' => $values['close'],
                'isClosed' => isset($values['closed']) ? 1 : 0
            ]);
        }
        $success = "Opening hours updated successfully!";
    } catch (Exception $e) {
        $error = "Error updating opening hours: " . $e->getMessage();
    }
}

$openingHours = $openingController->getAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Opening Hours</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-neutral-900 min-h-screen flex">

    <?php include __DIR__ . '/../includes/adminSidebar.php'; ?>

    <div class="flex-1 ml-64 p-8">
        <header class="flex justify-between items-center mb-8 border-b border-neutral-700 pb-4">
            <h1 class="text-2xl text-white font-semibold">Manage Opening Hours</h1>
            <a href="admin_dashboard.php" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-500 text-sm">Back to Dashboard</a>
        </header>

        <?php if ($success): ?>
            <div class="bg-green-600 text-white p-3 rounded mb-4"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-600 text-white p-3 rounded mb-4"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php foreach ($openingHours as $h): ?>
                <div class="bg-neutral-800 rounded p-4 shadow hover:shadow-lg transition">
                    <h4 class="text-white font-semibold mb-3"><?= htmlspecialchars($h['dayOfWeek']) ?></h4>
                    <div class="flex items-center gap-2 mb-2">
                        <label class="text-gray-300 w-16">Open:</label>
                        <input type="time" name="days[<?= $h['dayOfWeek'] ?>][open]" value="<?= htmlspecialchars($h['openTime']) ?>"
                            class="flex-1 px-2 py-1 rounded bg-neutral-700 text-white border border-gray-600">
                    </div>
                    <div class="flex items-center gap-2 mb-2">
                        <label class="text-gray-300 w-16">Close:</label>
                        <input type="time" name="days[<?= $h['dayOfWeek'] ?>][close]" value="<?= htmlspecialchars($h['closeTime']) ?>"
                            class="flex-1 px-2 py-1 rounded bg-neutral-700 text-white border border-gray-600">
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-gray-300">Closed:</label>
                        <input type="checkbox" name="days[<?= $h['dayOfWeek'] ?>][closed]" value="1" <?= $h['isClosed'] ? 'checked' : '' ?>>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="md:col-span-2 text-center mt-4">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-500 transition">
                    Update Opening Hours
                </button>
            </div>
        </form>
    </div>

</body>

</html>