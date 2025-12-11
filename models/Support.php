<?php
class SupportModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function createTicket(int $userID, string $subject, string $priority = 'medium'): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO SupportTicket (userID, subject, priority)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$userID, $subject, $priority]);
        return (int)$this->pdo->lastInsertId();
    }

    public function addMessage(int $ticketID, int $senderID, string $role, string $message): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO SupportMessage (ticketID, senderID, senderRole, message)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$ticketID, $senderID, $role, $message]);

        $this->pdo->prepare("UPDATE SupportTicket SET updatedAt = NOW() WHERE ticketID = ?")
            ->execute([$ticketID]);

        return (int)$this->pdo->lastInsertId();
    }

    public function getMessages(int $ticketID): array
    {
        $stmt = $this->pdo->prepare("
            SELECT messageID, senderRole, message, createdAt
            FROM SupportMessage
            WHERE ticketID = ?
            ORDER BY createdAt ASC
        ");
        $stmt->execute([$ticketID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserTickets(int $userID): array
    {
        $stmt = $this->pdo->prepare("
            SELECT ticketID, subject, priority, status, updatedAt
            FROM SupportTicket
            WHERE userID = ?
            ORDER BY updatedAt DESC
        ");
        $stmt->execute([$userID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getAllTickets(): array
    {
        $stmt = $this->pdo->prepare("
            SELECT t.ticketID, t.userID, u.firstName, u.lastName, t.subject, t.status, t.priority, t.updatedAt
            FROM SupportTicket t
            JOIN User u ON t.userID = u.userID
            ORDER BY t.updatedAt DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateTicketStatus(int $ticketID, string $status): bool
    {
        $stmt = $this->pdo->prepare("UPDATE SupportTicket SET status = ? WHERE ticketID = ?");
        return $stmt->execute([$status, $ticketID]);
    }
}
