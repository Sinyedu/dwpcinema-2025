<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    $redirect = urlencode($_SERVER['REQUEST_URI']);
    header("Location: login.php?redirect=$redirect");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Reservation - DWP Esports Cinema</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-neutral-900 text-white">

    <?php include __DIR__ . '/includes/navbar.php'; ?>
    <?php include __DIR__ . '/includes/contactform.php'; ?>

    <script src="/public/js/contact.js"></script>
</body>

</html>