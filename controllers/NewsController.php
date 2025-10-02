<?php
require_once "../models/News.php";

class NewsController {
    private News $newsModel;

    public function __construct(PDO $pdo) {
        $this->newsModel = new News($pdo);
    }

    public function getAllNews(): array {
        return $this->newsModel->getAll();
    }

    public function getNewsById(int $newsID): ?array {
        return $this->newsModel->getNewsById($newsID);
    }

    public function createNews(array $data): bool {
        return $this->newsModel->create($data);
    }

    public function updateNews(int $newsID, array $data): bool {
        return $this->newsModel->update($newsID, $data);
    }

    public function deleteNews(int $newsID): bool {
        return $this->newsModel->delete($newsID);
    }
}
