<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_user();

$users = new User(db());
$userID = $auth->userId();
$errors = [];

if (is_post()) {
    require_csrf();

    $input = UserInput::profile($_POST);
    $errors = $input['errors'];
    $current = $users->getUserById($userID);

    if (!$errors) {
        $avatar = $current['avatar'] ?: Auth::DEFAULT_AVATAR;
        try {
            if (!empty($_FILES['avatar']['name'])) {
                $avatar = (new ImageUploader())->upload($_FILES['avatar']);
            }
            $d = $input['data'];
            if ($users->updateUser($userID, $d['firstName'], $d['lastName'], $d['email'], $avatar)) {
                $auth->updateProfile($d['firstName'] . ' ' . $d['lastName'], $avatar);
                redirect('user_profile.php?success=1');
            }
            $errors['email'] = 'That email address is already in use.';
        } catch (RuntimeException $e) {
            $errors['avatar'] = $e->getMessage();
        }
    }
}

$user = $users->getUserById($userID);
$bookings = (new Booking(db()))->listDetailsForUser($userID);
$tierColors = ['VIP' => 'bg-blue-400', 'Standard' => 'bg-green-400'];

$pageTitle = 'My Profile - DWP Esports Cinema';
include __DIR__ . '/includes/head.php';

$fieldClass = 'w-full border rounded px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-blue-500 transition';
// Show what the user just typed when validation failed, otherwise the stored values.
$value = fn(string $postKey, string $dbKey) => $errors ? ($_POST[$postKey] ?? '') : ($user[$dbKey] ?? '');
?>

<body class="bg-neutral-900 text-white min-h-screen">
    <?php include __DIR__ . '/includes/navbar.php'; ?>
    <div class="flex items-center justify-center py-10">
        <div class="w-full max-w-2xl px-6">
            <div class="bg-neutral-800 rounded-2xl shadow-lg p-8 md:p-10">
                <h1 class="text-3xl font-bold text-center mb-8 text-blue-600">My Profile</h1>
                <?php if (isset($_GET['success']) && !$errors): ?>
                    <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded mb-6 text-center font-medium">
                        Profile updated successfully!
                    </div>
                <?php elseif ($errors): ?>
                    <ul class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded mb-6 list-disc list-inside font-medium">
                        <?php foreach ($errors as $message): ?>
                            <li><?= e($message) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <?= $csrf->field() ?>
                    <div class="flex flex-col items-center">
                        <img src="<?= e(url('public/' . ($user['avatar'] ?: Auth::DEFAULT_AVATAR))) ?>" alt="Avatar" class="w-32 h-32 rounded-full object-cover border-4 border-blue-200 shadow-md">
                        <label for="avatar" class="mt-3 text-sm text-white font-medium">Change Avatar</label>
                        <input id="avatar" type="file" name="avatar" accept="image/jpeg,image/png,image/webp" class="mt-1 w-full md:w-64 text-sm text-white border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label for="firstName" class="block text-sm font-medium text-white mb-1">First Name</label>
                            <input id="firstName" type="text" name="firstName" required maxlength="100" value="<?= e($value('firstName', 'firstName')) ?>" class="<?= $fieldClass ?>">
                        </div>
                        <div>
                            <label for="lastName" class="block text-sm font-medium text-white mb-1">Last Name</label>
                            <input id="lastName" type="text" name="lastName" required maxlength="100" value="<?= e($value('lastName', 'lastName')) ?>" class="<?= $fieldClass ?>">
                        </div>
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-white mb-1">Email</label>
                        <input id="email" type="email" name="email" required maxlength="100" value="<?= e($value('email', 'userEmail')) ?>" class="<?= $fieldClass ?>">
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-semibold px-6 py-2 rounded-lg shadow-md transition">Save Changes</button>
                    </div>
                </form>

                <div class="mt-10">
                    <h2 class="text-2xl font-bold text-white mb-4">Booking History</h2>
                    <?php if (empty($bookings)): ?>
                        <p class="text-gray-400">You have no bookings yet.</p>
                    <?php else: ?>
                        <div class="space-y-6">
                            <?php foreach ($bookings as $b): ?>
                                <div class="bg-neutral-800 shadow rounded p-4">
                                    <div class="flex justify-between items-center mb-2">
                                        <h3 class="font-semibold text-white text-lg"><?= e($b['tournamentName']) ?> - <?= e($b['gameName']) ?></h3>
                                        <span class="text-white text-sm"><?= e(date('F j, Y H:i', strtotime($b['bookingDate']))) ?></span>
                                    </div>
                                    <p class="text-white mb-1">Hall: <?= e($b['hallName']) ?></p>
                                    <p class="text-white mb-1">Showing: <?= e(date('F j, Y', strtotime($b['showingDate']))) ?> | <?= e(date('H:i', strtotime($b['showingTime']))) ?></p>
                                    <div class="flex flex-wrap gap-2 mt-2 mb-2">
                                        <?php foreach ($b['seats'] as $s): ?>
                                            <span class="px-2 py-1 <?= e($tierColors[$s['tierName']] ?? 'bg-gray-300') ?> rounded text-sm"><?= e($s['seatLabel']) ?> (<?= e($s['tierName']) ?>)</span>
                                        <?php endforeach; ?>
                                    </div>
                                    <p class="font-semibold text-white">Total Paid: $<?= number_format((float)$b['totalAmount'], 2) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="text-center mt-6">
                    <a href="index.php" class="text-white hover:text-blue-600 text-sm">&larr; Back to Home</a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
