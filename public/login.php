<?php
session_start();
require_once "../database/connection.php";
require_once "../controllers/UserController.php";

$controller = new UserController($pdo);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $controller->login($_POST);
    if ($user) {
        $_SESSION['user_id'] = $user['userID'];
        $_SESSION['user_name'] = $user['firstName'];
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid email or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login - DWP Cinema</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&display=swap" rel="stylesheet">
<style>
    body { background: #0b0b0b; font-family: 'Orbitron', sans-serif; color: #fff; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
    .login-container { background: #111; padding: 40px; border-radius: 12px; box-shadow: 0 0 20px #1e90ff; width: 350px; text-align: center; }
    h1 { color: #1e90ff; margin-bottom: 30px; text-shadow: 0 0 10px #1e90ff; }
    input { width: 100%; padding: 12px; margin: 10px 0; border: none; border-radius: 6px; background: #222; color: #fff; }
    input:focus { outline: 2px solid #1e90ff; }
    .btn { background: #1e90ff; color: #fff; padding: 12px; width: 100%; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; box-shadow: 0 0 10px #1e90ff; transition: 0.3s; }
    .btn:hover { background: #63b8ff; box-shadow: 0 0 20px #63b8ff; }
    p.error { color: #ff007f; font-weight: bold; }
</style>
</head>
<body>

<div class="login-container">
    <h1>Login</h1>
    <?php if($error) echo "<p class='error'>$error</p>"; ?>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required />
        <input type="password" name="password" placeholder="Password" required />
        <button type="submit" class="btn">Login</button>
    </form>
    <p>Don't have an account? <a href="register.php" style="color:#ff007f;">Register</a></p>
</div>

</body>
</html>
