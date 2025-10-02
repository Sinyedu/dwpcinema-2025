<?php
class Showing {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll($tournamentID = null) {
        $sql = "
            SELECT 
                s.showingID,
                s.showingDate,
                s.showingTime,
                h.hallName,
                h.totalSeats,
                m.tournamentID,
                t.tournamentName,
                (SELECT COUNT(*) FROM Booking b WHERE b.showingID = s.showingID) AS bookedSeats
            FROM Showing s
            JOIN Hall h ON s.hallID = h.hallID
            JOIN `Match` m ON s.matchID = m.matchID
            JOIN Tournament t ON m.tournamentID = t.tournamentID
        ";

        $params = [];
        if ($tournamentID) {
            $sql .= " WHERE m.tournamentID = ?";
            $params[] = $tournamentID;
        }

        $sql .= " ORDER BY s.showingDate, s.showingTime";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($showingID) {
        $stmt = $this->pdo->prepare("
            SELECT s.showingID, s.showingDate, s.showingTime, h.hallName, h.totalSeats, m.tournamentID, t.tournamentName
            FROM Showing s
            JOIN Hall h ON s.hallID = h.hallID
            JOIN `Match` m ON s.matchID = m.matchID
            JOIN Tournament t ON m.tournamentID = t.tournamentID
            WHERE s.showingID = ?
        ");
        $stmt->execute([$showingID]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
