<?php
session_start();
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../controllers/OpeningHoursController.php';

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




<form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <?php foreach ($openingHours as $h): ?>
        <div class="bg-neutral-800 rounded p-4 shadow">
            <?php include __DIR__ . '/../includes/adminSidebar.php'; ?>
            <h4 class="text-white font-semibold mb-2"><?= htmlspecialchars($h['dayOfWeek']) ?></h4>
            <div class="flex items-center gap-2 mb-2">
                <label class="text-gray-300">Open:</label>
                <input type="time" name="days[<?= $h['dayOfWeek'] ?>][open]" value="<?= htmlspecialchars($h['openTime']) ?>"
                    class="px-2 py-1 rounded bg-neutral-700 text-white border border-gray-600">
            </div>
            <div class="flex items-center gap-2 mb-2">
                <label class="text-gray-300">Close:</label>
                <input type="time" name="days[<?= $h['dayOfWeek'] ?>][close]" value="<?= htmlspecialchars($h['closeTime']) ?>"
                    class="px-2 py-1 rounded bg-neutral-700 text-white border border-gray-600">
            </div>
            <div class="flex items-center gap-2">
                <label class="text-gray-300">Closed:</label>
                <input type="checkbox" name="days[<?= $h['dayOfWeek'] ?>][closed]" value="1" <?= $h['isClosed'] ? 'checked' : '' ?>>
            </div>
        </div>
    <?php endforeach; ?>
    <div class="md:col-span-2 text-center mt-4">
        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-500">Update Opening Hours</button>
    </div>
</form>