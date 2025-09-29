<?php
session_start();
require_once "../database/connection.php";
require_once "../controllers/AdminController.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$adminController = new AdminController($pdo);
$users = $adminController->getAllUsers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard - DWP Cinema</title>
<style>
    body {
        font-family: 'Orbitron', sans-serif;
        background: #0b0b0b;
        color: #fff;
        margin: 0;
        padding: 0;
    }
    header {
        background: #111;
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 0 15px #ff007f;
    }
    header h1 {
        color: #ff007f;
        margin: 0;
        text-shadow: 0 0 10px #ff007f;
    }
    header a {
        color: #fff;
        text-decoration: none;
        background: #ff007f;
        padding: 10px 15px;
        border-radius: 6px;
        font-weight: bold;
    }
    header a:hover {
        background: #ff40bf;
    }
    .container {
        padding: 30px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        background: #111;
        box-shadow: 0 0 10px #1e90ff;
        border-radius: 8px;
        overflow: hidden;
    }
    th, td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #222;
    }
    th {
        background: #1e90ff;
        color: #fff;
    }
    tr:hover {
        background: #222;
    }
</style>
</head>
<body>

<header>
    <h1>Admin Dashboard</h1>
    <a href="../public/logout.php">Logout</a>
</header>

<div class="container">
    <h2>All Registered Users</h2>
    <table>
        <thead>
            <tr>
                <th>User ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['userID']) ?></td>
                <td><?= htmlspecialchars($user['firstName']) ?></td>
                <td><?= htmlspecialchars($user['lastName']) ?></td>
                <td><?= htmlspecialchars($user['userEmail']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
