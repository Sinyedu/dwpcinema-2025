<?php
session_start();
require_once __DIR__ . '/../classes/Database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$pdo = Database::getInstance();

$stmt = $pdo->query("
    SELECT t.tournamentID,
           t.tournamentName,
           t.tournamentDescription,
           t.startDate,
           t.endDate,
           g.gameName
    FROM Tournament t
    JOIN Game g ON t.gameID = g.gameID
    ORDER BY t.startDate ASC
");
$tournaments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$gamesStmt = $pdo->query("SELECT gameID, gameName FROM Game ORDER BY gameName ASC");
$games = $gamesStmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $stmt = $pdo->prepare("
            INSERT INTO Tournament (tournamentName, tournamentDescription, startDate, endDate, gameID)
            VALUES (:name, :description, :startDate, :endDate, :gameID)
        ");
        $stmt->execute([
            ':name' => $_POST['tournamentName'],
            ':description' => $_POST['tournamentDescription'],
            ':startDate' => $_POST['startDate'],
            ':endDate' => $_POST['endDate'],
            ':gameID' => $_POST['gameID']
        ]);
        header("Location: tournaments.php");
        exit;
    }

    if (isset($_POST['update'])) {
        $stmt = $pdo->prepare("
            UPDATE Tournament
            SET tournamentName = :name,
                tournamentDescription = :description,
                startDate = :startDate,
                endDate = :endDate,
                gameID = :gameID
            WHERE tournamentID = :id
        ");
        $stmt->execute([
            ':name' => $_POST['tournamentName'],
            ':description' => $_POST['tournamentDescription'],
            ':startDate' => $_POST['startDate'],
            ':endDate' => $_POST['endDate'],
            ':gameID' => $_POST['gameID'],
            ':id' => $_POST['tournamentID']
        ]);
        header("Location: tournaments.php");
        exit;
    }

    if (isset($_POST['delete'])) {
        $stmt = $pdo->prepare("DELETE FROM Tournament WHERE tournamentID = ?");
        $stmt->execute([$_POST['tournamentID']]);
        header("Location: tournaments.php");
        exit;
    }
}

$editTournament = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM Tournament WHERE tournamentID = ?");
    $stmt->execute([$_GET['edit']]);
    $editTournament = $stmt->fetch(PDO::FETCH_ASSOC);
}

include __DIR__ . '/../includes/adminSidebar.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Tournaments - DWP Esports Cinema</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-neutral-900 min-h-screen ml-64">
    <div class="max-w-6xl mx-auto px-6 py-10">
        <h1 class="text-2xl font-semibold mb-6">Manage Tournaments</h1>

        <?php if ($editTournament): ?>
            <h2 class="text-xl font-semibold mb-2">Edit Tournament</h2>
            <form method="POST" class="space-y-4 bg-white p-6 rounded shadow mb-6">
                <input type="hidden" name="tournamentID" value="<?= $editTournament['tournamentID'] ?>">

                <input type="text" name="tournamentName" value="<?= htmlspecialchars($editTournament['tournamentName']) ?>" placeholder="Tournament Name" class="w-full border rounded px-3 py-2" required>

                <textarea name="tournamentDescription" placeholder="Description" class="w-full border rounded px-3 py-2" required><?= htmlspecialchars($editTournament['tournamentDescription']) ?></textarea>

                <div class="flex space-x-2">
                    <input type="date" name="startDate" value="<?= $editTournament['startDate'] ?>" class="w-1/2 border rounded px-3 py-2" required>
                    <input type="date" name="endDate" value="<?= $editTournament['endDate'] ?>" class="w-1/2 border rounded px-3 py-2" required>
                </div>

                <select name="gameID" class="w-full border rounded px-3 py-2" required>
                    <option value="">Select Game</option>
                    <?php foreach ($games as $g): ?>
                        <option value="<?= $g['gameID'] ?>" <?= $g['gameID'] == $editTournament['gameID'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($g['gameName']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <div class="flex space-x-2">
                    <button type="submit" name="update" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-400">Update</button>
                    <a href="tournaments.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-400">Cancel</a>
                </div>
            </form>
        <?php endif; ?>

        <h2 class="text-xl font-semibold mb-2">Add Tournament</h2>
        <form method="POST" class="space-y-4 bg-white p-6 rounded shadow mb-10">
            <input type="text" name="tournamentName" placeholder="Tournament Name" class="w-full border rounded px-3 py-2" required>
            <textarea name="tournamentDescription" placeholder="Description" class="w-full border rounded px-3 py-2" required></textarea>
            <div class="flex space-x-2">
                <input type="date" name="startDate" class="w-1/2 border rounded px-3 py-2" required>
                <input type="date" name="endDate" class="w-1/2 border rounded px-3 py-2" required>
            </div>
            <select name="gameID" class="w-full border rounded px-3 py-2" required>
                <option value="">Select Game</option>
                <?php foreach ($games as $g): ?>
                    <option value="<?= $g['gameID'] ?>"><?= htmlspecialchars($g['gameName']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="add" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-500">Add Tournament</button>
        </form>

        <table class="w-full table-auto bg-white rounded shadow overflow-hidden">
            <thead>
                <tr class="bg-gray-200">
                    <th class="px-4 py-2">ID</th>
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">Game</th>
                    <th class="px-4 py-2">Start</th>
                    <th class="px-4 py-2">End</th>
                    <th class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tournaments as $t): ?>
                    <tr class="border-b">
                        <td class="px-4 py-2"><?= $t['tournamentID'] ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($t['tournamentName']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($t['gameName']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($t['startDate']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($t['endDate']) ?></td>
                        <td class="px-4 py-2 space-x-2">
                            <a href="tournaments.php?edit=<?= $t['tournamentID'] ?>" class="bg-yellow-500 text-white px-2 py-1 rounded hover:bg-yellow-400">Edit</a>
                            <form method="POST" class="inline">
                                <input type="hidden" name="tournamentID" value="<?= $t['tournamentID'] ?>">
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