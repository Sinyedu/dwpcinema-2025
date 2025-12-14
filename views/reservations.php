<?php
session_start();
include __DIR__ . '/../includes/adminSidebar.php';
require_once __DIR__ . '/../classes/Database.php';

$pdo = Database::getInstance();

$reservations = $pdo->query("
    SELECT cf.*, t.tournamentName
    FROM ContactForm cf
    LEFT JOIN Tournament t ON cf.tournamentID = t.tournamentID
    WHERE cf.category='Reservation'
    ORDER BY cf.createdAt DESC
")->fetchAll(PDO::FETCH_ASSOC);

$tournaments = $pdo->query("SELECT tournamentID, tournamentName FROM Tournament ORDER BY startDate DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Reservations - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen flex">

    <?= $sidebar ?? '' ?>

    <main class="flex-1 p-6 ml-64">
        <h1 class="text-2xl font-semibold mb-6">Manage Reservations</h1>

        <div class="overflow-x-auto">
            <table class="table-auto border-collapse border border-gray-300 w-full bg-white rounded shadow">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border p-2">#</th>
                        <th class="border p-2">Name</th>
                        <th class="border p-2">Email</th>
                        <th class="border p-2">Message</th>
                        <th class="border p-2">Tournament</th>
                        <th class="border p-2">Status</th>
                        <th class="border p-2">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as $r): ?>
                        <tr>
                            <form action="process_reservations.php" method="POST" class="w-full">
                                <td class="border p-2"><?= $r['contactFormid'] ?></td>
                                <td class="border p-2"><?= htmlspecialchars($r['firstName'] . ' ' . $r['lastName']) ?></td>
                                <td class="border p-2"><?= htmlspecialchars($r['email']) ?></td>
                                <td class="border p-2"><?= nl2br(htmlspecialchars($r['message'])) ?></td>
                                <td class="border p-2">
                                    <?= !empty($r['tournamentID'])
                                        ? htmlspecialchars($tournaments[array_search($r['tournamentID'], array_column($tournaments, 'tournamentID'))]['tournamentName'])
                                        : 'User did not select a tournament';
                                    ?>
                                </td>

                                <td class="border p-2">
                                    <select name="status" class="border rounded p-1 w-full">
                                        <?php
                                        $statuses = ['Pending', 'In Review', 'Sent Message', 'Confirmed', 'Cancelled'];
                                        foreach ($statuses as $s): ?>
                                            <option value="<?= $s ?>" <?= (($r['status'] ?? 'Pending') === $s) ? 'selected' : '' ?>><?= $s ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>

                                <td class="border p-2">
                                    <input type="hidden" name="contactFormid" value="<?= $r['contactFormid'] ?>">
                                    <button type="submit" class="bg-blue-600 text-white px-2 py-1 rounded hover:bg-blue-500">Save</button>
                                </td>
                            </form>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>

</html>