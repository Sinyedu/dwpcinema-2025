<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();

$desk = new SupportDesk(db());

// ---- JSON endpoints used by public/js/adminSupport.js ----
if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest') {
    if (!is_post()) {
        json_response(['success' => false, 'error' => 'Invalid request'], 400);
    }
    require_csrf();

    $ticketID = (int)($_POST['ticketID'] ?? 0);
    $action = $_POST['action'] ?? null;

    try {
        if (in_array($action, ['close', 'reopen'], true) && $ticketID) {
            $status = $action === 'close' ? 'closed' : 'open';
            $desk->setStatus($ticketID, $status);
            json_response(['success' => true, 'status' => $status]);
        }
        if (isset($_POST['message']) && $ticketID) {
            $desk->replyAsAdmin($ticketID, $auth->adminId(), (string)$_POST['message']);
            json_response(['success' => true]);
        }
        json_response(['success' => false, 'error' => 'Invalid request'], 400);
    } catch (SupportException $e) {
        json_response(['success' => false, 'error' => $e->getMessage()], 422);
    }
}

// ---- Page ----
$tickets = $desk->allTickets();
$activeTicketID = filter_input(INPUT_GET, 'ticketID', FILTER_VALIDATE_INT) ?: ($tickets[0]['ticketID'] ?? 0);

$activeTicket = null;
foreach ($tickets as $t) {
    if ((int)$t['ticketID'] === (int)$activeTicketID) {
        $activeTicket = $t;
        break;
    }
}
$messages = $activeTicket ? $desk->messagesForAdmin((int)$activeTicketID) : [];

// Highest priority first (stable for equal priorities).
$order = ['high' => 1, 'medium' => 2, 'low' => 3];
usort($tickets, fn($a, $b) => ($order[$a['priority']] ?? 4) <=> ($order[$b['priority']] ?? 4));

$pageTitle = 'Admin Support - DWP Cinema';
include __DIR__ . '/../includes/head.php';
?>

<body class="bg-neutral-900 text-white flex min-h-screen">
    <?php include __DIR__ . '/../includes/adminSidebar.php'; ?>

    <div class="flex-1 ml-64 p-8">
        <div id="toastContainer" class="fixed top-5 right-5 space-y-2 z-50"></div>

        <header class="flex justify-between items-center mb-8 pb-4 border-b border-neutral-700">
            <h1 class="text-2xl font-semibold">Support Tickets</h1>
            <?= logout_button('Logout', 'px-4 py-2 rounded bg-red-600 hover:bg-red-500 text-white text-sm') ?>
        </header>

        <div class="grid md:grid-cols-3 gap-6">
            <div class="bg-neutral-800 rounded shadow p-6 h-[600px] overflow-y-auto">
                <h2 class="font-semibold text-lg mb-3">Tickets</h2>
                <?php if (count($tickets) === 0): ?>
                    <p class="text-gray-400 text-sm">No tickets found.</p>
                <?php else: ?>
                    <ul class="space-y-2">
                        <?php foreach ($tickets as $t):
                            $priorityColor = match ($t['priority']) {
                                'high' => 'bg-red-500',
                                'medium' => 'bg-yellow-500',
                                'low' => 'bg-green-500',
                                default => 'bg-gray-400'
                            };
                        ?>
                            <li>
                                <a href="?ticketID=<?= (int)$t['ticketID'] ?>"
                                    class="flex justify-between items-center p-2 rounded hover:bg-neutral-700 <?= (int)$t['ticketID'] === (int)$activeTicketID ? 'bg-neutral-700' : '' ?>">
                                    <div>
                                        <div class="font-medium"><?= e($t['subject']) ?></div>
                                        <div class="text-xs text-gray-400">
                                            <?= e($t['firstName'] . ' ' . $t['lastName']) ?> • <?= e($t['status']) ?>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="px-2 py-1 text-xs text-white rounded <?= $priorityColor ?>">
                                            <?= e(ucfirst($t['priority'])) ?>
                                        </span>
                                        <span class="text-xs text-gray-400"><?= e($t['updatedAt']) ?></span>
                                    </div>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <div class="col-span-2 bg-neutral-800 rounded shadow p-6 flex flex-col h-[600px]">
                <h2 class="font-semibold text-lg mb-3">Messages</h2>
                <div id="messageBox" class="flex-1 overflow-y-auto mb-3 px-3 py-2 bg-neutral-900 rounded border border-neutral-700"
                    data-ticket-status="<?= e($activeTicket['status'] ?? 'open') ?>">
                    <?php foreach ($messages as $msg): ?>
                        <div class="mb-2 <?= $msg['senderRole'] === 'admin' ? 'text-red-400' : 'text-blue-400' ?>">
                            <strong><?= e(ucfirst($msg['senderRole'])) ?>:</strong>
                            <?= e($msg['message']) ?>
                            <span class="text-gray-500 text-xs block"><?= e($msg['createdAt']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($activeTicket): ?>
                    <form id="replyForm" class="mb-3">
                        <?= $csrf->field() ?>
                        <input type="hidden" name="ticketID" value="<?= (int)$activeTicketID ?>">
                        <textarea name="message" rows="3" maxlength="5000" class="w-full p-2 border rounded mb-2 bg-neutral-900 text-white border-neutral-700" placeholder="Type your message..." required></textarea>
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                            Send Reply
                        </button>
                    </form>

                    <div class="flex space-x-2">
                        <button type="button" id="closeTicketBtn" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">
                            Close Ticket
                        </button>
                        <button type="button" id="reopenTicketBtn" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
                            Reopen Ticket
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
        window.csrfToken = <?= json_encode($csrf->token()) ?>;
    </script>
    <script src="../public/js/adminSupport.js" defer></script>
</body>

</html>
