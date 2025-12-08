<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Logged Out - DWP Esports Cinema Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="bg-white p-10 rounded-lg shadow-md text-center max-w-md">
        <h1 class="text-2xl font-semibold mb-4 text-gray-800">You have been logged out</h1>
        <p class="text-gray-600 mb-6">Your admin session has ended successfully.</p>
        <a href="admin_login.php" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded">Login Again</a>
    </div>

</body>

</html>