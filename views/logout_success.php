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

<body class="bg-neutral-900 flex items-center justify-center min-h-screen">

    <div class="bg-neutral-800 p-10 rounded-lg shadow-md text-center max-w-md">
        <h1 class="text-2xl font-semibold mb-4 text-white">You have been logged out</h1>
        <p class="text-gray-400 mb-6">Your admin session has ended successfully.</p>
        <a href="../admin_login.php" class="bg-blue-600 hover:bg-blue-300 text-white px-4 py-2 rounded">Login Again</a>
        <a href="../index.php" class="bg-gray-600 hover:bg-gray-300 text-white px-4 py-2 rounded ml-2">Go to Homepage</a>
    </div>

</body>

</html>