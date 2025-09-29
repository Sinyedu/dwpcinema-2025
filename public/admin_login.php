<?php
session_start();
require_once "../database/connection.php";
require_once "../controllers/AdminController.php";

$controller = new AdminController($pdo);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin = $controller->login($_POST['email'], $_POST['password']);
    if ($admin) {
        $_SESSION['admin_id'] = $admin['userID'];
        $_SESSION['admin_name'] = $admin['firstName'];
        header("Location: ../views/admin_dashboard.php");
        exit;
    } else {
        $error = "Invalid admin credentials!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Login - DWP Cinema</title>
<style>
    body {
        background: #0b0b0b;
        font-family: 'Orbitron', sans-serif;
        color: #fff;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    }
    .container {
        background: #111;
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 0 20px #ff007f;
        width: 350px;
        text-align: center;
    }
    h1 {
        color: #ff007f;
        margin-bottom: 30px;
        text-shadow: 0 0 10px #ff007f;
    }
    input {
        width: 100%;
        padding: 12px;
        margin: 10px 0;
        border: none;
        border-radius: 6px;
        background: #222;
        color: #fff;
    }
    input:focus {
        outline: 2px solid #ff007f;
    }
    .btn {
        background: #ff007f;
        color: #fff;
        padding: 12px;
        width: 100%;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: bold;
        box-shadow: 0 0 10px #ff007f;
        transition: 0.3s;
    }
    .btn:hover {
        background: #ff40bf;
        box-shadow: 0 0 20px #ff40bf;
    }
    p.error {
        color: #ff007f;
        font-weight: bold;
    }
</style>
</head>
<body>

<div class="container">
    <h1>Admin Login</h1>
    <?php if($error) echo "<p class='error'>$error</p>"; ?>
    <form method="POST">
        <input type="email" name="email" placeholder="Admin Email" required />
        <input type="password" name="password" placeholder="Password" required />
        <button type="submit" class="btn">Login</button>
    </form>
</div>

</body>
</html>
