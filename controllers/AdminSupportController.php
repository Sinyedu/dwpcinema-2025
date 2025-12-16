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

    public function getAllTicketsDetailed(): array
    {
        return $this->model->getAllTicketsWithDetails();
    }

    public function getTicketMessages(int $ticketID): array
    {
        return $this->model->getMessages($ticketID);
    }

    public function getTicketStatus(int $ticketID): ?string
    {
        return $this->model->getTicketStatus($ticketID);
    }

    public function replyToTicket(int $ticketID, int $adminID, string $message): bool
    {
        $status = $this->model->getTicketStatus($ticketID);
        if ($status === 'closed') {
            return false;
        }

        $message = SecurityController::sanitizeInput($message);
        return $this->model->addMessage($ticketID, $adminID, 'admin', $message);
    }

    public function closeTicket(int $ticketID): bool
    {
        return $this->model->updateTicketStatus($ticketID, 'closed');
    }
    public function reopenTicket(int $ticketID): bool
    {
        return $this->model->updateTicketStatus($ticketID, 'open');
    }

    public function updateTicketStatus(int $ticketID, string $status): bool
    {
        return $this->model->updateTicketStatus($ticketID, $status);
    }
}
