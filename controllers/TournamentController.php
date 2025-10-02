<?php
require_once "../models/Tournament.php";

class TournamentController {
    private $tournamentModel;

    public function __construct($pdo) {
        $this->tournamentModel = new Tournament($pdo);
    }

    public function getAllTournaments() {
        return $this->tournamentModel->getAll();
    }

    public function createTournament($data) {
        return $this->tournamentModel->create($data);
    }

    public function updateTournament($id, $data) {
        return $this->tournamentModel->update($id, $data);
    }

    public function deleteTournament($id) {
        return $this->tournamentModel->delete($id);
    }
}
?>
