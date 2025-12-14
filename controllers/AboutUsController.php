<?php
require_once __DIR__ . '/../classes/AboutUs.php';

class AboutUsController
{
    private AboutUs $model;

    public function __construct(PDO $pdo)
    {
        $this->model = new AboutUs($pdo);
    }

    public function getAll(): array
    {
        return $this->model->getAll();
    }

    public function getById(int $id): ?array
    {
        return $this->model->getById($id);
    }

    public function create(array $data): bool
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->model->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->model->delete($id);
    }
}
