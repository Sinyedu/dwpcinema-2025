<?php
require_once __DIR__ . '../../controllers/SecurityController.php';
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
            SELECT contactFormID, firstName, lastName, email, category, message, created_at
            FROM ContactForm
            WHERE category = 'Reservation'
            ORDER BY created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getAllSubmissions(string $category = null): array
    {
        if ($category) {
            $stmt = $this->pdo->prepare("
                SELECT contactFormID, firstName, lastName, email, category, message, created_at
                FROM ContactForm
                WHERE category = :category
                ORDER BY created_at DESC
            ");
            $stmt->execute(['category' => $category]);
        } else {
            $stmt = $this->pdo->query("
                SELECT contactFormID, firstName, lastName, email, category, message, created_at
                FROM ContactForm
                ORDER BY created_at DESC
            ");
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createSubmission(array $data): bool
    {
        $firstName = SecurityController::sanitizeInput($data['firstName']);
        $lastName  = SecurityController::sanitizeInput($data['lastName']);
        $email     = SecurityController::sanitizeInput($data['email']);
        $category  = SecurityController::sanitizeInput($data['category']);
        $message   = SecurityController::sanitizeInput($data['message']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email address.");
        }

        $stmt = $this->pdo->prepare("
        INSERT INTO ContactForm (firstName, lastName, email, category, message)
        VALUES (:firstName, :lastName, :email, :category, :message)
    ");
        return $stmt->execute([
            'firstName' => $firstName,
            'lastName'  => $lastName,
            'email'     => $email,
            'category'  => $category,
            'message'   => $message,
        ]);
    }
}
