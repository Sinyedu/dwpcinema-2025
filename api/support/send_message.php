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
$message = trim($_POST['message'] ?? '');

try {
    $role = (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin']) ? 'admin' : 'user';
    $msgID = $ctrl->sendMessage($ticketID, (int)$_SESSION['user_id'], $role, $message);
    echo json_encode(['success' => true, 'messageID' => $msgID]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
