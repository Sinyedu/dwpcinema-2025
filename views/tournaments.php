<?php
session_start();
require_once __DIR__ . '/../classes/Database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$pdo = Database::getInstance();

$tournaments = $pdo->query("
    SELECT t.*, g.gameName 
    FROM Tournament t
    LEFT JOIN Game g ON t.gameID = g.gameID
    ORDER BY t.startDate ASC
")->fetchAll(PDO::FETCH_ASSOC);

$games = $pdo->query("SELECT * FROM Game ORDER BY gameName ASC")->fetchAll(PDO::FETCH_ASSOC);
$halls = $pdo->query("SELECT * FROM Hall ORDER BY hallName ASC")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['addTournament'])) {
        $stmt = $pdo->prepare("
            INSERT INTO Tournament (tournamentName, tournamentDescription, startDate, endDate, gameID)
            VALUES (:name, :desc, :start, :end, :gameID)
        ");
        $stmt->execute([
            ':name' => $_POST['tournamentName'],
            ':desc' => $_POST['tournamentDescription'],
            ':start' => $_POST['startDate'],
            ':end' => $_POST['endDate'],
            ':gameID' => $_POST['gameID']
        ]);
        header("Location: tournaments.php");
        exit;
    }

    if (isset($_POST['updateTournament'])) {
        $stmt = $pdo->prepare("
            UPDATE Tournament
            SET tournamentName = :name,
                tournamentDescription = :desc,
                startDate = :start,
                endDate = :end,
                gameID = :gameID
            WHERE tournamentID = :id
        ");
        $stmt->execute([
            ':name' => $_POST['tournamentName'],
            ':desc' => $_POST['tournamentDescription'],
            ':start' => $_POST['startDate'],
            ':end' => $_POST['endDate'],
            ':gameID' => $_POST['gameID'],
            ':id' => $_POST['tournamentID']
        ]);
        header("Location: tournaments.php");
        exit;
    }

    if (isset($_POST['deleteTournament'])) {
        $stmt = $pdo->prepare("UPDATE Tournament SET isHidden = 1 WHERE tournamentID = ?");
        $stmt->execute([$_POST['tournamentID']]);
        header("Location: tournaments.php");
        exit;
    }

    if (isset($_POST['addMatch'])) {
        $stmt = $pdo->prepare("
            INSERT INTO `Match` (tournamentID, gameID, matchName, matchDate, matchTime, hallID)
            VALUES (:tID, :gID, :mName, :mDate, :mTime, :hID)
        ");
        $stmt->execute([
            ':tID' => $_POST['tournamentID'],
            ':gID' => $_POST['gameID'],
            ':mName' => $_POST['matchName'],
            ':mDate' => $_POST['matchDate'],
            ':mTime' => $_POST['matchTime'],
            ':hID' => $_POST['hallID']
        ]);

        $matchID = $pdo->lastInsertId();

        $stmt2 = $pdo->prepare("
            INSERT INTO Showing (matchID, hallID, showingDate, showingTime)
            VALUES (:matchID, :hallID, :date, :time)
        ");
        $stmt2->execute([
            ':matchID' => $matchID,
            ':hallID' => $_POST['hallID'],
            ':date' => $_POST['matchDate'],
            ':time' => $_POST['matchTime']
        ]);

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
        <h1 class="text-2xl text-white font-semibold mb-6">Manage Tournaments</h1>

        <?php if ($editTournament): ?>
            <form method="POST" class="space-y-4 bg-neutral-800 p-6 rounded shadow mb-6">
                <input type="hidden" name="tournamentID" value="<?= $editTournament['tournamentID'] ?>">
                <input type="text" name="tournamentName" value="<?= htmlspecialchars($editTournament['tournamentName']) ?>" placeholder="Tournament Name" class="w-full border rounded px-3 py-2" required>
                <textarea name="tournamentDescription" placeholder="Description" class="w-full border rounded px-3 py-2" required><?= htmlspecialchars($editTournament['tournamentDescription']) ?></textarea>
                <div class="flex space-x-2">
                    <input type="date" name="startDate" value="<?= $editTournament['startDate'] ?>" class="w-1/2 border rounded px-3 py-2 bg-neutral-900 text-white" required>
                    <input type="date" name="endDate" value="<?= $editTournament['endDate'] ?>" class="w-1/2 border rounded px-3 py-2 bg-neutral-900 text-white" required>
                </div>
                <select name="gameID" class="w-full border rounded px-3 py-2 bg-neutral-900 text-white" required>
                    <option value="">Select Game</option>
                    <?php foreach ($games as $g): ?>
                        <option value="<?= $g['gameID'] ?>" <?= $g['gameID'] == $editTournament['gameID'] ? 'selected' : '' ?>><?= htmlspecialchars($g['gameName']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="flex space-x-2">
                    <button type="submit" name="updateTournament" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-400">Update</button>
                    <a href="tournaments.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-400">Cancel</a>
                </div>
            </form>
        <?php endif; ?>

        <form method="POST" class="space-y-4 bg-neutral-800 p-6 rounded shadow mb-10">
            <input type="text" name="tournamentName" placeholder="Tournament Name" class="w-full border rounded px-3 py-2 bg-neutral-900 text-white" required>
            <textarea name="tournamentDescription" placeholder="Description" class="w-full border rounded px-3 py-2 bg-neutral-900 text-white" required></textarea>
            <div class="flex space-x-2">
                <input type="date" name="startDate" class="w-1/2 border rounded px-3 py-2 bg-neutral-900 text-white" required>
                <input type="date" name="endDate" class="w-1/2 border rounded px-3 py-2 bg-neutral-900 text-white" required>
            </div>
            <select name="gameID" class="w-full border rounded px-3 py-2 bg-neutral-900 text-white" required>
                <option value="">Select Game</option>
                <?php foreach ($games as $g): ?>
                    <option value="<?= $g['gameID'] ?>"><?= htmlspecialchars($g['gameName']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="addTournament" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-500">Add Tournament</button>
        </form>

        <?php foreach ($tournaments as $t):
            if ($t['isHidden']) continue;
            $matchesStmt = $pdo->prepare("SELECT m.*, h.hallName FROM `Match` m LEFT JOIN Hall h ON m.hallID = h.hallID WHERE m.tournamentID = ? ORDER BY m.matchDate, m.matchTime");
            $matchesStmt->execute([$t['tournamentID']]);
            $matches = $matchesStmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
            <div class="bg-neutral-800 rounded shadow mb-6 p-4">
                <div class="flex justify-between items-center mb-2">
                    <h2 class="text-xl text-white font-semibold"><?= htmlspecialchars($t['tournamentName']) ?> (<?= htmlspecialchars($t['gameName']) ?>)</h2>
                    <div class="flex space-x-2">
                        <a href="tournaments.php?edit=<?= $t['tournamentID'] ?>" class="bg-yellow-500 text-white px-2 py-1 rounded hover:bg-yellow-400">Edit</a>
                        <form method="POST" class="inline">
                            <input type="hidden" name="tournamentID" value="<?= $t['tournamentID'] ?>">
                            <button type="submit" name="deleteTournament" class="bg-red-600 text-white px-2 py-1 rounded hover:bg-red-500">Hide</button>
                        </form>
                    </div>
                </div>
                <p class="text-white mb-4"><?= htmlspecialchars($t['tournamentDescription']) ?></p>
                <p class="text-white mb-4">Dates: <?= $t['startDate'] ?> - <?= $t['endDate'] ?></p>

                <table class="w-full table-auto bg-neutral-700 rounded mb-4">
                    <thead>
                        <tr class="text-white">
                            <th class="px-2 py-1">Match</th>
                            <th class="px-2 py-1">Date</th>
                            <th class="px-2 py-1">Time</th>
                            <th class="px-2 py-1">Hall</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($matches as $m): ?>
                            <tr class="border-b border-neutral-600 text-white">
                                <td class="px-2 py-1"><?= htmlspecialchars($m['matchName']) ?></td>
                                <td class="px-2 py-1"><?= $m['matchDate'] ?></td>
                                <td class="px-2 py-1"><?= $m['matchTime'] ?></td>
                                <td class="px-2 py-1"><?= htmlspecialchars($m['hallName']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <form method="POST" class="space-y-2 bg-neutral-600 p-4 rounded">
                    <input type="hidden" name="tournamentID" value="<?= $t['tournamentID'] ?>">
                    <select name="gameID" class="w-full px-2 py-1 rounded" required>
                        <option value="">Select Game</option>
                        <?php foreach ($games as $g): ?>
                            <option value="<?= $g['gameID'] ?>" <?= $g['gameID'] == $t['gameID'] ? 'selected' : '' ?>><?= htmlspecialchars($g['gameName']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="matchName" placeholder="Match Name" class="w-full px-2 py-1 rounded" required>
                    <input type="date" name="matchDate" class="w-full px-2 py-1 rounded" required>
                    <input type="time" name="matchTime" class="w-full px-2 py-1 rounded" required>
                    <select name="hallID" class="w-full px-2 py-1 rounded" required>
                        <option value="">Select Hall</option>
                        <?php foreach ($halls as $h): ?>
                            <option value="<?= $h['hallID'] ?>"><?= htmlspecialchars($h['hallName']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" name="addMatch" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-500">Add Match</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</body>

</html>