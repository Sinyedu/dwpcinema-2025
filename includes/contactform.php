<?php
require_once __DIR__ . '/../classes/Database.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = Database::getInstance();
$success = '';
$error = '';

$loggedIn = isset($_SESSION['user']);
$autoFirst = $loggedIn ? $_SESSION['user']['firstName'] : '';
$autoLast = $loggedIn ? $_SESSION['user']['lastName'] : '';
$autoEmail = $loggedIn ? $_SESSION['user']['email'] : '';

$tournamentStatement = $pdo->query("SELECT tournamentID, tournamentName FROM Tournament ORDER BY tournamentName ASC");
$tournaments = $tournamentStatement->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $email = trim($_POST['email']);
    $category = $_POST['support'];
    $message = trim($_POST['message']);
    $tournamentID = !empty($_POST['tournament']) ? intval($_POST['tournament']) : null;

    if (!$firstName || !$lastName || !$email || !$category || !$message) {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif ($category === 'Reservation' && !$tournamentID) {
        $error = "Please select a tournament for your reservation.";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO contactform (firstName, lastName, email, category, message, tournamentID)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        if ($stmt->execute([$firstName, $lastName, $email, $category, $message, $tournamentID])) {
            $success = "Your message has been sent successfully!";
        } else {
            $error = "Something went wrong. Please try again later.";
        }
    }
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
            <div class="bg-green-100 text-green-800 p-4 mb-4 rounded"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-100 text-red-800 p-4 mb-4 rounded"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="" method="post" class="space-y-4">

            <div>
                <label for="category" class="block font-medium mb-1">Category *</label>
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
                <label for="tournament" class="block font-medium mb-1">Tournament *</label>
                <select name="tournament" id="tournament" class="w-full p-2 border rounded">
                    <option value="">-- Select a tournament --</option>
                    <?php foreach ($tournaments as $t): ?>
                        <option value="<?= $t['tournamentID'] ?>"><?= htmlspecialchars($t['tournamentName']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label for="firstName" class="block font-medium mb-1">First Name *</label>
                    <input type="text" name="firstName" id="firstName"
                        value="<?= htmlspecialchars($autoFirst) ?>"
                        class="w-full p-2 border rounded"
                        <?= $loggedIn ? 'readonly' : '' ?>
                        required>
                </div>

                <div>
                    <label for="lastName" class="block font-medium mb-1">Last Name *</label>
                    <input type="text" name="lastName" id="lastName"
                        value="<?= htmlspecialchars($autoLast) ?>"
                        class="w-full p-2 border rounded"
                        <?= $loggedIn ? 'readonly' : '' ?>
                        required>
                </div>
            </div>

            <div>
                <label for="email" class="block font-medium mb-1">Email *</label>
                <input type="email" name="email" id="email"
                    value="<?= htmlspecialchars($autoEmail) ?>"
                    class="w-full p-2 border rounded"
                    <?= $loggedIn ? 'readonly' : '' ?>
                    required>
            </div>

            <div>
                <label for="message" class="block font-medium mb-1">Message *</label>
                <textarea name="message" id="message" rows="5" class="w-full p-2 border rounded" required></textarea>
            </div>

            <div class="text-center">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-500 transition font-medium">
                    Send Message
                </button>
            </div>
        </form>
    </section>

    <script src="public/js/contact.js"></script>
</body>

</html>