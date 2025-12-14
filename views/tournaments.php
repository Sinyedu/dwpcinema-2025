<?php
session_start();
require_once __DIR__ . '/../classes/Database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$pdo = Database::getInstance();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token');
    }

    if (isset($_POST['addTournament'])) {
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

    if (isset($_POST['updateTournament'])) {
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

    if (isset($_POST['toggleHideTournament'])) {
        $stmt = $pdo->prepare("UPDATE Tournament SET isHidden = :hidden WHERE tournamentID = :id");
        $stmt->execute([
            ':hidden' => $_POST['isHidden'],
            ':id' => $_POST['tournamentID']
        ]);
        header("Location: tournaments.php");
        exit;
    }

    if (isset($_POST['addMatch'])) {
        $stmt = $pdo->prepare("
            INSERT INTO `Match` (tournamentID, gameID, matchName, matchDate, matchTime, hallID)
            VALUES (:tournamentID, :gameID, :matchName, :matchDate, :matchTime, :hallID)
        ");
        $stmt->execute([
            ':tournamentID' => $_POST['tournamentID'],
            ':gameID' => $_POST['gameID'],
            ':matchName' => $_POST['matchName'],
            ':matchDate' => $_POST['matchDate'],
            ':matchTime' => $_POST['matchTime'],
            ':hallID' => $_POST['hallID']
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

$tournaments = $pdo->query("
    SELECT t.*, g.gameName
    FROM Tournament t
    LEFT JOIN Game g ON t.gameID = g.gameID
    ORDER BY t.startDate ASC
")->fetchAll(PDO::FETCH_ASSOC);

$games = $pdo->query("SELECT * FROM Game ORDER BY gameName ASC")->fetchAll(PDO::FETCH_ASSOC);
$halls = $pdo->query("SELECT * FROM Hall ORDER BY hallName ASC")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../includes/adminSidebar.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Tournaments - DWP Esports Cinema</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-neutral-900 min-h-screen ml-64 text-white">
    <div class="max-w-6xl mx-auto px-6 py-10">

        <h1 class="text-2xl font-semibold mb-6">Manage Tournaments</h1>

        <form method="POST" class="space-y-4 bg-neutral-800 p-6 rounded shadow mb-10">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
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
            <button type="submit" name="addTournament" class="bg-blue-600 hover:bg-blue-500 px-4 py-2 rounded">Add Tournament</button>
        </form>

        <?php foreach ($tournaments as $t): ?>
            <div class="bg-neutral-800 rounded shadow mb-6 p-4 <?= $t['isHidden'] ? 'opacity-50' : '' ?>">

                <div class="flex justify-between items-center mb-2">
                    <h2 class="text-xl font-semibold"><?= htmlspecialchars($t['tournamentName']) ?> (<?= htmlspecialchars($t['gameName']) ?>)</h2>
                    <div class="flex space-x-2">
                        <a href="tournaments.php?edit=<?= $t['tournamentID'] ?>" class="bg-yellow-500 hover:bg-yellow-400 px-2 py-1 rounded">Edit</a>

                        <form method="POST" class="inline">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            <input type="hidden" name="tournamentID" value="<?= $t['tournamentID'] ?>">
                            <input type="hidden" name="isHidden" value="<?= $t['isHidden'] ? 0 : 1 ?>">
                            <button type="submit" name="toggleHideTournament"
                                class="<?= $t['isHidden'] ? 'bg-green-600 hover:bg-green-500' : 'bg-red-600 hover:bg-red-500' ?> px-2 py-1 rounded text-white">
                                <?= $t['isHidden'] ? 'Unhide' : 'Hide' ?>
                            </button>
                        </form>
                    </div>
                </div>

                <p class="mb-2"><?= htmlspecialchars($t['tournamentDescription']) ?></p>
                <p class="mb-2">Dates: <?= $t['startDate'] ?> - <?= $t['endDate'] ?></p>

                <?php
                $matchesStmt = $pdo->prepare("SELECT m.*, h.hallName FROM `Match` m LEFT JOIN Hall h ON m.hallID = h.hallID WHERE m.tournamentID = ? ORDER BY m.matchDate, m.matchTime");
                $matchesStmt->execute([$t['tournamentID']]);
                $matches = $matchesStmt->fetchAll(PDO::FETCH_ASSOC);
                ?>

                <?php if ($matches): ?>
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
                <?php endif; ?>

                <form method="POST" class="space-y-2 bg-neutral-700 p-4 rounded">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="tournamentID" value="<?= $t['tournamentID'] ?>">

                    <select name="gameID" class="w-full px-2 py-1 rounded bg-neutral-800 text-white" required>
                        <option value="">Select Game</option>
                        <?php foreach ($games as $g): ?>
                            <option value="<?= $g['gameID'] ?>" <?= $g['gameID'] == $t['gameID'] ? 'selected' : '' ?>><?= htmlspecialchars($g['gameName']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <input type="text" name="matchName" placeholder="Match Name" class="w-full px-2 py-1 rounded bg-neutral-800 text-white" required>
                    <input type="date" name="matchDate" class="w-full px-2 py-1 rounded bg-neutral-800 text-white" required>
                    <input type="time" name="matchTime" class="w-full px-2 py-1 rounded bg-neutral-800 text-white" required>

                    <select name="hallID" class="w-full px-2 py-1 rounded bg-neutral-800 text-white" required>
                        <option value="">Select Hall</option>
                        <?php foreach ($halls as $h): ?>
                            <option value="<?= $h['hallID'] ?>"><?= htmlspecialchars($h['hallName']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit" name="addMatch" class="bg-blue-600 hover:bg-blue-500 px-4 py-2 rounded text-white w-full">Add Match</button>
                </form>

            </div>
        <?php endforeach; ?>

    </div>
</body>

</html>