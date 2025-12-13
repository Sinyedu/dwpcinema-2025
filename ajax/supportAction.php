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
        $message = trim($_POST['replyMessage']);

        if (!$ctrl->canSendMessage($ticketID, $_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Please wait for admin reply.']);
            exit;
        }

        $success = $ctrl->sendMessage($ticketID, $_SESSION['user_id'], $message);
        echo json_encode(['success' => $success]);
        exit;
    }
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}
