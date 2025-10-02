<?php
class Booking {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function create($userID, $showingID) {
        $stmt = $this->pdo->prepare("INSERT INTO Booking (userID, showingID, bookingDate) VALUES (?, ?, NOW())");
        return $stmt->execute([$userID, $showingID]);
    }

    public function getByUser($userID) {
        $stmt = $this->pdo->prepare("
            SELECT b.bookingID, b.showingID, s.showingDate, s.showingTime, h.hallName, t.tournamentName
            FROM Booking b
            JOIN Showing s ON b.showingID = s.showingID
            JOIN `Match` m ON s.matchID = m.matchID
            JOIN Tournament t ON m.tournamentID = t.tournamentID
            JOIN Hall h ON s.hallID = h.hallID
            WHERE b.userID = ?
            ORDER BY s.showingDate, s.showingTime
        ");
        $stmt->execute([$userID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
