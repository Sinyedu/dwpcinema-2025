<?php
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    $redirect = urlencode($_SERVER['REQUEST_URI']);
    header("Location: login.php?redirect=$redirect");
    exit;
}

$config = require __DIR__ . '/../config.php';
$emailConfig = $config['email'];

$pdo = Database::getInstance();

$stmt = $pdo->prepare("SELECT firstName, lastName, email FROM User WHERE userID = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$firstName = $user['firstName'];
$lastName  = $user['lastName'];
$email     = $user['email'];

$tournamentStatement = $pdo->query("SELECT tournamentID, tournamentName FROM Tournament ORDER BY tournamentName ASC");
$tournaments = $tournamentStatement->fetchAll(PDO::FETCH_ASSOC);

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = strip_tags(trim($_POST['message'] ?? ''));
    $tournamentID = !empty($_POST['tournament']) ? (int)$_POST['tournament'] : null;

    if (!$tournamentID || !$message) {
        $error = "Please select a tournament and write a message.";
    } else {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = $emailConfig['smtp_host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $emailConfig['username'];
            $mail->Password   = $emailConfig['password'];
            $mail->SMTPSecure = $emailConfig['smtp_secure'];
            $mail->Port       = $emailConfig['smtp_port'];

            $mail->setFrom($emailConfig['from_email'], $emailConfig['from_name']);
            $mail->addAddress($emailConfig['to_email']);
            $mail->addReplyTo($email, "$firstName $lastName");

            $mail->Subject = "New Reservation from $firstName $lastName";
            $mail->Body    = "Name: $firstName $lastName\nEmail: $email\nTournament ID: $tournamentID\n\nMessage:\n$message";

            $mail->send();

            $stmt = $pdo->prepare("
                INSERT INTO ContactForm 
                (firstName, lastName, email, category, message, tournamentID)
                VALUES (?, ?, ?, 'Reservation', ?, ?)
            ");
            $stmt->execute([$firstName, $lastName, $email, $message, $tournamentID]);

            $success = "Your reservation has been sent successfully!";
        } catch (Exception $e) {
            $error = "Reservation could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Reservation - DWP Esports Cinema</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 text-gray-900">

    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <section class="max-w-3xl mx-auto p-6 mt-16 bg-white rounded shadow">
        <h2 class="text-3xl font-semibold mb-6 text-center">Reservation Form</h2>

        <?php if ($success): ?>
            <div class="bg-green-100 text-green-800 p-4 mb-4 rounded">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-100 text-red-800 p-4 mb-4 rounded">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="post" class="space-y-4">
            <div>
                <label class="block font-medium mb-1">Tournament *</label>
                <select name="tournament" class="w-full p-2 border rounded" required>
                    <option value="">-- Select a tournament --</option>
                    <?php foreach ($tournaments as $t): ?>
                        <option value="<?= $t['tournamentID'] ?>">
                            <?= htmlspecialchars($t['tournamentName']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block font-medium mb-1">Message *</label>
                <textarea name="message" rows="5" class="w-full p-2 border rounded" placeholder="Write your message" required></textarea>
            </div>

            <div class="text-center">
                <button class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-500 transition">
                    Send Reservation
                </button>
            </div>
        </form>
    </section>

    <script src="/public/js/contact.js"></script>
</body>

</html>