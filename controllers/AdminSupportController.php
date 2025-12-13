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

    public function getReservationInfo(int $showingID): array
    {
        return $this->model->getReservationInfo($showingID);
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

    public function getAllTicketsDetailed(): array
    {
        return $this->model->getAllTicketsWithDetails();
    }

    public function getAllTicketsWithReservation(): array
    {
        $tickets = $this->model->getAllTickets();

        foreach ($tickets as &$t) {
            if ($t['subject'] === 'Reservation' && !empty($t['showingID'])) {
                $res = $this->model->getReservationInfo($t['showingID']);
                $t['gameName'] = $res['gameName'] ?? '-';
                $t['showingDate'] = $res['showingDate'] ?? '-';
                $t['showingTime'] = $res['showingTime'] ?? '-';
            }
        }

        return $tickets;
    }
}
