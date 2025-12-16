<?php
require_once __DIR__ . '/../controllers/SecurityController.php';
class Tournament
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query("
            SELECT t.*, g.gameName
            FROM Tournament t
            JOIN Game g ON t.gameID = g.gameID
            ORDER BY t.startDate ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): bool
    {
        $name = SecurityController::sanitizeInput($data['tournamentName']);
        $desc = SecurityController::sanitizeInput($data['tournamentDescription']);
        return $this->pdo->prepare("
            INSERT INTO Tournament (gameID, tournamentName, startDate, endDate, tournamentDescription) 
            VALUES (?, ?, ?, ?, ?)
        ")->execute([
            $data['gameID'],
            $name,
            $data['startDate'],
            $data['endDate'],
            $desc
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $name = SecurityController::sanitizeInput($data['tournamentName']);
        $desc = SecurityController::sanitizeInput($data['tournamentDescription']);
        return $this->pdo->prepare("
            UPDATE Tournament 
            SET gameID=?, tournamentName=?, startDate=?, endDate=?, tournamentDescription=? 
            WHERE tournamentID=?
        ")->execute([
            $data['gameID'],
            $name,
            $data['startDate'],
            $data['endDate'],
            $desc,
            $id
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM Tournament WHERE tournamentID=?");
        return $stmt->execute([$id]);
    }
}
