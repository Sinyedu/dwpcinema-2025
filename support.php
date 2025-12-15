<?php
session_start();
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/controllers/UserSupportController.php';
require_once __DIR__ . '/includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf_token'] ?? null)) {
        echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
        exit;
    }
}

$pdo = Database::getInstance();
$ctrl = new UserSupportController($pdo);

if (
    isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
) {
    header('Content-Type: application/json');
    ob_clean();

    try {
        if (isset($_POST['subject'], $_POST['message'])) {
            $ticketID = $ctrl->createTicket(
                $_SESSION['user_id'],
                $_POST['subject'],
                $_POST['message'],
                $_POST['priority'] ?? 'medium'
            );
            echo json_encode(['success' => true, 'ticketID' => $ticketID]);
            exit;
        }

        if (isset($_POST['replyTicketID'], $_POST['replyMessage'])) {
            $ticketID = (int)$_POST['replyTicketID'];
            $message  = trim($_POST['replyMessage']);

            if (!$ctrl->canSendMessage($ticketID, $_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'error' => 'Please wait for admin reply.']);
                exit;
            }

            $sent = $ctrl->sendMessage($ticketID, $_SESSION['user_id'], $message);

            if ($sent) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Message could not be sent.']);
            }
            exit;
        }

        if (isset($_GET['fetchMessages'], $_GET['ticketID'])) {
            $ticketID = (int)$_GET['ticketID'];
            $ctrl->markMessagesRead($ticketID, $_SESSION['user_id']);
            echo json_encode($ctrl->getMessages($ticketID));
            exit;
        }

        if (isset($_GET['fetchUnreadCount'])) {
            echo json_encode(['unreadCount' => $ctrl->getUnreadMessages($_SESSION['user_id'])]);
            exit;
        }

        echo json_encode(['success' => false, 'error' => 'Invalid request']);
        exit;
    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$tickets = $ctrl->getUserTickets($_SESSION['user_id']);

foreach ($tickets as &$t) {
    $t['status'] = $ctrl->getTicketStatus($t['ticketID']);
}
unset($t);

$activeTicketID = isset($_GET['ticketID']) ? (int)$_GET['ticketID'] : ($tickets[0]['ticketID'] ?? 0);
$messages = $activeTicketID ? $ctrl->getMessages($activeTicketID) : [];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Support - DWP Cinema</title>
    <link rel="stylesheet" href="styles/animations.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        window.ticketID = <?= $activeTicketID ?? 'null' ?>;
        window.userID = <?= $_SESSION['user_id'] ?>;
    </script>
    <script src="public/js/supportMessage.js" defer></script>
    <script src="public/js/toast.js" defer></script>
</head>

<body class="bg-neutral-900 text-white min-h-screen flex flex-col">
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <main class="flex-1 max-w-6xl mx-auto w-full p-6 mt-24">
        <h1 class="text-3xl font-bold mb-6">Support Center</h1>

        <section class="mb-8">
            <form id="createTicketForm" class="bg-neutral-800 p-6 rounded shadow space-y-4">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <h2 class="text-xl font-semibold text-white">Create New Ticket</h2>
                <select name="subject" id="subjectSelect" class="w-full p-2 border rounded text-black" required>
                    <option value="" disabled selected class="text-gray-400">Select a subject</option>
                    <option value="General Inquiry">General Inquiry</option>
                    <option value="Technical Issue">Technical Issue</option>
                    <option value="Reservation">Reservation</option>
                    <option value="Billing">Billing</option>
                    <option value="Feedback">Feedback</option>
                </select>

                <textarea name="message" rows="4" placeholder="Message" class="w-full p-2 border rounded text-black" required></textarea>

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
                    <a href="?ticketID=<?= $t['ticketID'] ?>"
                        class="block bg-neutral-800 p-4 rounded shadow hover:bg-gray-700 flex justify-between items-center <?= ($activeTicketID === $t['ticketID']) ? 'border-l-4 border-blue-600' : '' ?>"
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

        <?php if ($activeTicketID): ?>
            <section>
                <h2 class="text-xl text-white font-semibold mb-3">Messages</h2>
                <div id="messageBox" class="bg-neutral-800 p-4 rounded shadow h-64 overflow-y-auto mb-4">
                    <?php foreach ($messages as $msg): ?>
                        <div class="<?= $msg['senderRole'] === 'admin' ? 'text-red-600' : 'text-blue-600' ?> mb-2">
                            <strong><?= htmlspecialchars($msg['senderRole']) ?>:</strong> <?= htmlspecialchars($msg['message']) ?>
                            <span class="text-gray-400 text-xs block"><?= $msg['createdAt'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <form id="replyForm">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <textarea id="newMessage" rows="3" class="w-full bg-neutral-800 p-2 rounded mb-2 text-white" placeholder="Type your message..." required></textarea>
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Send</button>
                </form>
            </section>
        <?php endif; ?>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>

</html>