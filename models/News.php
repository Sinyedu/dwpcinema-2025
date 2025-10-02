<?php
class News {
    private $pdo;
    private $table = "News";

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM {$this->table} ORDER BY newsCreatedAt DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO {$this->table} (newsTitle, newsContent, newsAuthor, newsImage) VALUES (?, ?, ?, ?)"
        );
        return $stmt->execute([
            $data['newsTitle'],
            $data['newsContent'],
            $data['newsAuthor'],
            $data['newsImage'] ?? null
        ]);
    }

    public function update($id, $data) {
        $stmt = $this->pdo->prepare(
            "UPDATE {$this->table} SET newsTitle = ?, newsContent = ?, newsAuthor = ?, newsImage = ? WHERE newsID = ?"
        );
        return $stmt->execute([
            $data['newsTitle'],
            $data['newsContent'],
            $data['newsAuthor'],
            $data['newsImage'] ?? null,
            $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE newsID = ?");
        return $stmt->execute([$id]);
    }
}
?>
