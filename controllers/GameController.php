<?php
class GameController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAllGames() {
        $stmt = $this->pdo->query("SELECT * FROM Game ORDER BY gameID DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getGameById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM Game WHERE gameID = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createGame($data) {
        $stmt = $this->pdo->prepare("INSERT INTO Game (gameName, gameGenre) VALUES (?, ?)");
        return $stmt->execute([$data['gameName'], $data['gameGenre']]);
    }

    public function updateGame($id, $data) {
        $stmt = $this->pdo->prepare("UPDATE Game SET gameName = ?, gameGenre = ? WHERE gameID = ?");
        return $stmt->execute([$data['gameName'], $data['gameGenre'], $id]);
    }

    public function deleteGame($id) {
        $stmt = $this->pdo->prepare("DELETE FROM Game WHERE gameID = ?");
        return $stmt->execute([$id]);
    }
}
?>
