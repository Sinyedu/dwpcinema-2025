<?php
require_once __DIR__ . '/../controllers/SecurityController.php';
class SupportTicket
{
    private PDO $pdo;
    private string $table = 'SupportTicket';

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function createTicket(int $userID, string $subject, string $priority = 'medium'): int
    {
        $subject = SecurityController::sanitizeInput($subject);

        $stmt = $this->pdo->prepare("
            INSERT INTO {$this->table} (userID, subject, priority)
            VALUES (:userID, :subject, :priority)
        ");
        $stmt->execute([
            ':userID' => $userID,
            ':subject' => $subject,
            ':priority' => $priority
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function getUserTickets(int $userID): array
    {
        $stmt = $this->pdo->prepare("
            SELECT ticketID, subject, status, priority, createdAt, updatedAt
            FROM {$this->table}
            WHERE userID = :userID
            ORDER BY updatedAt DESC
        ");
        $stmt->execute([':userID' => $userID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTicketById(int $ticketID): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT ticketID, userID, subject, status, priority, createdAt, updatedAt
            FROM {$this->table}
            WHERE ticketID = :ticketID
            LIMIT 1
        ");
        $stmt->execute([':ticketID' => $ticketID]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function updateStatus(int $ticketID, string $status): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE {$this->table}
            SET status = :status, updatedAt = NOW()
            WHERE ticketID = :ticketID
        ");
        return $stmt->execute([':status' => $status, ':ticketID' => $ticketID]);
    }

    public function getAllTickets(): array
    {
        $stmt = $this->pdo->query("
            SELECT t.ticketID, t.userID, t.subject, t.status, t.priority, t.updatedAt, u.firstName, u.lastName, u.userEmail
            FROM {$this->table} t
            LEFT JOIN `User` u ON t.userID = u.userID
            ORDER BY t.updatedAt DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
