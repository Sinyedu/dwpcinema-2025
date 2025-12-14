<?php
session_start();
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../controllers/AboutUsController.php';

$pdo = Database::getInstance();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$ctrl = new AboutUsController($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $ctrl->create($_POST);
        header("Location: aboutus.php");
        exit;
    }

    if (isset($_POST['update'])) {
        $ctrl->update($_POST['aboutID'], $_POST);
        header("Location: aboutus.php");
        exit;
    }

    if (isset($_POST['delete'])) {
        $ctrl->delete($_POST['aboutID']);
        header("Location: aboutus.php");
        exit;
    }
}

$aboutItems = $ctrl->getAll();
$editItem = isset($_GET['edit']) ? $ctrl->getById((int)$_GET['edit']) : null;

include __DIR__ . '/../includes/adminSidebar.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin About Us - DWP</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen">
    <div class="max-w-6xl mx-auto px-6 py-10">

        <h1 class="text-2xl font-semibold mb-6">Manage About Us</h1>

        <?php if ($editItem): ?>
            <h2 class="text-xl font-semibold mb-2">Edit Section</h2>
            <form method="POST" class="space-y-4 bg-white p-6 rounded shadow mb-6">
                <input type="hidden" name="aboutID" value="<?= $editItem['aboutID'] ?>">
                <input type="text" name="aboutTitle" value="<?= htmlspecialchars($editItem['aboutTitle']) ?>" class="w-full border rounded px-3 py-2" placeholder="Title" required>
                <textarea name="aboutContent" class="w-full border rounded px-3 py-2" placeholder="Content" required><?= htmlspecialchars($editItem['aboutContent']) ?></textarea>
                <input type="text" name="aboutFooter" value="<?= htmlspecialchars($editItem['aboutFooter']) ?>" class="w-full border rounded px-3 py-2" placeholder="Footer (optional)">
                <div class="flex space-x-2">
                    <button type="submit" name="update" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-400">Update</button>
                    <a href="aboutus.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-400">Cancel</a>
                </div>
            </form>
        <?php endif; ?>

        <h2 class="text-xl font-semibold mb-2">Add Section</h2>
        <form method="POST" class="space-y-4 bg-white p-6 rounded shadow mb-10">
            <input type="text" name="aboutTitle" placeholder="Title" class="w-full border rounded px-3 py-2" required>
            <textarea name="aboutContent" placeholder="Content" class="w-full border rounded px-3 py-2" required></textarea>
            <input type="text" name="aboutFooter" placeholder="Footer (optional)" class="w-full border rounded px-3 py-2">
            <button type="submit" name="add" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-500">Add Section</button>
        </form>

        <table class="w-full table-auto bg-white rounded shadow overflow-hidden">
            <thead>
                <tr class="bg-gray-200">
                    <th class="px-4 py-2">ID</th>
                    <th class="px-4 py-2">Title</th>
                    <th class="px-4 py-2">Content</th>
                    <th class="px-4 py-2">Footer</th>
                    <th class="px-4 py-2">Last Updated</th>
                    <th class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($aboutItems as $item): ?>
                    <tr class="border-b">
                        <td class="px-4 py-2"><?= $item['aboutID'] ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($item['aboutTitle']) ?></td>
                        <td class="px-4 py-2"><?= nl2br(htmlspecialchars($item['aboutContent'])) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($item['aboutFooter']) ?></td>
                        <td class="px-4 py-2"><?= $item['lastUpdated'] ?></td>
                        <td class="px-4 py-2 space-x-2">
                            <a href="aboutus.php?edit=<?= $item['aboutID'] ?>" class="bg-yellow-500 text-white px-2 py-1 rounded hover:bg-yellow-400">Edit</a>
                            <form method="POST" class="inline">
                                <input type="hidden" name="aboutID" value="<?= $item['aboutID'] ?>">
                                <button type="submit" name="delete" class="bg-red-600 text-white px-2 py-1 rounded hover:bg-red-500">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>