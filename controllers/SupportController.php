<?php
require_once __DIR__ . '/../models/SupportTicket.php';
require_once __DIR__ . '/../models/SupportMessage.php';

class SupportController
{
    private PDO $pdo;
    private SupportTicket $ticketModel;
    private SupportMessage $messageModel;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->ticketModel = new SupportTicket($pdo);
        $this->messageModel = new SupportMessage($pdo);
    }

    public function createTicket(int $userID, string $subject, string $initialMessage, string $priority = 'medium')
    {
        $subject = trim($subject);
        $initialMessage = trim($initialMessage);
        if ($subject === '' || $initialMessage === '') {
            throw new Exception("Subject and message required.");
        }
        $ticketID = $this->ticketModel->createTicket($userID, $subject, $priority);
        $this->messageModel->sendMessage($ticketID, $userID, 'user', $initialMessage);
        return $ticketID;
    }

    public function sendMessage(int $ticketID, int $senderID, string $senderRole, string $message)
    {
        $message = trim($message);
        if ($message === '') throw new Exception("Empty message.");

        $ticket = $this->ticketModel->getTicketById($ticketID);

        if (!$ticket) throw new Exception("Ticket not found.");

        $msgID = $this->messageModel->sendMessage($ticketID, $senderID, $senderRole, $message);

        $this->ticketModel->updateStatus($ticketID, $ticket['status']);

        return $msgID;
    }

    public function getMessages(int $ticketID, ?string $since = null)
    {
        return $this->messageModel->getMessagesByTicket($ticketID, $since);
    }

    public function markRead(int $ticketID, int $forUserID)
    {
        return $this->messageModel->markMessagesRead($ticketID, $forUserID);
    }

    public function getUserTickets(int $userID)
    {
        return $this->ticketModel->getUserTickets($userID);
    }

    public function getTicket(int $ticketID)
    {
        return $this->ticketModel->getTicketById($ticketID);
    }

    public function updateTicketStatus(int $ticketID, string $status)
    {
        return $this->ticketModel->updateStatus($ticketID, $status);
    }

    public function getAllTickets()
    {
        return $this->ticketModel->getAllTickets();
    }
}
