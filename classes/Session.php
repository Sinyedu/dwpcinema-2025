<?php
class Session
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $this->generateCsrfToken();
        }
    }

    private function ensureCsrfToken()
    {
        if (empty($_SESSION['csrf_token'])) {
            $this->generateCsrfToken();
        }
    }

    public function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    public function getCsrfToken()
    {
        $this->ensureCsrfToken();
        return $_SESSION['csrf_token'];
    }

    public function validateCsrfToken($token)
    {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public function generateCsrfToken()
    {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    public function getUserId()
    {
        return $_SESSION['user_id'] ?? null;
    }

    public function getUsername()
    {
        return $_SESSION['user_name'] ?? null;
    }

    public function getUserEmail()
    {
        return $_SESSION['user_email'] ?? null;
    }

    public function getUserAvatar()
    {
        return $_SESSION['user_avatar'] ?? 'uploads/avatars/default.png';
    }

    public function login($user)
    {
        $_SESSION['user_id']    = $user['userID'];
        $_SESSION['user_name']  = $user['firstName'] . ' ' . $user['lastName'];
        $_SESSION['user_email'] = $user['userEmail'];
        $_SESSION['user_avatar'] = $user['avatar'] ?? 'uploads/avatars/default.png';

        session_write_close();
        $this->generateCsrfToken();
    }

    public function logout()
    {
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 36000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();
        $this->generateCsrfToken();
    }
}
