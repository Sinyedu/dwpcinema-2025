<?php
class AboutUs
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM AboutUs ORDER BY aboutID DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM AboutUs WHERE aboutID = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create(array $data): bool
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO AboutUs (aboutTitle, aboutContent, aboutFooter, lastUpdated)
            VALUES (:title, :content, :footer, NOW())
        ");
        return $stmt->execute([
            ':title' => $data['aboutTitle'],
            ':content' => $data['aboutContent'],
            ':footer' => $data['aboutFooter'] ?? ''
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE AboutUs
            SET aboutTitle = :title, aboutContent = :content, aboutFooter = :footer, lastUpdated = NOW()
            WHERE aboutID = :id
        ");
        return $stmt->execute([
            ':title' => $data['aboutTitle'],
            ':content' => $data['aboutContent'],
            ':footer' => $data['aboutFooter'] ?? '',
            ':id' => $id
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM AboutUs WHERE aboutID = :id");
        return $stmt->execute([':id' => $id]);
    }
}
