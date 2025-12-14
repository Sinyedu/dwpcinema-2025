<?php
session_start();
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/controllers/UserController.php';

$controller = new UserController();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $user = $controller->login($_POST);

        if ($user) {
            if (!empty($user['isAdmin']) && (int)$user['isAdmin'] === 1) {
                $error = "Admins must log in through the admin portal.";
            } elseif (isset($user['isActive']) && (int)$user['isActive'] === 0) {
                $error = "Your account has been deactivated. Please contact the site administrator.";
            } else {
                $_SESSION['user_id'] = $user['userID'];
                $_SESSION['user_name'] = $user['firstName'] . ' ' . $user['lastName'];
                $_SESSION['user_avatar'] = $user['avatar'] ?? 'uploads/avatars/default.png';

                $redirect = $_GET['redirect'] ?? 'index.php';
                header("Location: $redirect");
                exit;
            }
        } else {
            $error = "Invalid email or password.";
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
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

<body class="bg-neutral-900 flex items-center justify-center min-h-screen">

    <div class="bg-neutral-800 rounded-xl shadow-lg p-8 w-full max-w-md">
        <h1 class="text-2xl font-semibold text-white mb-6 text-center">Login</h1>

        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-4 text-center">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-white mb-1" for="email">Email</label>
                <input id="email" type="email" name="email" placeholder="you@example.com" required
                    class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-white mb-1" for="password">Password</label>
                <input id="password" type="password" name="password" placeholder="********" required
                    class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-500 transition">
                Login
            </button>
        </form>

        <p class="mt-4 text-center text-gray-400">
            Don't have an account?
            <a href="register.php" class="text-blue-600 hover:underline">Register</a>
        </p>
    </div>

</body>

</html>