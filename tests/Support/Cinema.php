<?php
declare(strict_types=1);

/**
 * Test fixture: two halls (A: rows A,B x 10 seats; B: row A x 10), two tiers,
 * and two showings (one per hall) for a single tournament.
 */
final class Cinema
{
    public int $hallA;
    public int $hallB;
    public int $showingA;
    public int $showingA2; // a second showing in hall A
    public int $showingB;
    public int $vipTier;
    public int $standardTier;
    public int $tournament;

    private function __construct(private PDO $pdo)
    {
    }

    public static function build(PDO $pdo): self
    {
        $c = new self($pdo);

        $pdo->exec("INSERT INTO Game (gameName, gameGenre) VALUES ('Dota 2', 'MOBA')");
        $pdo->exec("INSERT INTO Tournament (gameID, tournamentName, startDate, endDate) VALUES (1, 'The Final', '2026-01-01', '2026-01-09')");
        $c->tournament = 1;
        $pdo->exec("INSERT INTO SeatTier (tierName, basePrice) VALUES ('Standard', 10.00), ('VIP', 25.00)");
        $c->standardTier = 1;
        $c->vipTier = 2;

        $c->hallA = $c->insertHall('Hall A', ['A', 'B']);
        $c->hallB = $c->insertHall('Hall B', ['A']);

        $c->showingA = $c->insertShowing($c->hallA);
        $c->showingA2 = $c->insertShowing($c->hallA);
        $c->showingB = $c->insertShowing($c->hallB);

        return $c;
    }

    public function seatId(int $hallID, string $row, int $number): int
    {
        $stmt = $this->pdo->prepare('SELECT seatID FROM Seat WHERE hallID = ? AND seatRow = ? AND seatNumber = ?');
        $stmt->execute([$hallID, $row, $number]);
        return (int)$stmt->fetchColumn();
    }

    /** @return int[] seat ids in hall A, row $row, numbers $from..$to */
    public function hallASeats(string $row, int $from, int $to): array
    {
        return array_map(fn(int $n) => $this->seatId($this->hallA, $row, $n), range($from, $to));
    }

    private function insertHall(string $name, array $rows): int
    {
        $this->pdo->prepare('INSERT INTO Hall (hallName, totalSeats) VALUES (?, ?)')->execute([$name, count($rows) * 10]);
        $hallID = (int)$this->pdo->lastInsertId();
        $seat = $this->pdo->prepare('INSERT INTO Seat (hallID, seatRow, seatNumber, tierID) VALUES (?, ?, ?, ?)');
        foreach ($rows as $row) {
            for ($n = 1; $n <= 10; $n++) {
                // Row A is VIP, everything else Standard.
                $seat->execute([$hallID, $row, $n, $row === 'A' ? $this->vipTier : $this->standardTier]);
            }
        }
        return $hallID;
    }

    private function insertShowing(int $hallID): int
    {
        $this->pdo->prepare("INSERT INTO \"Match\" (tournamentID, gameID, matchName, matchDate, matchTime, hallID) VALUES (1, 1, 'Grand Final', '2026-01-09', '18:00', ?)")
            ->execute([$hallID]);
        $matchID = (int)$this->pdo->lastInsertId();
        $this->pdo->prepare("INSERT INTO Showing (matchID, hallID, showingDate, showingTime) VALUES (?, ?, '2026-01-09', '18:00')")
            ->execute([$matchID, $hallID]);
        return (int)$this->pdo->lastInsertId();
    }
}
