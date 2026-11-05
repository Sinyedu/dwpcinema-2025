<?php
declare(strict_types=1);

class Game
{
    public function __construct(private PDO $pdo)
    {
    }

    public function getAll(): array
    {
        return $this->pdo->query('SELECT * FROM Game ORDER BY gameID DESC')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllByName(): array
    {
        return $this->pdo->query('SELECT * FROM Game ORDER BY gameName ASC')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM Game WHERE gameID = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function create(string $name, string $genre): bool
    {
        return $this->pdo->prepare('INSERT INTO Game (gameName, gameGenre) VALUES (?, ?)')->execute([$name, $genre]);
    }

    public function update(int $id, string $name, string $genre): bool
    {
        return $this->pdo->prepare('UPDATE Game SET gameName = ?, gameGenre = ? WHERE gameID = ?')->execute([$name, $genre, $id]);
    }

    public function delete(int $id): bool
    {
        return $this->pdo->prepare('DELETE FROM Game WHERE gameID = ?')->execute([$id]);
    }
}
