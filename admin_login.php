<?php
require_once __DIR__ . '/includes/bootstrap.php';

$error = '';

if (is_post()) {
    require_csrf();

    $loginService = new LoginService(new User(db()), $auth, $_SESSION);
    $error = $loginService->attempt(
        (string)($_POST['email'] ?? ''),
        (string)($_POST['password'] ?? ''),
        asAdmin: true
    );

    if ($error === null) {
        redirect('views/admin_dashboard.php');
    }
}

$pageTitle = 'Admin Login - DWP Esports Cinema';
include __DIR__ . '/includes/head.php';
?>

<body class="bg-neutral-900 flex items-center justify-center min-h-screen">

    <div class="bg-neutral-800 rounded-xl shadow-lg p-8 w-full max-w-md">
        <h1 class="text-2xl font-semibold text-white mb-6 text-center">Admin Login</h1>

        <?php if ($error): ?>
            <p class="bg-red-100 text-red-800 px-4 py-2 rounded mb-4 text-center"><?= e($error) ?></p>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <?= $csrf->field() ?>
            <div>
                <label class="block text-white mb-1" for="email">Email</label>
                <input id="email" type="email" name="email" placeholder="admin@example.com" required autocomplete="email" class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-white mb-1" for="password">Password</label>
                <input id="password" type="password" name="password" placeholder="********" required autocomplete="current-password" class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-500 transition">Login</button>
        </form>

        <p class="mt-4 text-center text-gray-400">
            Back to <a href="index.php" class="text-blue-400 hover:underline">Homepage</a>
        </p>
    </div>

</body>

</html>
