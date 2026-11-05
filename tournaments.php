<?php
require_once __DIR__ . '/includes/bootstrap.php';

$tournaments = (new Tournament(db()))->getVisibleWithShowings();

$pageTitle = 'Tournaments - DWP Esports Cinema';
include __DIR__ . '/includes/head.php';
?>

<body class="bg-neutral-900 text-white">
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <main class="max-w-6xl mx-auto px-6 py-16">
        <h2 class="text-3xl font-bold mb-10 text-center">Upcoming Tournaments</h2>

        <div class="grid md:grid-cols-3 gap-8">
            <?php foreach ($tournaments as $t):
                $imagePath = tournament_image($t['tournamentName'], $t['gameName'], __DIR__ . '/img');
            ?>
                <div class="bg-neutral-800 rounded-lg shadow hover:shadow-xl transition overflow-hidden">

                    <div class="h-40 bg-neutral-700 overflow-hidden">
                        <?php if ($imagePath): ?>
                            <img src="<?= e($imagePath) ?>" alt="<?= e($t['tournamentName']) ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="flex items-center justify-center h-full text-gray-400 font-bold">
                                <?= e($t['tournamentName']) ?> Image
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="p-4">
                        <h3 class="text-xl font-semibold mb-1"><?= e($t['tournamentName']) ?></h3>
                        <p class="text-sm text-gray-300 mb-2">
                            <?= e($t['gameGenre']) ?> |
                            <?= e(date('F j, Y', strtotime($t['startDate']))) ?> -
                            <?= e(date('F j, Y', strtotime($t['endDate']))) ?>
                        </p>
                        <p class="text-sm text-gray-300 mb-2">
                            <?= e(mb_substr((string)$t['tournamentDescription'], 0, 120)) ?>...
                        </p>

                        <?php if ($t['upcomingShowings'] > 0): ?>
                            <p class="text-green-500 text-sm font-medium mb-2">
                                Next Showing: <?= e(date('F j, Y', strtotime($t['nextShowing']))) ?>
                            </p>
                            <a href="showings.php?tournamentID=<?= (int)$t['tournamentID'] ?>"
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
