<?php
//TODO: Make an actual cookie this is a complete copy of GPT FOR TESTING PURPOSES ONLY
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function set_secure_cookie(string $name, string $value, int $days = 30): void
{
    setcookie(
        $name,
        $value,
        [
            'expires' => time() + 86400 * $days,
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax'
        ]
    );
}


function delete_cookie(string $name): void
{
    setcookie($name, '', time() - 3600, '/');
}


function get_cookie(string $name): ?string
{
    return $_COOKIE[$name] ?? null;
}
