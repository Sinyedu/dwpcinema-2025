<?php
require_once __DIR__ . '/../controllers/SecurityController.php';
class News
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM News ORDER BY newsCreatedAt DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): bool
    {
        $title = SecurityController::sanitizeInput($data['newsTitle']);
        $content = SecurityController::sanitizeInput($data['newsContent']);
        $author = SecurityController::sanitizeInput($data['newsAuthor']);
        $image = SecurityController::sanitizeInput($data['newsImage'] ?? '');

        $stmt = $this->pdo->prepare("
            INSERT INTO News (newsTitle, newsContent, newsAuthor, newsImage)
            VALUES (:title, :content, :author, :image)
        ");
        return $stmt->execute([
            ':title' => $title,
            ':content' => $content,
            ':author' => $author,
            ':image' => $image
        ]);
    }

    public function update(int $newsID, array $data): bool
    {
        $title = SecurityController::sanitizeInput($data['newsTitle']);
        $content = SecurityController::sanitizeInput($data['newsContent']);
        $author = SecurityController::sanitizeInput($data['newsAuthor']);
        $image = SecurityController::sanitizeInput($data['newsImage'] ?? '');

        $stmt = $this->pdo->prepare("
            UPDATE News
            SET newsTitle = :title, newsContent = :content, newsAuthor = :author, newsImage = :image
            WHERE newsID = :id
        ");
        return $stmt->execute([
            ':title' => $title,
            ':content' => $content,
            ':author' => $author,
            ':image' => $image,
            ':id' => $newsID
        ]);
    }

    public function getNewsById(int $newsID): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM News WHERE newsID = :id");
        $stmt->execute([':id' => $newsID]);
        $news = $stmt->fetch(PDO::FETCH_ASSOC);
        return $news ?: null;
    }

    public function delete(int $newsID): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM News WHERE newsID = :id");
        return $stmt->execute([':id' => $newsID]);
    }
}
