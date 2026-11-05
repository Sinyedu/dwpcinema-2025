<?php
declare(strict_types=1);

class Showing
{
    public function __construct(private PDO $pdo)
    {
    }

    /** Upcoming showings of visible tournaments, optionally for one tournament, with seat counts. */
    public function upcoming(?int $tournamentID = null): array
    {
        $where = "CONCAT(s.showingDate, ' ', s.showingTime) >= NOW() AND t.isHidden = 0";
        $params = [];
        if ($tournamentID) {
            $where .= ' AND m.tournamentID = ?';
            $params[] = $tournamentID;
        }

        $stmt = $this->pdo->prepare("
            SELECT s.showingID, s.showingDate, s.showingTime, h.hallName, h.totalSeats,
                   m.tournamentID, t.tournamentName, m.matchName,
                   IFNULL((
                       SELECT COUNT(DISTINCT bs.seatID)
                       FROM Booking_Seat bs JOIN Booking b ON bs.bookingID = b.bookingID
                       WHERE b.showingID = s.showingID
                   ), 0) AS bookedSeats
            FROM Showing s
            JOIN Hall h ON s.hallID = h.hallID
            JOIN `Match` m ON s.matchID = m.matchID
            JOIN Tournament t ON m.tournamentID = t.tournamentID
            WHERE $where
            ORDER BY s.showingDate ASC, s.showingTime ASC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
