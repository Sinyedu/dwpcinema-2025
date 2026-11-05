<?php
require_once __DIR__ . '/includes/bootstrap.php';

$redirectTo = SafeRedirect::local($_GET['redirect'] ?? null, 'index.php');
$error = '';

if (is_post()) {
    require_csrf();

    $loginService = new LoginService(new User(db()), $auth, $_SESSION);
    $error = $loginService->attempt(
        (string)($_POST['email'] ?? ''),
        (string)($_POST['password'] ?? '')
    );

    if ($error === null) {
        redirect($redirectTo);
    }
}

$pageTitle = 'Login - DWP Esports Cinema';
include __DIR__ . '/includes/head.php';
?>

<body class="bg-neutral-900 flex items-center justify-center min-h-screen">

    <div class="bg-neutral-800 rounded-xl shadow-lg p-8 w-full max-w-md">
        <h1 class="text-2xl font-semibold text-white mb-6 text-center">Login</h1>

        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-4 text-center">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php?redirect=<?= urlencode($redirectTo) ?>" class="space-y-4">
            <?= $csrf->field() ?>
            <div>
                <label class="block text-white mb-1" for="email">Email</label>
                <input id="email" type="email" name="email" placeholder="you@example.com" required autocomplete="email"
                    class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-white mb-1" for="password">Password</label>
                <input id="password" type="password" name="password" placeholder="********" required autocomplete="current-password"
                    class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-500 transition">
                Login
            </button>
        </form>

        <p class="mt-4 text-center text-gray-400">
            Don't have an account?
            <a href="register.php" class="text-blue-400 hover:underline">Register</a>
        </p>
    </div>

</body>

</html>
