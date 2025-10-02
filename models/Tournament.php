<?php
class Tournament {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM Tournament ORDER BY startDate ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("INSERT INTO Tournament (gameID, tournamentName, startDate, endDate, tournamentDescription) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$data['gameID'], $data['tournamentName'], $data['startDate'], $data['endDate'], $data['tournamentDescription']]);
    }

    public function update($id, $data) {
        $stmt = $this->pdo->prepare("UPDATE Tournament SET gameID=?, tournamentName=?, startDate=?, endDate=?, tournamentDescription=? WHERE tournamentID=?");
        return $stmt->execute([$data['gameID'], $data['tournamentName'], $data['startDate'], $data['endDate'], $data['tournamentDescription'], $id]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM Tournament WHERE tournamentID=?");
        return $stmt->execute([$id]);
    }
}
?>
