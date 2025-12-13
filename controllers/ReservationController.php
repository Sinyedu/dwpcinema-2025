<?php
require_once __DIR__ . '/../classes/Database.php';

class ReservationController
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getGames()
    {
        $stmt = $this->pdo->query("SELECT gameID, gameName FROM Game ORDER BY gameName");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getShowings(int $gameID)
    {
        $stmt = $this->pdo->prepare("
            SELECT s.showingID, CONCAT(m.matchName, ' (', s.showingDate, ' ', s.showingTime, ')') AS showingName
            FROM Showing s
            JOIN `Match` m ON s.matchID = m.matchID
            WHERE m.gameID = ?
            ORDER BY s.showingDate, s.showingTime
        ");
        $stmt->execute([$gameID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
