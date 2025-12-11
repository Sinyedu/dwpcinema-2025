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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subject'], $_POST['message'], $_POST['priority'])) {
    try {
        $ticketID = $ctrl->createTicket(
            (int)$_SESSION['user_id'],
            $_POST['subject'],
            $_POST['message'],
            $_POST['priority']
        );
        $_SESSION['success'] = "Ticket created successfully!";
        header("Location: support.php?ticketID=$ticketID");
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$tickets = $ctrl->getUserTickets((int)$_SESSION['user_id']);
$activeTicketID = isset($_GET['ticketID']) ? (int)$_GET['ticketID'] : ($tickets[0]['ticketID'] ?? 0);
$messages = $activeTicketID ? $ctrl->getMessages($activeTicketID) : [];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Support - DWP Cinema</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="/public/js/support.js" defer></script>
    <script>
        window.ticketID = <?= $activeTicketID ?>;
    </script>
</head>

<body class="bg-white text-gray-900 flex flex-col min-h-screen">

    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <main class="flex-1 bg-gray-50 p-6 max-w-6xl mx-auto w-full mt-24">
        <h1 class="text-3xl font-bold mb-6">Support Center</h1>

        <?php if (!empty($error)): ?>
            <div class="bg-red-200 text-red-800 p-3 mb-4 rounded"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['success'])): ?>
            <div class="bg-green-200 text-green-800 p-3 mb-4 rounded"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <section class="mb-8">
            <form id="createTicketForm" class="bg-white p-6 rounded-lg shadow space-y-4" method="post">
                <h2 class="text-xl font-semibold">Create New Ticket</h2>
                <input name="subject" placeholder="Subject" class="w-full p-2 border rounded" required>
                <textarea name="message" rows="4" placeholder="Message" class="w-full p-2 border rounded" required></textarea>
                <select name="priority" class="w-full p-2 border rounded">
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                    <option value="high">High</option>
                </select>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Create Ticket
                </button>
            </form>
        </section>

        <section class="mb-8">
            <h2 class="text-xl font-semibold mb-3">Your Tickets</h2>
            <div id="ticketsContainer" class="space-y-3">
                <?php foreach ($tickets as $t): ?>
                    <a href="?ticketID=<?= $t['ticketID'] ?>"
                        class="block bg-white p-4 rounded shadow hover:bg-gray-50 flex justify-between items-center"
                        data-ticket-id="<?= $t['ticketID'] ?>">
                        <div>
                            <div class="font-semibold"><?= htmlspecialchars($t['subject']) ?></div>
                            <div class="text-xs text-gray-500"><?= htmlspecialchars($t['priority']) ?> • <?= htmlspecialchars($t['status']) ?></div>
                        </div>
                        <div class="text-sm text-gray-500"><?= htmlspecialchars($t['updatedAt']) ?></div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-3">Messages</h2>
            <div id="messageBox" class="bg-white p-4 rounded shadow h-64 overflow-y-auto mb-4">
                <?php foreach ($messages as $msg): ?>
                    <div class="<?= $msg['senderRole'] === 'admin' ? 'text-red-600' : 'text-blue-600' ?> mb-2">
                        <strong><?= htmlspecialchars($msg['senderRole']) ?>:</strong> <?= htmlspecialchars($msg['message']) ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <form id="replyForm">
                <textarea id="newMessage" rows="3" class="w-full p-2 border rounded mb-2" placeholder="Type your message..." required></textarea>
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Send</button>
            </form>
        </section>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>

</body>

</html>