<?php
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../controllers/SupportController.php';
header('Content-Type: application/json');

session_start();
if (empty($_SESSION['isAdmin'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Admin only']);
    exit;
}

$pdo = Database::getInstance();
$ctrl = new SupportController($pdo);

$ticketID = (int)($_POST['ticketID'] ?? 0);
$status = $_POST['status'] ?? '';

if (!in_array($status, ['open', 'pending', 'resolved', 'closed'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid status']);
    exit;
}

try {
    $ctrl->updateTicketStatus($ticketID, $status);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
