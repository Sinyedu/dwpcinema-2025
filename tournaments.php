<?php
session_start();
include __DIR__ . '/includes/navbar.php';

require_once __DIR__ . '/classes/Database.php';

$pdo = Database::getInstance();

$stmt = $pdo->query("
    SELECT t.tournamentID, 
           t.tournamentName, 
           t.tournamentDescription, 
           t.startDate, 
           t.endDate, 
           g.gameName,
           g.gameGenre,
           (SELECT COUNT(*) FROM `Match` m 
            JOIN Showing s ON m.matchID = s.matchID
            WHERE m.tournamentID = t.tournamentID
            AND s.showingDate >= CURDATE()) AS upcomingShowings,
           (SELECT MIN(s.showingDate) FROM `Match` m 
            JOIN Showing s ON m.matchID = s.matchID
            WHERE m.tournamentID = t.tournamentID
            AND s.showingDate >= CURDATE()) AS nextShowing
    FROM Tournament t
    JOIN Game g ON t.gameID = g.gameID
    ORDER BY t.startDate ASC
");
$tournaments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Tournaments - DWP Esports Cinema</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 text-gray-900">

    <main class="max-w-6xl mx-auto px-6 py-16">
        <h2 class="text-3xl font-bold mb-10 text-center">Upcoming Tournaments</h2>

        <div class="grid md:grid-cols-3 gap-8">
            <?php foreach ($tournaments as $t): ?>
                <div class="bg-white rounded-lg shadow hover:shadow-xl transition overflow-hidden">

                    <?php
                    $extensions = ['jpg', 'jpeg', 'png', 'webp'];
                    $imagePath = null;

                    $baseTournament = strtolower(str_replace([' ', ':', '-', "'", '"'], '_', $t['tournamentName']));
                    foreach ($extensions as $ext) {
                        $try = "img/{$baseTournament}.{$ext}";
                        if (file_exists(__DIR__ . '/' . $try)) {
                            $imagePath = $try;
                            break;
                        }
                    }

                    if (!$imagePath) {
                        $baseGame = strtolower(str_replace([' ', ':', '-', "'", '"'], '_', $t['gameName']));
                        foreach ($extensions as $ext) {
                            $try = "img/{$baseGame}.{$ext}";
                            if (file_exists(__DIR__ . '/' . $try)) {
                                $imagePath = $try;
                                break;
                            }
                        }
                    }
                    ?>
                    <div class="h-40 bg-gray-200 overflow-hidden">
                        <?php if ($imagePath): ?>
                            <img src="<?= $imagePath ?>"
                                alt="<?= htmlspecialchars($t['tournamentName']) ?>"
                                class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="flex items-center justify-center h-full text-gray-400 font-bold">
                                <?= htmlspecialchars($t['tournamentName']) ?> Image
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="p-4">
                        <h3 class="text-xl font-semibold mb-1"><?= htmlspecialchars($t['tournamentName']) ?></h3>
                        <p class="text-gray-600 text-sm mb-2">
                            <?= htmlspecialchars($t['gameGenre']) ?> |
                            <?= htmlspecialchars($t['startDate']) ?> -
                            <?= htmlspecialchars($t['endDate']) ?>
                        </p>
                        <p class="text-gray-700 text-sm mb-2">
                            <?= htmlspecialchars(substr($t['tournamentDescription'], 0, 120)) ?>...
                        </p>

                        <?php if ($t['upcomingShowings'] > 0): ?>
                            <p class="text-green-600 text-sm font-medium mb-2">
                                Next Showing: <?= htmlspecialchars($t['nextShowing']) ?>
                            </p>
                            <a href="showings.php?tournamentID=<?= $t['tournamentID'] ?>"
                                class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-500 font-medium">
                                View Showings
                            </a>
                        <?php else: ?>
                            <p class="text-red-600 font-medium mb-2">No upcoming showings</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

</body>

</html>