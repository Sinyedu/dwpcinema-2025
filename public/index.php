<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>DWP Cinema - eSports Watch Parties</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&display=swap" rel="stylesheet">
<style>
    body {
        margin: 0;
        font-family: 'Orbitron', sans-serif;
        background-color: #0b0b0b;
        color: #fff;
    }
    header {
        background: #111;
        padding: 30px;
        text-align: center;
        border-bottom: 2px solid #1e90ff;
    }
    header h1 {
        color: #1e90ff;
        text-shadow: 0 0 10px #1e90ff;
        margin: 0;
    }
    nav {
        margin-top: 15px;
    }
    nav a {
        color: #1e90ff;
        text-decoration: none;
        margin: 0 15px;
        font-weight: bold;
        transition: 0.3s;
    }
    nav a:hover { color: #63b8ff; }
    section {
        max-width: 900px;
        margin: 50px auto;
        text-align: center;
    }
    h2 {
        color: #ff007f;
        text-shadow: 0 0 8px #ff007f;
    }
    p { font-size: 1.1em; line-height: 1.6em; }
    .btn {
        display: inline-block;
        padding: 12px 30px;
        margin-top: 20px;
        color: #fff;
        background: #1e90ff;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: bold;
        text-decoration: none;
        box-shadow: 0 0 10px #1e90ff;
        transition: 0.3s;
    }
    .btn:hover {
        background: #63b8ff;
        box-shadow: 0 0 20px #63b8ff;
    }
</style>
</head>
<body>

<header>
    <h1>🎮 DWP Cinema - eSports Watch Parties</h1>
    <nav>
        <?php if(isset($_SESSION['user_id'])): ?>
            <span>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>!</span>
            <a href="users.php">Community</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="admin_login.php">Admin Login</a>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </nav>
</header>

<section>
    <h2>Watch the biggest eSports tournaments live!</h2>
    <p>Join us for Worlds Finals, LEC Grand Final, Valorant Champions 2025, and more. Book your seats in advance and enjoy the ultimate gaming experience on the big screen.</p>
    
    <?php if(!isset($_SESSION['user_id'])): ?>
        <a href="register.php" class="btn">Get Started</a>
    <?php else: ?>
        <a href="" class="btn">View Community</a>
    <?php endif; ?>
</section>

<section>
    <h2>Upcoming Tournaments</h2>
    <p>Stay tuned for schedule updates and special events. Coming soon: Worlds Finals 2025, Valorant Champions 2025 Grand Final!</p>
</section>

</body>
</html>
