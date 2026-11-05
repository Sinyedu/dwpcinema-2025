<?php
declare(strict_types=1);

class Tournament
{
    public function __construct(private PDO $pdo)
    {
    }

    /** Everything, including hidden (admin). */
    public function getAll(): array
    {
        return $this->pdo->query('
            SELECT t.*, g.gameName
            FROM Tournament t
            LEFT JOIN Game g ON t.gameID = g.gameID
            ORDER BY t.startDate ASC
        ')->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Public listing: visible tournaments with upcoming-showing info. */
    public function getVisibleWithShowings(): array
    {
        return $this->pdo->query('
            SELECT t.tournamentID, t.tournamentName, t.tournamentDescription, t.startDate, t.endDate,
                   g.gameName, g.gameGenre,
                   (SELECT COUNT(*) FROM `Match` m JOIN Showing s ON m.matchID = s.matchID
                     WHERE m.tournamentID = t.tournamentID AND s.showingDate >= CURDATE()) AS upcomingShowings,
                   (SELECT MIN(s.showingDate) FROM `Match` m JOIN Showing s ON m.matchID = s.matchID
                     WHERE m.tournamentID = t.tournamentID AND s.showingDate >= CURDATE()) AS nextShowing
            FROM Tournament t
            JOIN Game g ON t.gameID = g.gameID
            WHERE t.isHidden = 0
            ORDER BY t.startDate ASC
        ')->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Homepage: most recent visible tournaments. */
    public function getFeatured(int $limit = 3): array
    {
        $stmt = $this->pdo->prepare('
            SELECT t.tournamentID, t.tournamentName, t.tournamentDescription, t.startDate,
                   g.gameName, g.gameGenre,
                   COUNT(s.showingID) AS upcomingShowings,
                   MIN(s.showingDate) AS nextShowing
            FROM Tournament t
            JOIN Game g ON t.gameID = g.gameID
            LEFT JOIN `Match` m ON m.tournamentID = t.tournamentID
            LEFT JOIN Showing s ON s.matchID = m.matchID AND s.showingDate >= CURDATE()
            WHERE t.isHidden = 0
            GROUP BY t.tournamentID, t.tournamentName, t.tournamentDescription, t.startDate, g.gameName, g.gameGenre
            ORDER BY t.startDate DESC
            LIMIT :limit
        ');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** id + name of visible tournaments, for filter dropdowns. */
    public function getVisibleOptions(): array
    {
        return $this->pdo->query('SELECT tournamentID, tournamentName FROM Tournament WHERE isHidden = 0 ORDER BY startDate ASC')
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    /** id + name of all tournaments, for admin/contact dropdowns. */
    public function getOptions(): array
    {
        return $this->pdo->query('SELECT tournamentID, tournamentName FROM Tournament ORDER BY tournamentName ASC')
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getName(int $id): ?string
    {
        $stmt = $this->pdo->prepare('SELECT tournamentName FROM Tournament WHERE tournamentID = ?');
        $stmt->execute([$id]);
        $name = $stmt->fetchColumn();
        return $name === false ? null : (string)$name;
    }

    public function create(array $data): bool
    {
        return $this->pdo->prepare('
            INSERT INTO Tournament (gameID, tournamentName, startDate, endDate, tournamentDescription)
            VALUES (?, ?, ?, ?, ?)
        ')->execute([
            (int)$data['gameID'], $data['tournamentName'], $data['startDate'], $data['endDate'], $data['tournamentDescription'],
        ]);
    }

    public function update(int $id, array $data): bool
    {
        return $this->pdo->prepare('
            UPDATE Tournament
            SET gameID = ?, tournamentName = ?, startDate = ?, endDate = ?, tournamentDescription = ?
            WHERE tournamentID = ?
        ')->execute([
            (int)$data['gameID'], $data['tournamentName'], $data['startDate'], $data['endDate'], $data['tournamentDescription'], $id,
        ]);
    }

    public function setHidden(int $id, bool $hidden): bool
    {
        return $this->pdo->prepare('UPDATE Tournament SET isHidden = ? WHERE tournamentID = ?')
            ->execute([(int)$hidden, $id]);
    }

    public function delete(int $id): bool
    {
        return $this->pdo->prepare('DELETE FROM Tournament WHERE tournamentID = ?')->execute([$id]);
    }

    /** Create a match and its (single) showing together, atomically. */
    public function addMatchWithShowing(array $data): int
    {
        $this->pdo->beginTransaction();
        try {
            $this->pdo->prepare('
                INSERT INTO `Match` (tournamentID, gameID, matchName, matchDate, matchTime, hallID)
                VALUES (?, ?, ?, ?, ?, ?)
            ')->execute([
                (int)$data['tournamentID'], (int)$data['gameID'], $data['matchName'],
                $data['matchDate'], $data['matchTime'], (int)$data['hallID'],
            ]);
            $matchID = (int)$this->pdo->lastInsertId();

            $this->pdo->prepare('INSERT INTO Showing (matchID, hallID, showingDate, showingTime) VALUES (?, ?, ?, ?)')
                ->execute([$matchID, (int)$data['hallID'], $data['matchDate'], $data['matchTime']]);

            $this->pdo->commit();
            return $matchID;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /** All matches (with hall name) grouped by tournament id, for the admin listing. */
    public function getMatchesGrouped(): array
    {
        $rows = $this->pdo->query('
            SELECT m.*, h.hallName
            FROM `Match` m LEFT JOIN Hall h ON m.hallID = h.hallID
            ORDER BY m.matchDate, m.matchTime
        ')->fetchAll(PDO::FETCH_ASSOC);

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['tournamentID']][] = $row;
        }
        return $grouped;
    }

    public function getHalls(): array
    {
        return $this->pdo->query('SELECT * FROM Hall ORDER BY hallName ASC')->fetchAll(PDO::FETCH_ASSOC);
    }
}
