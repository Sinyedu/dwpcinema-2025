<?php
class Booking
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Create a booking for a user and selected seats.
     *
     * @param int $userID
     * @param int $showingID
     * @param array $seatIDs
     * @return int The bookingID
     * @throws Exception
     */
    public function create(int $userID, int $showingID, array $seatIDs): int
    {
        if (empty($seatIDs)) {
            throw new Exception("No seats selected.");
        }

        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("INSERT INTO Booking (userID, showingID) VALUES (?, ?)");
            $stmt->execute([$userID, $showingID]);
            $bookingID = (int)$this->pdo->lastInsertId();

            $seatStmt = $this->pdo->prepare("INSERT INTO Booking_Seat (bookingID, seatID) VALUES (?, ?)");
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
}
