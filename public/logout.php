<?php
session_start();

unset($_SESSION['user_id'], $_SESSION['user_name']);
unset($_SESSION['admin_id'], $_SESSION['admin_name']);

session_destroy();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

header("Location: logged_out.php");
exit;
?>
