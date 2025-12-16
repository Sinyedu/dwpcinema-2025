<?php
require_once __DIR__ . '/../models/OpeningHours.php';

class OpeningHoursController
{
    private OpeningHours $model;

    public function __construct(PDO $pdo)
    {
        $this->model = new OpeningHours($pdo);
    }

    public function getAll(): array
    {
        return $this->model->getAll();
    }

    public function updateDay(array $data): bool
    {
        $day = $data['dayOfWeek'] ?? '';
        $open = $data['openTime'] ?? '00:00';
        $close = $data['closeTime'] ?? '00:00';
        $closed = !empty($data['isClosed']) ? true : false;

        if (!$day) {
            throw new Exception("Day of the week is required.");
        }

        return $this->model->updateDay($day, $open, $close, $closed);
    }
}
