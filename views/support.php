<?php
session_start();
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../controllers/AdminSupportController.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit;
}

$pdo = Database::getInstance();
$adminSupport = new AdminSupportController($pdo);
$tickets = $adminSupport->getAllTickets();

// Ensure ticket status is up-to-date from DB
foreach ($tickets as &$t) {
    $t['status'] = $adminSupport->getTicketStatus($t['ticketID']);
}
unset($t);

if (
    isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
) {
    header('Content-Type: application/json');

    $ticketID = (int)($_POST['ticketID'] ?? 0);
    $action = $_POST['action'] ?? null;
    $message = trim($_POST['message'] ?? '');

    try {
        if ($action === 'close' && $ticketID) {
            $success = $adminSupport->updateTicketStatus($ticketID, 'closed');
            echo json_encode(['success' => $success, 'status' => 'closed']);
            exit;
        }

        if ($action === 'reopen' && $ticketID) {
            $success = $adminSupport->updateTicketStatus($ticketID, 'open');
            echo json_encode(['success' => $success, 'status' => 'open']);
            exit;
        }

        if ($message && $ticketID) {
            $success = $adminSupport->replyToTicket($ticketID, $_SESSION['admin_id'], $message);
            echo json_encode(['success' => $success]);
            exit;
        }

        echo json_encode(['success' => false, 'error' => 'Invalid request']);
        exit;
    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

$activeTicketID = isset($_GET['ticketID']) ? (int)$_GET['ticketID'] : ($tickets[0]['ticketID'] ?? 0);
$activeTicket = null;
foreach ($tickets as $t) {
    if ($t['ticketID'] === $activeTicketID) {
        $activeTicket = $t;
        break;
    }
}

$messages = $activeTicketID ? $adminSupport->getTicketMessages($activeTicketID) : [];

usort($tickets, function ($a, $b) {
    $order = ['high' => 1, 'medium' => 2, 'low' => 3];
    return ($order[$a['priority']] ?? 4) - ($order[$b['priority']] ?? 4);
});
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Support - DWP Cinema</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-neutral-800 flex min-h-screen">
    <?php include __DIR__ . '/../includes/adminSidebar.php'; ?>

    <div class="flex-1 ml-64 p-8">
        <div id="toastContainer" class="fixed top-5 right-5 space-y-2 z-50"></div>
        <header class="flex justify-between items-center mb-8 border-b pb-4">
            <h1 class="text-2xl text-white font-semibold">Support Tickets</h1>
            <a href="logout.php" class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-500 text-sm">Logout</a>
        </header>

        <div class="grid md:grid-cols-3 gap-6">
            <div class="bg-neutral-800 rounded shadow p-6 h-[600px] overflow-y-auto">
                <h2 class="font-semibold text-lg mb-3 text-white">Tickets</h2>
                <?php if (count($tickets) === 0): ?>
                    <p class="text-gray-400 text-sm">No tickets found.</p>
                <?php else: ?>
                    <ul class="space-y-2">
                        <?php foreach ($tickets as $t):
                            $priorityColor = match ($t['priority']) {
                                'high' => 'bg-red-500',
                                'medium' => 'bg-yellow-500',
                                'low' => 'bg-green-500',
                                default => 'bg-gray-300'
                            };
                        ?>
                            <li>
                                <a href="?ticketID=<?= $t['ticketID'] ?>"
                                    class="flex justify-between items-center p-2 rounded hover:bg-gray-100 <?= $t['ticketID'] === $activeTicketID ? 'bg-gray-200' : '' ?>">
                                    <div>
                                        <div class="font-medium"><?= htmlspecialchars($t['subject']) ?></div>
                                        <div class="text-xs text-gray-500">
                                            <?= htmlspecialchars($t['firstName'] . ' ' . $t['lastName']) ?> • <?= htmlspecialchars($t['status']) ?>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="px-2 py-1 text-xs text-white rounded <?= $priorityColor ?>">
                                            <?= ucfirst($t['priority']) ?>
                                        </span>
                                        <span class="text-sm text-gray-500"><?= htmlspecialchars($t['updatedAt']) ?></span>
                                    </div>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <div class="col-span-2 bg-white rounded shadow p-6 flex flex-col h-[600px]">
                <h2 class="font-semibold text-lg mb-3">Messages</h2>
                <div id="messageBox"
                    class="flex-1 overflow-y-auto mb-3"
                    data-ticket-status="<?= htmlspecialchars($activeTicket['status'] ?? 'open') ?>">
                    <?php foreach ($messages as $msg): ?>
                        <div class="mb-2 <?= $msg['senderRole'] === 'admin' ? 'text-red-600' : 'text-blue-600' ?>">
                            <strong><?= htmlspecialchars($msg['senderRole']) ?>:</strong>
                            <?= htmlspecialchars($msg['message']) ?>
                            <span class="text-gray-400 text-xs block"><?= $msg['createdAt'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($activeTicketID): ?>
                    <div class="mb-3">
                        <form id="replyForm">
                            <input type="hidden" name="ticketID" value="<?= $activeTicketID ?>">
                            <textarea name="message" rows="3" class="w-full p-2 border rounded mb-2" placeholder="Type your message..." required></textarea>
                            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                                Send Reply
                            </button>
                        </form>

                        <div class="flex space-x-2 mt-2">
                            <button type="button" id="closeTicketBtn" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">
                                Close Ticket
                            </button>
                            <button type="button" id="reopenTicketBtn" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
                                Reopen Ticket
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="../public/js/adminSupport.js" defer></script>
</body>

</html>