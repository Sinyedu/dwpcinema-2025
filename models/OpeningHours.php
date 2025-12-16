<?php
class OpeningHours
{
    private PDO $pdo;
    private string $table = 'OpeningHours';

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query("
            SELECT dayOfWeek, openTime, closeTime, isClosed
            FROM {$this->table}
            ORDER BY FIELD(dayOfWeek, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateDay(string $dayOfWeek, string $openTime, string $closeTime, bool $isClosed): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE {$this->table}
            SET openTime = :openTime,
                closeTime = :closeTime,
                isClosed = :isClosed
            WHERE dayOfWeek = :dayOfWeek
        ");
        return $stmt->execute([
            ':openTime' => $openTime,
            ':closeTime' => $closeTime,
            ':isClosed' => $isClosed ? 1 : 0,
            ':dayOfWeek' => $dayOfWeek
        ]);
    }
}
