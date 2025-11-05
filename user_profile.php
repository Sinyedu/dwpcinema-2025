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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - DWP Esports Cinema</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-900 min-h-screen">

    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <div class="flex items-center justify-center py-10">
        <div class="w-full max-w-2xl px-6">

            <div class="bg-white rounded-2xl shadow-lg p-8 md:p-10">

                <h1 class="text-3xl font-bold text-center mb-8 text-blue-600">My Profile</h1>

                <?php if (isset($_GET['success'])): ?>
                    <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded mb-6 text-center font-medium">
                        Profile updated successfully!
                    </div>
                <?php elseif (!empty($error)): ?>
                    <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded mb-6 text-center font-medium">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="space-y-6">

                    <div class="flex flex-col items-center">
                        <div class="relative">
                            <img src="/dwpcinema-2025/public/<?= htmlspecialchars($user['avatar']) ?>" 
                                 alt="Avatar" 
                                 class="w-32 h-32 rounded-full object-cover border-4 border-blue-200 shadow-md">
                        </div>
                        <label class="mt-3 text-sm text-gray-600 font-medium">Change Avatar</label>
                        <input type="file" name="avatar" accept="image/*" 
                               class="mt-1 w-full md:w-64 text-sm text-gray-700 border rounded px-3 py-2 
                                      focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                            <input type="text" name="firstName" value="<?= htmlspecialchars($user['firstName']) ?>" 
                                   class="w-full border rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                            <input type="text" name="lastName" value="<?= htmlspecialchars($user['lastName']) ?>" 
                                   class="w-full border rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['userEmail']) ?>" 
                               class="w-full border rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" 
                                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg shadow-md transition">
                            Save Changes
                        </button>
                    </div>

                </form>

                <div class="text-center mt-6">
                    <a href="index.php" class="text-gray-500 hover:text-blue-600 text-sm">← Back to Home</a>
                </div>

            </div>
        </div>
    </div>

</body>
</html>
