<?php
session_start();
require_once __DIR__ . '/classes/Database.php';
require_once "controllers/UserController.php";

$pdo = Database::getInstance();
$controller = new UserController($pdo);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $controller->login($_POST);
    if ($user) {
        if (!empty($user['isAdmin']) && $user['isAdmin'] == 1) {
            $error = "Invalid email or password";
        } else {
            $_SESSION['user_id'] = $user['userID'];
            $_SESSION['user_name'] = $user['firstName'];
            $_SESSION['user_avatar'] = $user['avatar'] ?? null;

            header("Location: index.php");
            exit;
        }
    } else {
        $error = "Invalid email or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login - DWP Esports Cinema</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

<div class="bg-white rounded-xl shadow-lg p-8 w-full max-w-md">
    <h1 class="text-2xl font-semibold text-gray-900 mb-6 text-center">Login</h1>

    <?php if($error): ?>
        <p class="bg-red-100 text-red-800 px-4 py-2 rounded mb-4 text-center"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
        <div>
            <label class="block text-gray-700 mb-1" for="email">Email</label>
            <input id="email" type="email" name="email" placeholder="you@example.com" required class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none">
        </div>
        <div>
            <label class="block text-gray-700 mb-1" for="password">Password</label>
            <input id="password" type="password" name="password" placeholder="********" required class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none">
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-500 transition">Login</button>
    </form>

    <p class="mt-4 text-center text-gray-600">
        Don't have an account? 
        <a href="register.php" class="text-blue-600 hover:underline">Register</a>
    </p>
</div>

</body>
</html>
