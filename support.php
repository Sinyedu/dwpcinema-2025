<?php
require_once __DIR__ . '/includes/bootstrap.php';

$isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';

if (!$auth->isUser()) {
    $isAjax ? json_response(['success' => false, 'error' => 'Please log in.'], 401) : require_user();
}

$desk = new SupportDesk(db());
$userID = $auth->userId();

// ---- JSON endpoints used by public/js/supportMessage.js and notification.js ----
if ($isAjax) {
    try {
        if (is_post()) {
            require_csrf();

            if (isset($_POST['subject'], $_POST['message'])) {
                $ticketID = $desk->createTicket($userID, (string)$_POST['subject'], (string)$_POST['message'], (string)($_POST['priority'] ?? 'medium'));
                json_response(['success' => true, 'ticketID' => $ticketID]);
            }

            if (isset($_POST['replyTicketID'], $_POST['replyMessage'])) {
                $desk->replyAsUser((int)$_POST['replyTicketID'], $userID, (string)$_POST['replyMessage']);
                json_response(['success' => true]);
            }
        } else {
            if (isset($_GET['fetchMessages'], $_GET['ticketID'])) {
                json_response($desk->messagesForUser((int)$_GET['ticketID'], $userID));
            }
            if (isset($_GET['fetchUnreadCount'])) {
                json_response(['unreadCount' => $desk->unreadCountForUser($userID)]);
            }
        }
        json_response(['success' => false, 'error' => 'Invalid request'], 400);
    } catch (SupportException $e) {
        json_response(['success' => false, 'error' => $e->getMessage()], 422);
    }
}

// ---- Page ----
$tickets = $desk->ticketsForUser($userID);
$activeTicketID = filter_input(INPUT_GET, 'ticketID', FILTER_VALIDATE_INT) ?: ($tickets[0]['ticketID'] ?? 0);

$messages = [];
if ($activeTicketID) {
    try {
        $messages = $desk->messagesForUser((int)$activeTicketID, $userID);
    } catch (SupportException) {
        redirect('support.php'); // not your ticket (or doesn't exist)
    }
}

$pageTitle = 'Support - DWP Cinema';
include __DIR__ . '/includes/head.php';
?>
<script>
    window.ticketID = <?= json_encode((int)$activeTicketID ?: null) ?>;
</script>
<link rel="stylesheet" href="styles/animations.css">
<script src="public/js/toast.js" defer></script>
<script src="public/js/supportMessage.js" defer></script>

<body class="bg-neutral-900 text-white min-h-screen flex flex-col">
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <main class="flex-1 max-w-6xl mx-auto w-full p-6 mt-24">
        <h1 class="text-3xl font-bold mb-6">Support Center</h1>

        <section class="mb-8">
            <form id="createTicketForm" class="bg-neutral-800 p-6 rounded shadow space-y-4">
                <?= $csrf->field() ?>
                <h2 class="text-xl font-semibold text-white">Create New Ticket</h2>
                <select name="subject" id="subjectSelect" class="w-full p-2 border rounded text-black" required>
                    <option value="" disabled selected>Select a subject</option>
                    <?php foreach (['General Inquiry', 'Technical Issue', 'Reservation', 'Billing', 'Feedback'] as $subject): ?>
                        <option value="<?= e($subject) ?>"><?= e($subject) ?></option>
                    <?php endforeach; ?>
                </select>

                <textarea name="message" rows="4" maxlength="5000" placeholder="Message" class="w-full p-2 border rounded text-black" required></textarea>

                <select name="priority" class="w-full p-2 border rounded text-black">
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
                    <a href="?ticketID=<?= (int)$t['ticketID'] ?>"
                        class="bg-neutral-800 p-4 rounded shadow hover:bg-gray-700 flex justify-between items-center <?= ((int)$activeTicketID === (int)$t['ticketID']) ? 'border-l-4 border-blue-600' : '' ?>"
                        data-ticket-id="<?= (int)$t['ticketID'] ?>"
                        data-ticket-status="<?= e($t['status']) ?>">
                        <div>
                            <div class="font-semibold"><?= e($t['subject']) ?></div>
                            <div class="text-xs text-gray-500"><?= e($t['priority']) ?> • <?= e($t['status']) ?></div>
                        </div>
                        <div class="text-sm text-gray-500"><?= e($t['updatedAt']) ?></div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <?php if ($activeTicketID): ?>
            <section>
                <h2 class="text-xl text-white font-semibold mb-3">Messages</h2>
                <div id="messageBox" class="bg-neutral-800 p-4 rounded shadow h-64 overflow-y-auto mb-4">
                    <?php foreach ($messages as $msg): ?>
                        <div class="<?= $msg['senderRole'] === 'admin' ? 'text-red-600' : 'text-blue-600' ?> mb-2">
                            <strong><?= e($msg['senderRole']) ?>:</strong> <?= e($msg['message']) ?>
                            <span class="text-gray-400 text-xs block"><?= e($msg['createdAt']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <form id="replyForm">
                    <?= $csrf->field() ?>
                    <textarea id="newMessage" rows="3" maxlength="5000" class="w-full bg-neutral-800 p-2 rounded mb-2 text-white" placeholder="Type your message..." required></textarea>
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Send</button>
                </form>
            </section>
        <?php endif; ?>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>

</html>
