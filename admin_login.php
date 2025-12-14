<?php
session_start();
require_once "controllers/AdminController.php";
require_once __DIR__ . '/classes/Database.php';

$pdo = Database::getInstance();
$controller = new AdminController($pdo);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin = $controller->login($_POST['email'], $_POST['password']);
    if ($admin) {
        $_SESSION['admin_id'] = $admin['userID'];
        $_SESSION['admin_name'] = $admin['firstName'];
        header("Location: views/admin_dashboard.php");
        exit;
    } else {
        $error = "Invalid admin credentials!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Login - DWP Esports Cinema</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-neutral-900 flex items-center justify-center min-h-screen">

    <div class="bg-neutral-800 rounded-xl shadow-lg p-8 w-full max-w-md">
        <h1 class="text-2xl font-semibold text-white mb-6 text-center">Admin Login</h1>

        <?php if ($error): ?>
            <p class="bg-red-100 text-red-800 px-4 py-2 rounded mb-4 text-center"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-white mb-1" for="email">Email</label>
                <input id="email" type="email" name="email" placeholder="admin@example.com" required class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-white mb-1" for="password">Password</label>
                <input id="password" type="password" name="password" placeholder="********" required class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-300 transition">Login</button>
        </form>

        <p class="mt-4 text-center text-gray-400">
            Back to <a href="index.php" class="text-blue-600 hover:underline">Homepage</a>
        </p>
    </div>

</body>

</html>