<?php
declare(strict_types=1);

class News
{
    public function __construct(private PDO $pdo)
    {
    }

    public function getAll(): array
    {
        return $this->pdo->query('SELECT * FROM News ORDER BY newsCreatedAt DESC')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLatest(int $limit = 3): array
    {
        $stmt = $this->pdo->prepare('
            SELECT newsID, newsTitle, newsContent, newsAuthor, newsImage, newsCreatedAt
            FROM News ORDER BY newsCreatedAt DESC LIMIT :limit
        ');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getNewsById(int $newsID): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM News WHERE newsID = ?');
        $stmt->execute([$newsID]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /** Text is stored verbatim; escape on output. */
    public function create(array $data): bool
    {
        return $this->pdo->prepare('
            INSERT INTO News (newsTitle, newsContent, newsAuthor, newsImage) VALUES (?, ?, ?, ?)
        ')->execute([$data['newsTitle'], $data['newsContent'], $data['newsAuthor'], $data['newsImage'] ?? '']);
    }

    public function update(int $newsID, array $data): bool
    {
        return $this->pdo->prepare('
            UPDATE News SET newsTitle = ?, newsContent = ?, newsAuthor = ?, newsImage = ? WHERE newsID = ?
        ')->execute([$data['newsTitle'], $data['newsContent'], $data['newsAuthor'], $data['newsImage'] ?? '', $newsID]);
    }

    public function delete(int $newsID): bool
    {
        return $this->pdo->prepare('DELETE FROM News WHERE newsID = ?')->execute([$newsID]);
    }
}
