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

$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');
$priority = in_array($_POST['priority'] ?? 'medium', ['low', 'medium', 'high']) ? $_POST['priority'] : 'medium';

try {
    $ticketID = $ctrl->createTicket((int)$_SESSION['user_id'], $subject, $message, $priority);
    echo json_encode(['success' => true, 'ticketID' => $ticketID]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
