<?php
require_once "../models/News.php";

class NewsController {
    private $newsModel;

    public function __construct($pdo) {
        $this->newsModel = new News($pdo);
    }

    public function getAllNews() {
        return $this->newsModel->getAll();
    }

    public function createNews($data) {
        return $this->newsModel->create($data);
    }

    public function updateNews($id, $data) {
        return $this->newsModel->update($id, $data);
    }

    public function deleteNews($id) {
        return $this->newsModel->delete($id);
    }
}
?>
