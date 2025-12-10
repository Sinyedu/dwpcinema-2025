<?php
class SupportMessage
{
    private PDO $pdo;
    private string $table = 'SupportMessage';

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function sendMessage(int $ticketID, int $senderID, string $senderRole, string $message): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO {$this->table} (ticketID, senderID, senderRole, message)
            VALUES (:ticketID, :senderID, :senderRole, :message)
        ");
        $stmt->execute([
            ':ticketID' => $ticketID,
            ':senderID' => $senderID,
            ':senderRole' => $senderRole,
            ':message' => $message
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function getMessagesByTicket(int $ticketID, ?string $since = null): array
    {
        if ($since) {
            $stmt = $this->pdo->prepare("
                SELECT messageID, ticketID, senderID, senderRole, message, createdAt, isRead
                FROM {$this->table}
                WHERE ticketID = :ticketID AND createdAt > :since
                ORDER BY createdAt ASC
            ");
            $stmt->execute([':ticketID' => $ticketID, ':since' => $since]);
        } else {
            $stmt = $this->pdo->prepare("
                SELECT messageID, ticketID, senderID, senderRole, message, createdAt, isRead
                FROM {$this->table}
                WHERE ticketID = :ticketID
                ORDER BY createdAt ASC
            ");
            $stmt->execute([':ticketID' => $ticketID]);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function markMessagesRead(int $ticketID, int $forUserID): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE {$this->table}
            SET isRead = 1
            WHERE ticketID = :ticketID AND senderID <> :forUserID
        ");
        return $stmt->execute([':ticketID' => $ticketID, ':forUserID' => $forUserID]);
    }

    public function getUnreadCountForUser(int $userID): int
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(m.messageID) as cnt
            FROM {$this->table} m
            JOIN SupportTicket t ON m.ticketID = t.ticketID
            WHERE t.userID = :userID AND m.isRead = 0 AND m.senderRole = 'admin'
        ");
        $stmt->execute([':userID' => $userID]);
        return (int)$stmt->fetchColumn();
    }
}
