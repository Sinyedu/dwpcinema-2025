<?php
session_start();
require_once "classes/Database.php";
require_once "controllers/UserController.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$pdo = Database::getInstance();
$userController = new UserController($pdo);
$userID = $_SESSION['user_id'];

try {
    $user = $userController->getProfile($userID);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $userController->updateProfile($userID, $_POST, $_FILES);
        header("Location: user_profile.php?success=1");
        exit;
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}

$bookingStmt = $pdo->prepare("
    SELECT b.bookingID, b.bookingDate, b.totalAmount,
           s.showingDate, s.showingTime,
           h.hallName, t.tournamentName, g.gameName
    FROM Booking b
    JOIN Showing s ON b.showingID = s.showingID
    JOIN Hall h ON s.hallID = h.hallID
    JOIN `Match` m ON s.matchID = m.matchID
    JOIN Tournament t ON m.tournamentID = t.tournamentID
    JOIN Game g ON t.gameID = g.gameID
    WHERE b.userID = ?
    ORDER BY b.bookingDate DESC
");
$bookingStmt->execute([$userID]);
$bookings = $bookingStmt->fetchAll(PDO::FETCH_ASSOC);

$seatStmt = $pdo->prepare("
    SELECT CONCAT(seatRow, seatNumber) AS seatLabel, st.tierName
    FROM Booking_Seat bs
    JOIN Seat s ON bs.seatID = s.seatID
    JOIN SeatTier st ON s.tierID = st.tierID
    WHERE bs.bookingID = ?
    ORDER BY s.seatRow, s.seatNumber
");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Profile - DWP Esports Cinema</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-neutral-900 text-white min-h-screen">
    <?php include __DIR__ . '/includes/navbar.php'; ?>
    <div class="flex items-center justify-center py-10">
        <div class="w-full max-w-2xl px-6">
            <div class="bg-neutral-800 rounded-2xl shadow-lg p-8 md:p-10">
                <h1 class="text-3xl font-bold text-center mb-8 text-blue-600">My Profile</h1>
                <?php if (isset($_GET['success'])): ?>
                    <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded mb-6 text-center font-medium">
                        Profile updated successfully!
                    </div>
                <?php elseif (!empty($error)): ?>
                    <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded mb-6 text-center font-medium">
                        <?= htmlspecialchars($error ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>
                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <div class="flex flex-col items-center">
                        <div class="relative">
                            <img src="/public/<?= htmlspecialchars($user['avatar'] ?? '', ENT_QUOTES, 'UTF-8') ?>" alt="Avatar" class="w-32 h-32 rounded-full object-cover border-4 border-blue-200 shadow-md">
                        </div>
                        <label class="mt-3 text-sm text-white font-medium">Change Avatar</label>
                        <input type="file" name="avatar" accept="image/*" class="mt-1 w-full md:w-64 text-sm text-gray-700 border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-white mb-1">First Name</label>
                            <input type="text" name="firstName" value="<?= htmlspecialchars($user['firstName'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="w-full border rounded px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-white mb-1">Last Name</label>
                            <input type="text" name="lastName" value="<?= htmlspecialchars($user['lastName'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="w-full border rounded px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-white mb-1">Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['userEmail'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="w-full border rounded px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-300 text-white font-semibold px-6 py-2 rounded-lg shadow-md transition">Save Changes</button>
                    </div>
                </form>
                <div class="mt-10">
                    <h2 class="text-2xl font-bold text-white mb-4">Booking History</h2>
                    <?php if (empty($bookings)): ?>
                        <p class="text-gray-600">You have no bookings yet.</p>
                    <?php else: ?>
                        <div class="space-y-6">
                            <?php foreach ($bookings as $b): ?>
                                <div class="bg-neutral-800 shadow rounded p-4">
                                    <div class="flex justify-between items-center mb-2">
                                        <h3 class="font-semibold text-white text-lg"><?= htmlspecialchars($b['tournamentName'] ?? '', ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars($b['gameName'] ?? '', ENT_QUOTES, 'UTF-8') ?></h3>
                                        <span class="text-white text-sm"><?= date('F j, Y H:i', strtotime($b['bookingDate'])) ?></span>
                                    </div>
                                    <p class="text-white mb-1">Hall: <?= htmlspecialchars($b['hallName'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                                    <p class="text-white mb-1">Showing: <?= date('F j, Y', strtotime($b['showingDate'])) ?> | <?= date('H:i', strtotime($b['showingTime'])) ?></p>
                                    <?php
                                    $seatStmt->execute([$b['bookingID']]);
                                    $seats = $seatStmt->fetchAll(PDO::FETCH_ASSOC);
                                    ?>
                                    <div class="flex flex-wrap gap-2 mt-2 mb-2">
                                        <?php foreach ($seats as $s): ?>
                                            <span class="px-2 py-1 bg-gray-200 rounded text-sm"><?= htmlspecialchars($s['seatLabel'] ?? '', ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($s['tierName'] ?? '', ENT_QUOTES, 'UTF-8') ?>)</span>
                                        <?php endforeach; ?>
                                    </div>
                                    <p class="font-semibold text-white">Total Paid: $<?= number_format($b['totalAmount'], 2) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="text-center mt-6">
                    <a href="index.php" class="text-white hover:text-blue-600 text-sm">← Back to Home</a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>