<?php

class Booking
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create(int $userID, int $showingID, array $seatIDs): int
    {
        if (empty($seatIDs)) {
            throw new Exception("No seats selected.");
        }

        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                "INSERT INTO Booking (userID, showingID) VALUES (?, ?)"
            );
            $stmt->execute([$userID, $showingID]);

            $bookingID = (int)$this->pdo->lastInsertId();

            $seatStmt = $this->pdo->prepare(
                "INSERT INTO Booking_Seat (bookingID, seatID) VALUES (?, ?)"
            );

            foreach ($seatIDs as $seatID) {
                $seatStmt->execute([$bookingID, $seatID]);
            }

            $this->pdo->commit();
            return $bookingID;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }


    public function getByUser(int $userID): array
    {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM vw_user_bookings
            WHERE userID = ?
            ORDER BY bookingDate DESC
        ");
        $stmt->execute([$userID]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query("
            SELECT *
            FROM vw_user_bookings
            ORDER BY bookingDate DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $bookingID): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM vw_user_bookings
            WHERE bookingID = ?
        ");
        $stmt->execute([$bookingID]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }
}
