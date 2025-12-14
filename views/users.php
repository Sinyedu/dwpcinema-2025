<?php
session_start();
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '../../controllers/UserController.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$userController = new UserController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userID = (int)$_POST['userID'];

    if (isset($_POST['deactivate'])) {
        $userController->deactivateUser($userID);
        header("Location: users.php");
        exit;
    }

    if (isset($_POST['activate'])) {
        $userController->activateUser($userID);
        header("Location: users.php");
        exit;
    }

    if (isset($_POST['delete'])) {
        $userController->deleteUser($userID);
        header("Location: users.php");
        exit;
    }
}

$users = $userController->listUsers();

include __DIR__ . '/../includes/adminSidebar.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Users - DWP Esports Cinema</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-neutral-900 min-h-screen ml-64">

    <div class="max-w-7xl mx-auto px-6 py-10">
        <h1 class="text-3xl font-bold mb-6 text-white">Manage Users</h1>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-neutral-800 rounded-lg shadow divide-y divide-gray-200">
                <thead class="bg-neutral-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-white uppercase text-sm">ID</th>
                        <th class="px-4 py-3 text-left text-white uppercase text-sm">Avatar</th>
                        <th class="px-4 py-3 text-left text-white uppercase text-sm">Name</th>
                        <th class="px-4 py-3 text-left text-white uppercase text-sm">Email</th>
                        <th class="px-4 py-3 text-left text-white uppercase text-sm">Last Active</th>
                        <th class="px-4 py-3 text-left text-white uppercase text-sm">Status</th>
                        <th class="px-4 py-3 text-center text-white uppercase text-sm">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($users as $u): ?>
                        <tr class="hover:bg-neutral-600 transition">
                            <td class="px-4 py-2 text-white"><?= $u['userID'] ?></td>
                            <td class="px-4 py-2">
                                <img src="<?= htmlspecialchars($u['avatar'] ?: 'uploads/avatars/default.png') ?>" alt="Avatar" class="rounded-full w-12 h-12">

                            </td>
                            <td class="px-4 py-2 font-medium text-white"><?= htmlspecialchars($u['firstName'] . ' ' . $u['lastName']) ?></td>
                            <td class="px-4 py-2 text-gray-300"><?= htmlspecialchars($u['userEmail']) ?></td>
                            <td class="px-4 py-2 text-gray-300">
                                <?= $u['lastActive'] ? date('d M Y H:i', strtotime($u['lastActive'])) : '<span class="text-gray-400 italic">Never</span>' ?>
                            </td>
                            <td class="px-4 py-2 text-center">
                                <?php if ($u['isActive']): ?>
                                    <span class="inline-block px-2 py-1 text-white bg-green-100 rounded-full text-xs font-semibold">Active</span>
                                <?php else: ?>
                                    <span class="inline-block px-2 py-1 text-white bg-red-100 rounded-full text-xs font-semibold">Deactivated</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-2 flex flex-col sm:flex-row items-center justify-center gap-2">
                                <?php if ($u['isActive']): ?>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="userID" value="<?= $u['userID'] ?>">
                                        <button type="submit" name="deactivate"
                                            class="bg-yellow-500 hover:bg-yellow-400 text-white px-3 py-1 rounded transition text-sm">
                                            Deactivate
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="userID" value="<?= $u['userID'] ?>">
                                        <button type="submit" name="activate"
                                            class="bg-green-600 hover:bg-green-500 text-white px-3 py-1 rounded transition text-sm">
                                            Activate
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="userID" value="<?= $u['userID'] ?>">
                                    <button type="submit" name="delete"
                                        class="bg-red-600 hover:bg-red-500 text-white px-3 py-1 rounded transition text-sm">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>

</html>