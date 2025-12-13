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

    public function addMessage(int $ticketID, int $senderID, string $role, string $message): bool
    {
        $stmt = $this->pdo->prepare("
        INSERT INTO SupportMessage (ticketID, senderID, senderRole, message)
        VALUES (?, ?, ?, ?)
    ");
        $success = $stmt->execute([$ticketID, $senderID, $role, $message]);

        $this->pdo->prepare("UPDATE SupportTicket SET updatedAt = NOW() WHERE ticketID = ?")
            ->execute([$ticketID]);

        return (bool)$success;
    }

    public function countUnreadMessages(int $userID): int
    {
        $stmt = $this->pdo->prepare("
        SELECT COUNT(*) 
        FROM SupportMessage m
        JOIN SupportTicket t ON m.ticketID = t.ticketID
        WHERE t.userID = :userID
          AND m.senderRole = 'admin'
          AND m.isRead = 0
    ");
        $stmt->execute(['userID' => $userID]);
        return (int)$stmt->fetchColumn();
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

    public function markMessagesRead(int $ticketID, int $userID): bool
    {
        $stmt = $this->pdo->prepare("
        UPDATE SupportMessage
        SET isRead = 1
        WHERE ticketID = :ticketID AND senderRole = 'admin'
    ");
        return $stmt->execute(['ticketID' => $ticketID]);
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

    public function getAllTicketsWithDetails(): array
    {
        $stmt = $this->pdo->prepare("
        SELECT t.ticketID, t.userID, u.firstName, u.lastName, t.subject, t.status, t.priority, t.updatedAt,
               g.gameName, s.showingDate, s.showingTime
        FROM SupportTicket t
        JOIN User u ON t.userID = u.userID
        LEFT JOIN Game g ON t.gameID = g.gameID
        LEFT JOIN Showing s ON t.showingID = s.showingID
        ORDER BY t.updatedAt DESC
    ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReservationInfo(int $showingID): array
    {
        $stmt = $this->pdo->prepare("
        SELECT g.gameName, s.showingDate, s.showingTime
        FROM Showing s
        JOIN Game g ON s.gameID = g.gameID
        WHERE s.showingID = ?
    ");
        $stmt->execute([$showingID]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function countConsecutiveUserMessages(int $ticketID, int $userID): int
    {
        $stmt = $this->pdo->prepare("
        SELECT senderRole
        FROM SupportMessage
        WHERE ticketID = :ticketID
        ORDER BY createdAt DESC
    ");
        $stmt->execute(['ticketID' => $ticketID]);
        $messages = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $count = 0;
        foreach ($messages as $role) {
            if ($role === 'user') {
                $count++;
            } else {
                break;
            }
        }
        return $count;
    }
}
