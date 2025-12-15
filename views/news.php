<?php
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/../classes/Database.php';
require_once "../controllers/NewsController.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

include __DIR__ . '/../includes/adminSidebar.php';

$pdo = Database::getInstance();
$newsController = new NewsController($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token!");
    }

    $data = [
        'newsTitle' => trim($_POST['newsTitle'] ?? ''),
        'newsContent' => trim($_POST['newsContent'] ?? ''),
        'newsAuthor' => trim($_POST['newsAuthor'] ?? ''),
        'newsImage' => trim($_POST['newsImage'] ?? null)
    ];

    if (isset($_POST['add'])) {
        $newsController->createNews($data);
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        header("Location: news.php");
        exit;
    }

    if (isset($_POST['update'])) {
        $newsController->updateNews($_POST['newsID'], $data);
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        header("Location: news.php");
        exit;
    }

    if (isset($_POST['delete'])) {
        $newsController->deleteNews($_POST['newsID']);
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        header("Location: news.php");
        exit;
    }
}

$news = $newsController->getAllNews();
$editNews = isset($_GET['edit']) ? $newsController->getNewsById((int)$_GET['edit']) : null;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin News - DWP Esports Cinema</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-neutral-900 min-h-screen">

    <div class="max-w-6xl mx-auto px-6 py-10">

        <h1 class="text-2xl text-white font-semibold mb-6">Manage News</h1>

        <?php if ($editNews): ?>
            <h2 class="text-xl text-white font-semibold mb-2">Edit News</h2>
            <form method="POST" class="space-y-4 bg-neutral-800 p-6 rounded shadow mb-6">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="newsID" value="<?= $editNews['newsID'] ?>">
                <input type="text" name="newsTitle" placeholder="Title" value="<?= htmlspecialchars($editNews['newsTitle']) ?>" class="w-full bg-neutral-900 border rounded px-3 py-2 text-white">
                <input type="text" name="newsAuthor" placeholder="Author" value="<?= htmlspecialchars($editNews['newsAuthor']) ?>" class="w-full bg-neutral-900 border rounded px-3 py-2 text-white">
                <textarea name="newsContent" placeholder="Content" class="w-full bg-neutral-900 border rounded px-3 py-2 text-white"><?= htmlspecialchars($editNews['newsContent']) ?></textarea>
                <input type="text" name="newsImage" placeholder="Image URL (optional)" value="<?= htmlspecialchars($editNews['newsImage']) ?>" class="w-full bg-neutral-900 border rounded px-3 py-2 text-white">
                <div class="flex space-x-2">
                    <button type="submit" name="update" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-400">Update News</button>
                    <a href="news.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-400">Cancel</a>
                </div>
            </form>
        <?php endif; ?>

        <h2 class="text-xl text-white font-semibold mb-2">Add News</h2>
        <form method="POST" class="space-y-4 bg-neutral-800 p-6 rounded shadow mb-10">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="text" name="newsTitle" placeholder="Title" class="w-full border rounded px-3 py-2 bg-neutral-900 text-white" required>
            <input type="text" name="newsAuthor" placeholder="Author" class="w-full border rounded px-3 py-2 bg-neutral-900 text-white" required>
            <textarea name="newsContent" placeholder="Content" class="w-full border rounded px-3 py-2 bg-neutral-900 text-white" required rows="8"></textarea>
            <input type="text" name="newsImage" placeholder="Image URL (optional)" class="w-full border rounded px-3 py-2 bg-neutral-900 text-white">
            <button type="submit" name="add" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-500 transition font-medium">Add News</button>
        </form>

        <table class="w-full table-auto bg-neutral-800 rounded shadow overflow-hidden">
            <thead>
                <tr class="bg-gray-700 text-white">
                    <th class="px-4 py-2">ID</th>
                    <th class="px-4 py-2">Title</th>
                    <th class="px-4 py-2">Author</th>
                    <th class="px-4 py-2">Created</th>
                    <th class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($news as $n): ?>
                    <tr class="border-b">
                        <td class="px-4 py-2 text-white"><?= $n['newsID'] ?></td>
                        <td class="px-4 py-2 text-white"><?= htmlspecialchars($n['newsTitle']) ?></td>
                        <td class="px-4 py-2 text-white"><?= htmlspecialchars($n['newsAuthor']) ?></td>
                        <td class="px-4 py-2 text-white"><?= $n['newsCreatedAt'] ?></td>
                        <td class="px-4 py-2 space-x-2">
                            <a href="news.php?edit=<?= $n['newsID'] ?>" class="bg-yellow-500 text-white px-2 py-1 rounded hover:bg-yellow-400">Edit</a>
                            <form method="POST" class="inline">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <input type="hidden" name="newsID" value="<?= $n['newsID'] ?>">
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