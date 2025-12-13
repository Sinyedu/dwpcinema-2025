<?php
require_once __DIR__ . '/../models/Support.php';
require_once __DIR__ . '/SecurityController.php';

class UserSupportController
{
    private SupportModel $model;

    public function __construct(PDO $pdo)
    {
        $this->model = new SupportModel($pdo);
    }

    public function getUserTickets(int $userID): array
    {
        return $this->model->getUserTickets($userID);
    }

    public function getUnreadMessages(int $userID): int
    {
        return $this->model->countUnreadMessages($userID);
    }

    public function getMessages(int $ticketID): array
    {
        return $this->model->getMessages($ticketID);
    }

    public function markMessagesRead(int $ticketID, int $userID): bool
    {
        return $this->model->markMessagesRead($ticketID, $userID);
    }

    public function sendMessage(int $ticketID, int $userID, string $message): bool
    {
        return $this->model->addMessage($ticketID, $userID, 'user', $message) > 0;
    }

    public function createTicket(int $userID, string $subject, string $message, string $priority = 'medium'): int
    {
        $subject = SecurityController::sanitizeInput($subject);
        $message = SecurityController::sanitizeInput($message);

        if (!$subject || !$message) {
            throw new Exception("Subject and message cannot be empty.");
        }

        $ticketID = $this->model->createTicket($userID, $subject, $priority);
        $this->model->addMessage($ticketID, $userID, 'user', $message);

        return $ticketID;
    }
}
