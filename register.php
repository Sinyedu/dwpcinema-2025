<?php
require_once __DIR__ . '/includes/bootstrap.php';

$errors = [];
$old = ['firstName' => '', 'lastName' => '', 'email' => ''];

if (is_post()) {
    require_csrf();

    $input = UserInput::registration($_POST);
    $errors = $input['errors'];
    $old = array_merge($old, array_intersect_key($input['data'], $old));

    if (!$errors) {
        $d = $input['data'];
        if ((new User(db()))->register($d['firstName'], $d['lastName'], $d['email'], $d['password'])) {
            redirect('login.php');
        }
        $errors['email'] = 'Registration failed. That email may already be in use.';
    }
}

$pageTitle = 'Register - DWP Esports Cinema';
include __DIR__ . '/includes/head.php';

$fieldClass = 'w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none';
?>

<body class="bg-neutral-900 flex items-center justify-center min-h-screen">

    <div class="bg-neutral-800 rounded-xl shadow-lg p-8 w-full max-w-md">
        <h1 class="text-2xl font-semibold text-white mb-6 text-center">Create an Account</h1>

        <?php if ($errors): ?>
            <ul class="bg-red-100 text-red-800 px-4 py-2 rounded mb-4 list-disc list-inside">
                <?php foreach ($errors as $message): ?>
                    <li><?= e($message) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <?= $csrf->field() ?>
            <div>
                <label class="block text-white mb-1" for="firstName">First Name</label>
                <input id="firstName" type="text" name="firstName" value="<?= e($old['firstName']) ?>" placeholder="John" required maxlength="100" autocomplete="given-name" class="<?= $fieldClass ?>">
            </div>
            <div>
                <label class="block text-white mb-1" for="lastName">Last Name</label>
                <input id="lastName" type="text" name="lastName" value="<?= e($old['lastName']) ?>" placeholder="Doe" required maxlength="100" autocomplete="family-name" class="<?= $fieldClass ?>">
            </div>
            <div>
                <label class="block text-white mb-1" for="email">Email</label>
                <input id="email" type="email" name="email" value="<?= e($old['email']) ?>" placeholder="you@example.com" required maxlength="100" autocomplete="email" class="<?= $fieldClass ?>">
            </div>
            <div>
                <label class="block text-white mb-1" for="password">Password</label>
                <input id="password" type="password" name="password" placeholder="At least <?= UserInput::PASSWORD_MIN ?> characters" required minlength="<?= UserInput::PASSWORD_MIN ?>" autocomplete="new-password" class="<?= $fieldClass ?>">
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-500 transition">Register</button>
        </form>

        <p class="mt-4 text-center text-gray-400">
            Already have an account?
            <a href="login.php" class="text-blue-400 hover:underline">Login</a>
        </p>
    </div>

</body>

</html>
