<?php
// Copy to config/config.php (git-ignored) and fill in real values.
return [
    'db' => [
        'host' => 'localhost',
        'name' => 'dwpcinema',
        'user' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
    ],
    'email' => [
        'smtp_host' => 'smtp.example.com',
        'smtp_port' => 587,
        'smtp_secure' => 'tls',
        'username' => 'no-reply@example.com',
        'password' => '',
        'from_email' => 'no-reply@example.com',
        'from_name' => 'DWP Esports Cinema',
        'to_email' => 'reservations@example.com',
    ],
];
