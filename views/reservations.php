<?php
require_once __DIR__ . '/../classes/Database.php';
$pdo = Database::getInstance();

$reservations = $pdo->query("SELECT * FROM ContactForm WHERE category='Reservation' ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$tournaments = $pdo->query("SELECT tournamentID, tournamentName FROM Tournament ORDER BY startDate DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<script src="https://cdn.tailwindcss.com"></script>
<h1 class="text-2xl font-semibold mb-4">Manage Reservations</h1>

<table class="table-auto border-collapse border border-gray-300 w-full">
    <thead>
        <tr class="bg-gray-100">
            <th class="border p-2">#</th>
            <th class="border p-2">Name</th>
            <th class="border p-2">Email</th>
            <th class="border p-2">Message</th>
            <th class="border p-2">Assign Tournament</th>
            <th class="border p-2">Status</th>
            <th class="border p-2">Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($reservations as $r): ?>
            <tr>
                <form action="process_reservation.php" method="POST" class="w-full">
                    <td class="border p-2"><?= $r['contactFormid'] ?></td>
                    <td class="border p-2"><?= htmlspecialchars($r['firstName'] . ' ' . $r['lastName']) ?></td>
                    <td class="border p-2"><?= htmlspecialchars($r['email']) ?></td>
                    <td class="border p-2"><?= nl2br(htmlspecialchars($r['message'])) ?></td>
                    <td class="border p-2">
                        <select name="tournament_id" class="border rounded p-1 w-full">
                            <option value="">-- Select Tournament --</option>
                            <?php foreach ($tournaments as $t): ?>
                                <option value="<?= $t['tournamentID'] ?>"><?= htmlspecialchars($t['tournamentName']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td class="border p-2">
                        <select name="status" class="border rounded p-1 w-full">
                            <option value="Pending" selected>Pending</option>
                            <option value="Confirmed">Confirmed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </td>
                    <td class="border p-2">
                        <input type="hidden" name="contactFormid" value="<?= $r['contactFormid'] ?>">
                        <button type="submit" class="bg-blue-600 text-white px-2 py-1 rounded hover:bg-blue-500">
                            Save
                        </button>
                    </td>
                </form>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>