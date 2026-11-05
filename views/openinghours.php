<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();
$hoursModel = new OpeningHours(db());
$success = $error = '';

if (is_post()) {
    require_csrf();
    $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    $isTime = static fn($v) => is_string($v) && preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', $v) === 1;

    foreach ((array)($_POST['days'] ?? []) as $day => $values) {
        if (!in_array($day, $weekdays, true) || !is_array($values)) {
            continue;
        }
        $closed = isset($values['closed']);
        if (!$closed && (!$isTime($values['open'] ?? null) || !$isTime($values['close'] ?? null))) {
            $error = "Please enter valid opening and closing times for $day.";
            break;
        }
        $hoursModel->updateDay($day, (string)($values['open'] ?? '00:00'), (string)($values['close'] ?? '00:00'), $closed);
    }
    if (!$error) {
        $success = 'Opening hours updated successfully!';
    }
}

$openingHours = $hoursModel->getAll();
?>
<?php
$pageTitle = 'Admin Dashboard - Opening Hours';
include __DIR__ . '/../includes/head.php';
?>

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
<?= $csrf->field() ?>
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