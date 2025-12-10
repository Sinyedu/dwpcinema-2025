<?php
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../controllers/SupportController.php';
header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$pdo = Database::getInstance();
$ctrl = new SupportController($pdo);

$ticketID = (int)($_POST['ticketID'] ?? 0);

try {
    $ticket = $ctrl->getTicket($ticketID);
    if (!$ticket) throw new Exception("Ticket not found.");
    if ($ticket['userID'] != $_SESSION['user_id'] && empty($_SESSION['isAdmin'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }
    $ctrl->markRead($ticketID, (int)$_SESSION['user_id']);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
