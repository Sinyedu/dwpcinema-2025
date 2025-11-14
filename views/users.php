<?php
session_start();
include __DIR__ . '/../includes/adminSidebar.php';
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
}

$users = $userController->listUsers();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Users - DWP Esports Cinema</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen">

    <div class="max-w-7xl mx-auto px-6 py-10">
        <h1 class="text-2xl font-semibold mb-6">Manage Users</h1>

        <table class="w-full table-auto bg-white rounded shadow overflow-hidden">
            <thead>
                <tr class="bg-gray-200 text-left">
                    <th class="px-4 py-2">ID</th>
                    <th class="px-4 py-2">Avatar</th>
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">Email</th>
                    <th class="px-4 py-2">Last Active</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2"><?= $u['userID'] ?></td>
                        <td class="px-4 py-2">
                            <img src="/<?= htmlspecialchars($u['avatar'] ?? 'uploads/avatars/default.png') ?>"
                                alt="Avatar"
                                class="w-10 h-10 rounded-full object-cover border">
                        </td>
                        <td class="px-4 py-2">
                            <?= htmlspecialchars($u['firstName'] . ' ' . $u['lastName']) ?>
                        </td>
                        <td class="px-4 py-2"><?= htmlspecialchars($u['userEmail']) ?></td>
                        <td class="px-4 py-2">
                            <?= $u['isActive'] ? htmlspecialchars($u['isActive']) : '<span class="text-gray-400 italic">Never</span>' ?>
                        </td>
                        <td class="px-4 py-2">
                            <?= $u['lastActive'] ? date('d M Y H:i', strtotime($u['lastActive'])) : '<span class="text-gray-400 italic">Never</span>' ?>
                        </td>
                        <td class="px-4 py-2">
                            <?php if ($u['isActive']): ?>
                                <span class="text-green-600 font-semibold">Active</span>
                            <?php else: ?>
                                <span class="text-red-600 font-semibold">Deactivated</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-2 text-center space-x-2">
                            <form method="POST" class="inline">
                                <input type="hidden" name="userID" value="<?= $u['userID'] ?>">
                                <?php if ($u['isActive']): ?>
                                    <button type="submit" name="deactivate"
                                        class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-400">
                                        Deactivate
                                    </button>
                                <?php else: ?>
                                    <button type="submit" name="activate"
                                        class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-500">
                                        Activate
                                    </button>
                                <?php endif; ?>
                            </form>

                            <form method="POST" class="inline">
                                <input type="hidden" name="userID" value="<?= $u['userID'] ?>">
                                <button type="submit" name="delete"
                                    class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-500">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>

</body>

</html>