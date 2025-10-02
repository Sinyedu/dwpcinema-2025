<?php
class ShowingController {
    private $showingModel;
    private $bookingModel;

    public function __construct(Showing $showingModel, Booking $bookingModel) {
        $this->showingModel = $showingModel;
        $this->bookingModel = $bookingModel;
    }

    public function listShowings($tournamentID = null) {
        return $this->showingModel->getAll($tournamentID);
    }

    public function showDetails($showingID) {
        return $this->showingModel->getById($showingID);
    }

    public function bookShowing($userID, $showingID) {
        return $this->bookingModel->create($userID, $showingID);
    }

    public function userBookings($userID) {
        return $this->bookingModel->getByUser($userID);
    }
}
