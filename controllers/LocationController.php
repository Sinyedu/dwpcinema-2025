<?php
require_once __DIR__ . '/../models/Location.php';

class LocationController
{
    private Location $locationModel;

    public function __construct(PDO $pdo)
    {
        $this->locationModel = new Location($pdo);
    }

    public function getAllLocations(): array
    {
        return $this->locationModel->getAll();
    }

    public function getLocation(int $id): ?array
    {
        return $this->locationModel->getById($id);
    }

    public function addLocation(array $data): bool
    {
        return $this->locationModel->create($data);
    }

    public function updateLocation(int $id, array $data): bool
    {
        return $this->locationModel->update($id, $data);
    }

    public function deleteLocation(int $id): bool
    {
        return $this->locationModel->delete($id);
    }
}
