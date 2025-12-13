<?php
class BookingController
{
    private Booking $bookingModel;

    public function __construct(Booking $bookingModel)
    {
        $this->bookingModel = $bookingModel;
    }

    public function book(int $userID, int $showingID, array $seatIDs): int
    {
        return $this->bookingModel->create($userID, $showingID, $seatIDs);
    }
}
