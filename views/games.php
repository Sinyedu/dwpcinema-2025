<?php
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include __DIR__ . '/../includes/adminSidebar.php';
require_once "../controllers/GameController.php";
require_once __DIR__ . '/../classes/Database.php';

$pdo = Database::getInstance();
$gameController = new GameController($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token!");
    }

    if (isset($_POST['add'])) {
        $data = [
            'gameName' => $_POST['gameName'] ?? '',
            'gameGenre' => $_POST['gameGenre'] ?? ''
        ];
        $gameController->createGame($data);
        header("Location: games.php");
        exit;
    }

    if (isset($_POST['update'])) {
        $data = [
            'gameName' => $_POST['gameName'] ?? '',
            'gameGenre' => $_POST['gameGenre'] ?? ''
        ];
        $gameController->updateGame($_POST['gameID'], $data);
        header("Location: games.php");
        exit;
    }

    if (isset($_POST['delete'])) {
        $gameController->deleteGame($_POST['gameID']);
        header("Location: games.php");
        exit;
    }
}

$games = $gameController->getAllGames();

$editGame = null;
if (isset($_GET['edit'])) {
    $editGame = $gameController->getGameById((int)$_GET['edit']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Games - DWP Esports Cinema</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-neutral-900 min-h-screen">

    <div class="max-w-6xl mx-auto px-6 py-10">

        <h1 class="text-2xl text-white font-semibold mb-6">Manage Games</h1>
        <?php if ($editGame): ?>
            <h2 class="text-xl text-white font-semibold mb-2">Edit Game</h2>
            <form method="POST" class="space-y-4 bg-white p-6 rounded shadow mb-6">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="gameID" value="<?= $editGame['gameID'] ?>">
                <input type="text" name="gameName" placeholder="Game Name" value="<?= htmlspecialchars($editGame['gameName']) ?>" class="w-full border rounded px-3 py-2" required>
                <input type="text" name="gameGenre" placeholder="Genre" value="<?= htmlspecialchars($editGame['gameGenre']) ?>" class="w-full border rounded px-3 py-2">
                <div class="flex space-x-2">
                    <button type="submit" name="update" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-400">Update Game</button>
                    <a href="games.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-400">Cancel</a>
                </div>
            </form>
        <?php endif; ?>

        <h2 class="text-xl text-white font-semibold mb-2">Add Game</h2>
        <form method="POST" class="space-y-4 bg-neutral-800 p-6 rounded shadow mb-10">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="text" name="gameName" placeholder="Game Name" class="w-full border rounded px-3 py-2 bg-neutral-900 text-white" required>
            <input type="text" name="gameGenre" placeholder="Genre (optional)" class="w-full border rounded px-3 py-2 bg-neutral-900 text-white">
            <button type="submit" name="add" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-500">Add Game</button>
        </form>

        <table class="w-full table-auto bg-neutral-800 rounded shadow overflow-hidden">
            <thead>
                <tr class="bg-neutral-800 text-white">
                    <th class="px-4 py-2">ID</th>
                    <th class="px-4 py-2">Game Name</th>
                    <th class="px-4 py-2">Genre</th>
                    <th class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($games as $g): ?>
                    <tr class="border-b">
                        <td class="px-4 py-2 text-white"><?= $g['gameID'] ?></td>
                        <td class="px-4 py-2 text-white"><?= htmlspecialchars($g['gameName']) ?></td>
                        <td class="px-4 py-2 text-white"><?= htmlspecialchars($g['gameGenre']) ?></td>
                        <td class="px-4 py-2 space-x-2">
                            <a href="games.php?edit=<?= $g['gameID'] ?>" class="bg-yellow-500 text-white px-2 py-1 rounded hover:bg-yellow-400">Edit</a>
                            <form method="POST" class="inline">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <input type="hidden" name="gameID" value="<?= $g['gameID'] ?>">
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