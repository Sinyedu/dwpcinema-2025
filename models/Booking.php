<?php
declare(strict_types=1);

class Booking
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Book seats for a showing, atomically.
     *
     * The showing row is locked for the duration of the transaction so two people
     * can't both pass the availability check for the same seat. The booking total is
     * maintained by the trg_bookingseat_after_insert database trigger.
     *
     * @param int[] $seatIDs
     * @throws BookingException with a user-safe message when the booking is refused
     */
    public function create(int $userID, int $showingID, array $seatIDs): int
    {
        $seatIDs = array_values(array_unique(array_map('intval', $seatIDs)));
        if ($seatIDs === []) {
            throw new BookingException('No seats selected.');
        }

        $this->pdo->beginTransaction();
        try {
            $hallID = $this->lockShowing($showingID);
            $seats = $this->seatsInHall($hallID, $seatIDs);
            if (count($seats) !== count($seatIDs)) {
                throw new BookingException('One or more seats are not part of this showing.');
            }
            if ($message = SeatRules::violation($seats)) {
                throw new BookingException($message);
            }
            $taken = $this->alreadyBooked($showingID, $seatIDs);
            if ($taken !== []) {
                throw new BookingException('Some seats are already booked: ' . implode(', ', $taken));
            }

            $this->pdo->prepare('INSERT INTO Booking (userID, showingID) VALUES (?, ?)')
                ->execute([$userID, $showingID]);
            $bookingID = (int)$this->pdo->lastInsertId();

            $seatStmt = $this->pdo->prepare('INSERT INTO Booking_Seat (bookingID, seatID) VALUES (?, ?)');
            foreach ($seatIDs as $seatID) {
                $seatStmt->execute([$bookingID, $seatID]);
            }

            $this->pdo->commit();
            return $bookingID;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    /** One booking with its showing and seats, or null if it doesn't exist or isn't this user's. */
    public function getDetailsForUser(int $bookingID, int $userID): ?array
    {
        $rows = $this->detailRows('b.bookingID = ? AND b.userID = ?', [$bookingID, $userID]);
        return $rows[0] ?? null;
    }

    /** All of a user's bookings, newest first, each with its seats. */
    public function listDetailsForUser(int $userID): array
    {
        return $this->detailRows('b.userID = ?', [$userID]);
    }

    /** Every seat in the showing's hall with a `booked` flag, ordered for rendering. */
    public function seatMap(int $showingID): array
    {
        $stmt = $this->pdo->prepare('
            SELECT se.seatID, se.seatRow, se.seatNumber, se.tierID,
                   CONCAT(se.seatRow, se.seatNumber) AS seatLabel,
                   (SELECT COUNT(*) FROM Booking_Seat bs JOIN Booking b ON bs.bookingID = b.bookingID
                     WHERE b.showingID = s.showingID AND bs.seatID = se.seatID) AS bookedCount
            FROM Showing s
            JOIN Seat se ON se.hallID = s.hallID
            WHERE s.showingID = ?
            ORDER BY se.seatRow, se.seatNumber
        ');
        $stmt->execute([$showingID]);

        return array_map(function (array $seat): array {
            $seat['booked'] = (int)$seat['bookedCount'] > 0;
            unset($seat['bookedCount']);
            return $seat;
        }, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function showingSummary(int $showingID): ?array
    {
        $stmt = $this->pdo->prepare('
            SELECT s.showingID, s.showingDate, s.showingTime, h.hallID, h.hallName, m.matchName, t.tournamentName
            FROM Showing s
            JOIN Hall h ON s.hallID = h.hallID
            JOIN `Match` m ON s.matchID = m.matchID
            JOIN Tournament t ON m.tournamentID = t.tournamentID
            WHERE s.showingID = ?
        ');
        $stmt->execute([$showingID]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function seatTiers(): array
    {
        return $this->pdo->query('SELECT tierID, tierName, basePrice FROM SeatTier ORDER BY tierID')
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalCount(): int
    {
        return (int)$this->pdo->query('SELECT COUNT(*) FROM Booking')->fetchColumn();
    }

    /** Latest bookings across all users (admin dashboard). */
    public function getLatest(int $limit = 5): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM vw_user_bookings ORDER BY bookingDate DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** All bookings across all users (admin). */
    public function getAll(): array
    {
        return $this->pdo->query('SELECT * FROM vw_user_bookings ORDER BY bookingDate DESC')
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return int the showing's hall id; locks the showing row on MySQL */
    private function lockShowing(int $showingID): int
    {
        $lock = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql' ? ' FOR UPDATE' : '';
        $stmt = $this->pdo->prepare('SELECT hallID FROM Showing WHERE showingID = ?' . $lock);
        $stmt->execute([$showingID]);
        $hallID = $stmt->fetchColumn();
        if ($hallID === false) {
            throw new BookingException('Showing not found.');
        }
        return (int)$hallID;
    }

    /** Seats from $seatIDs that actually belong to the hall. */
    private function seatsInHall(int $hallID, array $seatIDs): array
    {
        $marks = implode(',', array_fill(0, count($seatIDs), '?'));
        $stmt = $this->pdo->prepare("SELECT seatID, seatRow, seatNumber FROM Seat WHERE hallID = ? AND seatID IN ($marks)");
        $stmt->execute([$hallID, ...$seatIDs]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function alreadyBooked(int $showingID, array $seatIDs): array
    {
        $marks = implode(',', array_fill(0, count($seatIDs), '?'));
        $stmt = $this->pdo->prepare("
            SELECT CONCAT(se.seatRow, se.seatNumber)
            FROM Booking_Seat bs
            JOIN Booking b ON bs.bookingID = b.bookingID
            JOIN Seat se ON se.seatID = bs.seatID
            WHERE b.showingID = ? AND bs.seatID IN ($marks)
        ");
        $stmt->execute([$showingID, ...$seatIDs]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /** Bookings (with showing details) matching $where, each carrying a `seats` list. */
    private function detailRows(string $where, array $params): array
    {
        $stmt = $this->pdo->prepare("
            SELECT b.bookingID, b.bookingDate, b.totalAmount,
                   s.showingDate, s.showingTime, h.hallName, m.matchName,
                   t.tournamentName, g.gameName
            FROM Booking b
            JOIN Showing s ON b.showingID = s.showingID
            JOIN Hall h ON s.hallID = h.hallID
            JOIN `Match` m ON s.matchID = m.matchID
            JOIN Tournament t ON m.tournamentID = t.tournamentID
            JOIN Game g ON t.gameID = g.gameID
            WHERE $where
            ORDER BY b.bookingDate DESC, b.bookingID DESC
        ");
        $stmt->execute($params);
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($bookings === []) {
            return [];
        }

        $ids = array_column($bookings, 'bookingID');
        $marks = implode(',', array_fill(0, count($ids), '?'));
        $seatStmt = $this->pdo->prepare("
            SELECT bs.bookingID, CONCAT(se.seatRow, se.seatNumber) AS seatLabel, st.tierName
            FROM Booking_Seat bs
            JOIN Seat se ON bs.seatID = se.seatID
            JOIN SeatTier st ON se.tierID = st.tierID
            WHERE bs.bookingID IN ($marks)
            ORDER BY se.seatRow, se.seatNumber
        ");
        $seatStmt->execute($ids);
        $seatsByBooking = [];
        foreach ($seatStmt->fetchAll(PDO::FETCH_ASSOC) as $seat) {
            $seatsByBooking[$seat['bookingID']][] = ['seatLabel' => $seat['seatLabel'], 'tierName' => $seat['tierName']];
        }

        foreach ($bookings as &$booking) {
            $booking['seats'] = $seatsByBooking[$booking['bookingID']] ?? [];
        }
        return $bookings;
    }
}
