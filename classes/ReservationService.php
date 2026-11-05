<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;

/** Handles a logged-in user's reservation request: validate, rate-limit, email the venue, record it. */
final class ReservationService
{
    private const COOLDOWN_SECONDS = 3600;

    private User $users;
    private Tournament $tournaments;
    private ContactForm $submissions;

    public function __construct(PDO $pdo, private array $emailConfig)
    {
        $this->users = new User($pdo);
        $this->tournaments = new Tournament($pdo);
        $this->submissions = new ContactForm($pdo);
    }

    /** @return array{success: string, error: string} */
    public function submit(array $post, int $userID): array
    {
        $user = $this->users->getReservationContact($userID);

        if (!$user) {
            return ['success' => '', 'error' => 'User not found.'];
        }

        if ($user['lastReservationAt'] && (time() - strtotime($user['lastReservationAt'])) < self::COOLDOWN_SECONDS) {
            return ['success' => '', 'error' => 'You can only send a new reservation once per hour. Please try again later.'];
        }

        // Newlines are stripped from the subject: it becomes an email header.
        $subject = trim(preg_replace('/\s+/', ' ', (string)($post['subject'] ?? '')));
        $message = trim((string)($post['message'] ?? ''));
        $tournamentID = (int)($post['tournament'] ?? 0);

        if ($subject === '' || $message === '' || $tournamentID <= 0) {
            return ['success' => '', 'error' => 'Please enter a subject, select a tournament, and write a message.'];
        }
        if (mb_strlen($subject) > 200 || mb_strlen($message) > 5000) {
            return ['success' => '', 'error' => 'Your subject or message is too long.'];
        }

        $tournamentName = $this->tournaments->getName($tournamentID);
        if ($tournamentName === null) {
            return ['success' => '', 'error' => 'Please select a valid tournament.'];
        }

        try {
            $this->sendMail($user, $subject, $message, $tournamentName);
        } catch (MailException $e) {
            error_log('Reservation mail failed: ' . $e->getMessage());
            return ['success' => '', 'error' => 'Your reservation could not be sent. Please try again later.'];
        }

        $this->submissions->createSubmission([
            'firstName' => $user['firstName'],
            'lastName' => $user['lastName'],
            'userEmail' => $user['userEmail'],
            'category' => 'Reservation',
            'message' => $message,
            'tournament' => $tournamentID,
        ]);
        $this->users->touchLastReservation($userID);

        return ['success' => 'Your reservation has been sent successfully!', 'error' => ''];
    }

    private function sendMail(array $user, string $subject, string $message, string $tournamentName): void
    {
        require_once dirname(__DIR__) . '/vendor/autoload.php'; // PHPMailer; only needed when sending

        $c = $this->emailConfig;
        $name = $user['firstName'] . ' ' . $user['lastName'];

        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->Host = $c['smtp_host'];
        $mail->SMTPAuth = true;
        $mail->Username = $c['username'];
        $mail->Password = $c['password'];
        $mail->SMTPSecure = $c['smtp_secure'];
        $mail->Port = $c['smtp_port'];
        $mail->setFrom($c['from_email'], $c['from_name']);
        $mail->addAddress($c['to_email']);
        $mail->addReplyTo($user['userEmail'], $name);
        $mail->Subject = $subject;
        $mail->Body = "Name: $name\nEmail: {$user['userEmail']}\nTournament: $tournamentName\n\nMessage:\n$message";
        $mail->send();
    }
}
