<?php
session_start();
require_once "../database/connection.php";
require_once "../controllers/UserController.php";

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$controller = new UserController($pdo);
$users = $controller->listUsers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Community - DWP Cinema</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&display=swap" rel="stylesheet">
<style>
    body { background: #0b0b0b; font-family: 'Orbitron', sans-serif; color: #fff; margin: 0; padding: 0; }
    header { background: #111; padding: 20px; text-align: center; border-bottom: 2px solid #1e90ff; }
    header h1 { color: #1e90ff; text-shadow: 0 0 10px #1e90ff; margin: 0; }
    nav a { color: #1e90ff; margin: 0 10px; text-decoration: none; font-weight: bold; }
    nav a:hover { color: #63b8ff; }
    section { max-width: 900px; margin: 50px auto; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px; text-align: left; border-bottom: 1px solid #444; }
    th { background: #1e90ff; color: #fff; text-shadow: 0 0 5px #1e90ff; }
    tr:hover { background: #222; }
    td { color: #fff; }
</style>
</head>
<body>

<header>
    <h1>Community - Registered Users</h1>
    <nav>
        <a href="index.php">Home</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<section>
    <table>
        <tr>
            <th>ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Email</th>
        </tr>
        <?php foreach($users as $user): ?>
        <tr>
            <td><?= htmlspecialchars($user['userID']) ?></td>
            <td><?= htmlspecialchars($user['firstName']) ?></td>
            <td><?= htmlspecialchars($user['lastName']) ?></td>
            <td><?= htmlspecialchars($user['userEmail']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</section>

</body>
</html>
