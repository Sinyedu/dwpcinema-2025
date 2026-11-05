<?php

class ContactForm
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getReservations(): array
    {
        $stmt = $this->pdo->prepare("
            SELECT contactFormID, firstName, lastName, email AS userEmail, category, message, tournamentID, created_at, status
            FROM ContactForm
            WHERE category = 'Reservation'
            ORDER BY created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getAllSubmissions(?string $category = null): array
    {
        if ($category) {
            $stmt = $this->pdo->prepare("
                SELECT contactFormID, firstName, lastName, email AS userEmail, category, message, tournamentID, created_at, status
                FROM ContactForm
                WHERE category = :category
                ORDER BY created_at DESC
            ");
            $stmt->execute(['category' => $category]);
        } else {
            $stmt = $this->pdo->query("
                SELECT contactFormID, firstName, lastName, email AS userEmail, category, message, tournamentID, created_at
                FROM ContactForm
                ORDER BY created_at DESC
            ");
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createSubmission(array $data): bool
    {
        $firstName    = trim((string)$data['firstName']);
        $lastName     = trim((string)$data['lastName']);
        $email        = trim((string)$data['userEmail']);
        $category     = trim((string)$data['category']);
        $message      = trim((string)$data['message']);
        $tournamentID = isset($data['tournament']) && !empty($data['tournament']) ? (int)$data['tournament'] : null;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email address.");
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO ContactForm (firstName, lastName, email, category, message, tournamentID)
            VALUES (:firstName, :lastName, :email, :category, :message, :tournamentID)
        ");

        return $stmt->execute([
            'firstName'    => $firstName,
            'lastName'     => $lastName,
            'email'        => $email,
            'category'     => $category,
            'message'      => $message,
            'tournamentID' => $tournamentID
        ]);
    }
}
