<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();
$tournamentModel = new Tournament(db());
$validDate = static fn($v) => is_string($v) && ($d = DateTime::createFromFormat('Y-m-d', $v)) && $d->format('Y-m-d') === $v;
$validTime = static fn($v) => is_string($v) && preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', $v) === 1;

if (is_post()) {
    require_csrf();

    if (isset($_POST['addTournament']) || isset($_POST['updateTournament'])) {
        $data = [
            'tournamentName' => post_str('tournamentName'),
            'tournamentDescription' => post_str('tournamentDescription'),
            'startDate' => post_str('startDate'),
            'endDate' => post_str('endDate'),
            'gameID' => (int)($_POST['gameID'] ?? 0),
        ];
        if ($data['tournamentName'] === '' || !$data['gameID'] || !$validDate($data['startDate']) || !$validDate($data['endDate']) || $data['endDate'] < $data['startDate']) {
            flash('Please provide a name, a game, and a valid start and end date (end after start).');
        } elseif (isset($_POST['addTournament'])) {
            $tournamentModel->create($data);
        } else {
            $tournamentModel->update((int)($_POST['tournamentID'] ?? 0), $data);
        }
    } elseif (isset($_POST['toggleHideTournament'])) {
        $tournamentModel->setHidden((int)($_POST['tournamentID'] ?? 0), (bool)(int)($_POST['isHidden'] ?? 0));
    } elseif (isset($_POST['addMatch'])) {
        $data = [
            'tournamentID' => (int)($_POST['tournamentID'] ?? 0),
            'gameID' => (int)($_POST['gameID'] ?? 0),
            'matchName' => post_str('matchName'),
            'matchDate' => post_str('matchDate'),
            'matchTime' => post_str('matchTime'),
            'hallID' => (int)($_POST['hallID'] ?? 0),
        ];
        if ($data['matchName'] === '' || !$data['tournamentID'] || !$data['gameID'] || !$data['hallID'] || !$validDate($data['matchDate']) || !$validTime($data['matchTime'])) {
            flash('Please provide a match name, game, hall, and a valid date and time.');
        } else {
            $tournamentModel->addMatchWithShowing($data);
        }
    }
    redirect('views/tournaments.php');
}

$tournaments = $tournamentModel->getAll();
$games = (new Game(db()))->getAllByName();
$halls = $tournamentModel->getHalls();
$matchesByTournament = $tournamentModel->getMatchesGrouped();
$csrf_token = $csrf->token();
?>
<?php
$pageTitle = 'Admin Tournaments - DWP Esports Cinema';
include __DIR__ . '/../includes/head.php';
?>

<body class="bg-neutral-900 min-h-screen ml-64 text-white">
    <?php include __DIR__ . '/../includes/adminSidebar.php'; ?>
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
                $matches = $matchesByTournament[$t['tournamentID']] ?? [];
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