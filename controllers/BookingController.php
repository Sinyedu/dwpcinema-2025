<?php
class BookingController {
    private $showingModel;
    private $bookingModel;

    public function __construct(Showing $showingModel, Booking $bookingModel) {
        $this->showingModel = $showingModel;
        $this->bookingModel = $bookingModel;
    }

    public function listShowings($tournamentID = null) {
        return $this->showingModel->getAll($tournamentID);
    }

    public function book($userID, $showingID) {
        return $this->bookingModel->create($userID, $showingID);
    }
}
