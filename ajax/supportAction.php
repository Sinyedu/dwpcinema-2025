<?php
session_start();
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../controllers/UserSupportController.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$pdo = Database::getInstance();
$ctrl = new UserSupportController($pdo);

try {
    if (isset($_POST['replyTicketID'], $_POST['replyMessage'])) {
        $ticketID = (int)$_POST['replyTicketID'];
        $message  = trim($_POST['replyMessage']);

        if (!$ctrl->canSendMessage($ticketID, $_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Please wait for admin reply.']);
            exit;
        }

        $sent = $ctrl->sendMessage($ticketID, $_SESSION['user_id'], $message);
        echo json_encode(['success' => (bool)$sent]);
        exit;
    }

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

    if (isset($_GET['fetchMessages'], $_GET['ticketID'])) {
        $ticketID = (int)$_GET['ticketID'];
        $ctrl->markMessagesRead($ticketID, $_SESSION['user_id']);
        echo json_encode($ctrl->getMessages($ticketID));
        exit;
    }

    if (isset($_GET['fetchUnreadCount'])) {
        echo json_encode([
            'unreadCount' => $ctrl->getUnreadMessages($_SESSION['user_id'])
        ]);
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'Invalid request']);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
