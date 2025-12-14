<?php
require_once __DIR__ . '/../classes/Database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = Database::getInstance();

$success = '';
$error   = '';

$loggedIn  = isset($_SESSION['user']);
$autoFirst = $loggedIn ? $_SESSION['user']['firstName'] : '';
$autoLast  = $loggedIn ? $_SESSION['user']['lastName']  : '';
$autoEmail = $loggedIn ? $_SESSION['user']['email']     : '';

$tournamentStatement = $pdo->query("
    SELECT tournamentID, tournamentName 
    FROM Tournament 
    ORDER BY tournamentName ASC
");
$tournaments = $tournamentStatement->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $firstName    = strip_tags(trim($_POST['firstName'] ?? ''));
    $lastName     = strip_tags(trim($_POST['lastName'] ?? ''));
    $email        = trim($_POST['email'] ?? '');
    $category     = $_POST['category'] ?? '';
    $message      = strip_tags(trim($_POST['message'] ?? ''));
    $tournamentID = !empty($_POST['tournament']) ? (int)$_POST['tournament'] : null;

    if (!$firstName || !$lastName || !$email || !$category || !$message) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif ($category === 'Reservation' && !$tournamentID) {
        $error = "Please select a tournament for your reservation.";
    } else {

        $stmt = $pdo->prepare("
            INSERT INTO ContactForm 
            (firstName, lastName, email, category, message, tournamentID)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        if ($stmt->execute([
            $firstName,
            $lastName,
            $email,
            $category,
            $message,
            $tournamentID
        ])) {

            $adminEmail = "simnyb01@easv365.dk";
            $subject    = "New Contact Form Message – $category";

            $emailBody = "
            New contact form submission

            Name: $firstName $lastName
            Email: $email
            Category: $category
            Tournament ID: " . ($tournamentID ?? 'N/A') . "

            Message:
            $message
                ";

            $headers = implode("\r\n", [
                "From: DWP Esports Cinema <simnyb01@easv365.dk>",
                "Reply-To: $email",
                "Content-Type: text/plain; charset=UTF-8"
            ]);

            mail($adminEmail, $subject, $emailBody, $headers);


            $userSubject = "We received your message";
            $userMessage = "Hi $firstName,

            Thanks for contacting DWP Esports Cinema.
            We have received your message and will get back to you shortly.

            Best regards,
            DWP Esports Cinema";

            mail(
                $email,
                $userSubject,
                $userMessage,
                "From: DWP Esports Cinema <simnyb01@easv365.dk>\r\nContent-Type: text/plain; charset=UTF-8"
            );

            $success = "Your message has been sent successfully!";
        } else {
            $error = "Something went wrong. Please try again later.";
        }
    }
}

if (isset($_GET['sent'])) {
    $success = "Your message has been sent successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Contact Us - DWP Esports Cinema</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 text-gray-900">

    <section class="max-w-3xl mx-auto p-6 mt-16 bg-white rounded shadow">
        <h2 class="text-3xl font-semibold mb-6 text-center">Contact Us</h2>

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
                <label class="block font-medium mb-1">Category *</label>
                <select name="category" id="category" class="w-full p-2 border rounded" required>
                    <option value="">-- Select a category --</option>
                    <option value="Support">Support</option>
                    <option value="Payment">Payment</option>
                    <option value="Order">Order</option>
                    <option value="Reservation">Reservation</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div id="tournamentWrapper" class="hidden">
                <label class="block font-medium mb-1">Tournament *</label>
                <select name="tournament" class="w-full p-2 border rounded">
                    <option value="">-- Select a tournament --</option>
                    <?php foreach ($tournaments as $t): ?>
                        <option value="<?= $t['tournamentID'] ?>">
                            <?= htmlspecialchars($t['tournamentName']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <input type="text" name="firstName" placeholder="First name *"
                    value="<?= htmlspecialchars($autoFirst) ?>"
                    class="w-full p-2 border rounded"
                    <?= $loggedIn ? 'readonly' : '' ?> required>

                <input type="text" name="lastName" placeholder="Last name *"
                    value="<?= htmlspecialchars($autoLast) ?>"
                    class="w-full p-2 border rounded"
                    <?= $loggedIn ? 'readonly' : '' ?> required>
            </div>

            <input type="email" name="email" placeholder="Email *"
                value="<?= htmlspecialchars($autoEmail) ?>"
                class="w-full p-2 border rounded"
                <?= $loggedIn ? 'readonly' : '' ?> required>

            <textarea name="message" rows="5" placeholder="Message *"
                class="w-full p-2 border rounded" required></textarea>

            <div class="text-center">
                <button class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-500 transition">
                    Send Message
                </button>
            </div>

        </form>
    </section>

    <script src="public/js/contact.js"></script>
</body>

</html>