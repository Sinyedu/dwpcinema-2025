<?php
declare(strict_types=1);

/**
 * Storage for support tickets and messages. No policy here (who may see/do what):
 * that lives in SupportDesk.
 *
 * NOTE: the ticket state column is `status` (as in SQL/createDB.sql). The previous code
 * also queried a `ticketStatus` column; see docs/schema-drift.md.
 */
class SupportModel
{
    public function __construct(private PDO $pdo)
    {
    }

    public function createTicket(int $userID, string $subject, string $priority): int
    {
        $this->pdo->prepare('INSERT INTO SupportTicket (userID, subject, priority) VALUES (?, ?, ?)')
            ->execute([$userID, $subject, $priority]);
        return (int)$this->pdo->lastInsertId();
    }

    /** @return array{ticketID: int, userID: int, status: string}|null */
    public function getTicket(int $ticketID): ?array
    {
        $stmt = $this->pdo->prepare('SELECT ticketID, userID, status FROM SupportTicket WHERE ticketID = ?');
        $stmt->execute([$ticketID]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function addMessage(int $ticketID, int $senderID, string $role, string $message): void
    {
        $this->pdo->prepare('INSERT INTO SupportMessage (ticketID, senderID, senderRole, message) VALUES (?, ?, ?, ?)')
            ->execute([$ticketID, $senderID, $role, $message]);
        $this->pdo->prepare('UPDATE SupportTicket SET updatedAt = NOW() WHERE ticketID = ?')->execute([$ticketID]);
    }

    public function getMessages(int $ticketID): array
    {
        $stmt = $this->pdo->prepare('
            SELECT messageID, senderRole, message, createdAt
            FROM SupportMessage WHERE ticketID = ?
            ORDER BY createdAt ASC, messageID ASC
        ');
        $stmt->execute([$ticketID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Mark the admin's messages on one ticket as read by the ticket's owner. */
    public function markAdminMessagesRead(int $ticketID): void
    {
        $this->pdo->prepare("UPDATE SupportMessage SET isRead = 1 WHERE ticketID = ? AND senderRole = 'admin'")
            ->execute([$ticketID]);
    }

    public function countUnreadAdminMessages(int $userID): int
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*)
            FROM SupportMessage m JOIN SupportTicket t ON m.ticketID = t.ticketID
            WHERE t.userID = ? AND m.senderRole = 'admin' AND m.isRead = 0
        ");
        $stmt->execute([$userID]);
        return (int)$stmt->fetchColumn();
    }

    /** Number of user messages since the last admin message. */
    public function countTrailingUserMessages(int $ticketID): int
    {
        $stmt = $this->pdo->prepare('SELECT senderRole FROM SupportMessage WHERE ticketID = ? ORDER BY createdAt DESC, messageID DESC');
        $stmt->execute([$ticketID]);
        $count = 0;
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $role) {
            if ($role !== 'user') {
                break;
            }
            $count++;
        }
        return $count;
    }

    public function getUserTickets(int $userID): array
    {
        $stmt = $this->pdo->prepare('
            SELECT ticketID, subject, priority, status, updatedAt
            FROM SupportTicket WHERE userID = ?
            ORDER BY updatedAt DESC, ticketID DESC
        ');
        $stmt->execute([$userID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllTickets(): array
    {
        return $this->pdo->query('
            SELECT t.ticketID, t.userID, u.firstName, u.lastName, t.subject, t.status, t.priority, t.updatedAt
            FROM SupportTicket t JOIN User u ON t.userID = u.userID
            ORDER BY t.updatedAt DESC, t.ticketID DESC
        ')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus(int $ticketID, string $status): void
    {
        $this->pdo->prepare('UPDATE SupportTicket SET status = ? WHERE ticketID = ?')->execute([$status, $ticketID]);
    }
}
