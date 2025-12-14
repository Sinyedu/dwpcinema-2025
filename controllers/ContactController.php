<?php
require_once __DIR__ . '/../models/ContactForm.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class ContactController
{
    private ContactForm $model;
    private PDO $pdo;
    private array $emailConfig;

    public function __construct(array $emailConfig)
    {
        $this->pdo = Database::getInstance();
        $this->model = new ContactForm($this->pdo);
        $this->emailConfig = $emailConfig;
    }

    public function submitReservation(array $postData, int $userID): array
    {
        $result = ['success' => '', 'error' => ''];

        $stmt = $this->pdo->prepare("SELECT firstName, lastName, userEmail, lastReservationAt FROM `User` WHERE userID = ?");
        $stmt->execute([$userID]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $result['error'] = "User not found.";
            return $result;
        }

        if ($user['lastReservationAt']) {
            $lastTime = new DateTime($user['lastReservationAt']);
            $now = new DateTime();
            if (($now->getTimestamp() - $lastTime->getTimestamp()) < 3600) {
                $result['error'] = "You can only send a new reservation once per hour. Please try again later.";
                return $result;
            }
        }

        $firstName = $user['firstName'];
        $lastName  = $user['lastName'];
        $email     = $user['userEmail'];

        $subject      = strip_tags(trim($postData['subject'] ?? ''));
        $message      = strip_tags(trim($postData['message'] ?? ''));
        $tournamentID = !empty($postData['tournament']) ? (int)$postData['tournament'] : null;

        if (!$subject || !$message || !$tournamentID) {
            $result['error'] = "Please enter a subject, select a tournament, and write a message.";
            return $result;
        }

        $tournamentStmt = $this->pdo->prepare("SELECT tournamentName FROM Tournament WHERE tournamentID = ?");
        $tournamentStmt->execute([$tournamentID]);
        $tournament = $tournamentStmt->fetch(PDO::FETCH_ASSOC);
        $tournamentName = $tournament['tournamentName'] ?? 'Unknown Tournament';

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $this->emailConfig['smtp_host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->emailConfig['username'];
            $mail->Password   = $this->emailConfig['password'];
            $mail->SMTPSecure = $this->emailConfig['smtp_secure'];
            $mail->Port       = $this->emailConfig['smtp_port'];

            $mail->setFrom($this->emailConfig['from_email'], $this->emailConfig['from_name']);
            $mail->addAddress($this->emailConfig['to_email']);
            $mail->addReplyTo($email, "$firstName $lastName");

            $mail->Subject = $subject;
            $mail->Body    = "Name: $firstName $lastName\nEmail: $email\nTournament: $tournamentName\n\nMessage:\n$message";

            $mail->send();
        } catch (Exception $e) {
            $result['error'] = "Reservation could not be sent. Mailer Error: {$mail->ErrorInfo}";
            return $result;
        }

        $this->model->createSubmission([
            'firstName' => $firstName,
            'lastName'  => $lastName,
            'userEmail' => $email,
            'category'  => 'Reservation',
            'subject'   => $subject,
            'message'   => $message,
            'tournament' => $tournamentID
        ]);

        $updateStmt = $this->pdo->prepare("UPDATE `User` SET lastReservationAt = NOW() WHERE userID = ?");
        $updateStmt->execute([$userID]);

        $result['success'] = "Your reservation has been sent successfully!";
        return $result;
    }
    public function getTournaments(): array
    {
        $stmt = $this->pdo->query("SELECT tournamentID, tournamentName FROM Tournament ORDER BY tournamentName ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
