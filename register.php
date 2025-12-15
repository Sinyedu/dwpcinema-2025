<?php
session_start();
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/classes/Database.php';
require_once "controllers/UserController.php";
$pdo = Database::getInstance();
$controller = new UserController($pdo);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!csrf_validate($_POST['csrf_token'] ?? null)) {
        $error = "Invalid CSRF token.";
    } else {
        if ($controller->register($_POST)) {
            header("Location: login.php");
            exit;
        } else {
            $error = "Registration failed. Maybe the email is already used.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Register - DWP Esports Cinema</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-neutral-900 flex items-center justify-center min-h-screen">

    <div class="bg-neutral-800 rounded-xl shadow-lg p-8 w-full max-w-md">
        <h1 class="text-2xl font-semibold text-white mb-6 text-center">Create an Account</h1>

        <?php if ($success): ?>
            <p class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4 text-center"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p class="bg-red-100 text-red-800 px-4 py-2 rounded mb-4 text-center"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <div>
                <label class="block text-white mb-1" for="firstName">First Name</label>
                <input id="firstName" type="text" name="firstName" placeholder="John" required class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-white mb-1" for="lastName">Last Name</label>
                <input id="lastName" type="text" name="lastName" placeholder="Doe" required class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-white mb-1" for="email">Email</label>
                <input id="email" type="email" name="email" placeholder="you@example.com" required class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-white mb-1" for="password">Password</label>
                <input id="password" type="password" name="password" placeholder="********" required class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-300 transition">Register</button>
        </form>

        <p class="mt-4 text-center text-gray-400">
            Already have an account?
            <a href="login.php" class="text-blue-600 hover:underline">Login</a>
        </p>
    </div>

</body>

</html>