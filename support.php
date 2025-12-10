<?php
session_start();
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/controllers/SupportController.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$pdo = Database::getInstance();
$ctrl = new SupportController($pdo);
$tickets = $ctrl->getUserTickets((int)$_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Support - DWP Cinema</title>
    <script src="/public/js/support.js" defer></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-50 p-6">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold mb-4">Support</h1>

        <section class="mb-6">
            <form id="createTicketForm" class="bg-white p-4 rounded shadow" method="post" action="/api/support/create_ticket.php">
                <label class="block mb-2 font-semibold">Subject</label>
                <input name="subject" class="w-full p-2 border rounded mb-3" required>
                <label class="block mb-2 font-semibold">Message</label>
                <textarea name="message" rows="4" class="w-full p-2 border rounded mb-3" required></textarea>
                <label class="block mb-2 font-semibold">Priority</label>
                <select name="priority" class="p-2 border rounded mb-3">
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                    <option value="high">High</option>
                </select>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Create Ticket</button>
            </form>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-3">Your Tickets</h2>
            <?php if (empty($tickets)): ?>
                <p>No tickets yet.</p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($tickets as $t): ?>
                        <a href="/views/ticket.php?ticketID=<?= (int)$t['ticketID'] ?>" class="block bg-white p-4 rounded shadow hover:bg-gray-50">
                            <div class="flex justify-between">
                                <div>
                                    <div class="font-semibold"><?= htmlspecialchars($t['subject'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="text-xs text-gray-500"><?= htmlspecialchars($t['priority']) ?> • <?= htmlspecialchars($t['status']) ?></div>
                                </div>
                                <div class="text-sm text-gray-500"><?= htmlspecialchars($t['updatedAt']) ?></div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</body>

</html>