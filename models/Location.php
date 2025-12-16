<?php
class Location
{
    private PDO $pdo;
    private string $table = 'Location';

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM {$this->table} ORDER BY locationName ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE locationID = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function create(array $data): bool
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO {$this->table} (locationName, address, city, postcode, country)
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['locationName'],
            $data['address'],
            $data['city'],
            $data['postcode'] ?? null,
            $data['country'] ?? 'Denmark'
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE {$this->table} 
            SET locationName=?, address=?, city=?, postcode=?, country=? 
            WHERE locationID=?
        ");
        return $stmt->execute([
            $data['locationName'],
            $data['address'],
            $data['city'],
            $data['postcode'] ?? null,
            $data['country'] ?? 'Denmark',
            $id
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE locationID=?");
        return $stmt->execute([$id]);
    }
}
