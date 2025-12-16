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

    public function getUserBookings(int $userID): array
    {
        return $this->bookingModel->getByUser($userID);
    }

    public function getAllBookings(): array
    {
        return $this->bookingModel->getAll();
    }

    public function getLatestBookings(int $limit = 5): array
    {
        return $this->bookingModel->getLatest($limit);
    }

    public function getBookingById(int $bookingID): ?array
    {
        return $this->bookingModel->getById($bookingID);
    }
}
