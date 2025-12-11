<?php
require_once __DIR__ . '/../models/Support.php';
require_once __DIR__ . '/SecurityController.php';

class AdminSupportController
{
    private SupportModel $model;

    public function __construct(PDO $pdo)
    {
        $this->model = new SupportModel($pdo);
    }

    public function getAllTickets(): array
    {
        return $this->model->getAllTickets();
    }

    public function getTicketMessages(int $ticketID): array
    {
        return $this->model->getMessages($ticketID);
    }

    public function replyToTicket(int $ticketID, int $adminID, string $message): int
    {
        return $this->model->addMessage($ticketID, $adminID, 'admin', $message);
    }

    public function updateTicketStatus(int $ticketID, string $status): bool
    {
        return $this->model->updateTicketStatus($ticketID, $status);
    }
}
