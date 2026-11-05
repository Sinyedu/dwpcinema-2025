<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_user();

$config = Config::get();
$success = $error = '';

if (is_post()) {
    require_csrf();
    $result = (new ReservationService(db(), $config['email']))->submit($_POST, $auth->userId());
    $success = $result['success'];
    $error = $result['error'];
}

$tournaments = (new Tournament(db()))->getOptions();

$pageTitle = 'Reservation - DWP Esports Cinema';
include __DIR__ . '/includes/head.php';
?>

<body class="bg-neutral-900 text-white">
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <section class="max-w-3xl mx-auto p-6 mt-16 bg-neutral-800 rounded shadow">
        <h2 class="text-3xl text-white font-semibold mb-6 text-center">Reservation Form</h2>

        <?php if ($success): ?>
            <div class="bg-green-100 text-green-800 p-4 mb-4 rounded"><?= e($success) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-100 text-red-800 p-4 mb-4 rounded"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" class="space-y-4">
            <?= $csrf->field() ?>
            <div>
                <label for="subject" class="block font-medium mb-1">Subject *</label>
                <input id="subject" type="text" name="subject" placeholder="e.g., Box Reservation" required maxlength="200"
                    value="<?= e($error ? ($_POST['subject'] ?? '') : '') ?>"
                    class="w-full p-2 border rounded text-black" />
            </div>
            <div>
                <label for="tournament" class="block font-medium text-white mb-1">Tournament *</label>
                <select id="tournament" name="tournament" class="w-full p-2 text-black border rounded" required>
                    <option value="">-- Select a tournament --</option>
                    <?php foreach ($tournaments as $t): ?>
                        <option value="<?= (int)$t['tournamentID'] ?>"><?= e($t['tournamentName']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="message" class="block font-medium text-white mb-1">Message *</label>
                <textarea id="message" name="message" rows="5" maxlength="5000" class="w-full p-2 border rounded text-black" placeholder="Write your message" required><?= e($error ? ($_POST['message'] ?? '') : '') ?></textarea>
            </div>

            <div class="text-center">
                <button class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-500 transition">
                    Send Reservation
                </button>
            </div>
        </form>
    </section>
</body>

</html>
