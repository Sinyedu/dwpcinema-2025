<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Contact & Reservations</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 text-gray-900">

    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <?php include __DIR__ . '/includes/contactform.php'; ?>

    <script src="/public/js/contact.js"></script>

</body>

</html>