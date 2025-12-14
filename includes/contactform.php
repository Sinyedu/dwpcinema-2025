<?php
session_start();
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../controllers/ContactController.php';

if (!isset($_SESSION['user_id'])) {
    $redirect = urlencode($_SERVER['REQUEST_URI']);
    header("Location: login.php?redirect=$redirect");
    exit;
}

$config = require __DIR__ . '/../config/config.php';
$controller = new ContactController($config['email']);

$success = $error = '';
$tournaments = $controller->getTournaments();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $controller->submitReservation($_POST, $_SESSION['user_id']);
    $success = $result['success'] ?? '';
    $error = $result['error'] ?? '';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Reservation - DWP Esports Cinema</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 text-gray-900">

    <section class="max-w-3xl mx-auto p-6 mt-16 bg-white rounded shadow">
        <h2 class="text-3xl font-semibold mb-6 text-center">Reservation Form</h2>

        <?php if ($success): ?>
            <div class="bg-green-100 text-green-800 p-4 mb-4 rounded"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-100 text-red-800 p-4 mb-4 rounded"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" class="space-y-4">
            <div>
                <div>
                    <label class="block font-medium mb-1">Subject *</label>
                    <input type="text" name="subject" placeholder="e.g., Box Reservation" required
                        class="w-full p-2 border rounded">
                </div>
                <label class="block font-medium mb-1">Tournament *</label>
                <select name="tournament" class="w-full p-2 border rounded" required>
                    <option value="">-- Select a tournament --</option>
                    <?php foreach ($tournaments as $t): ?>
                        <option value="<?= $t['tournamentID'] ?>"><?= htmlspecialchars($t['tournamentName']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block font-medium mb-1">Message *</label>
                <textarea name="message" rows="5" class="w-full p-2 border rounded" placeholder="Write your message" required></textarea>
            </div>

            <div class="text-center">
                <button class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-500 transition">
                    Send Reservation
                </button>
            </div>
        </form>
    </section>

    <script src="/public/js/contact.js"></script>
</body>

</html>