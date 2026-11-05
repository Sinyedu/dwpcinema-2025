<?php
declare(strict_types=1);

/**
 * Application settings. Uses config/config.php when present (production, see config.example.php);
 * otherwise reads environment variables (Docker / CI).
 */
final class Config
{
    private static ?array $cache = null;

    public static function get(): array
    {
        if (self::$cache === null) {
            $file = dirname(__DIR__) . '/config/config.php';
            self::$cache = is_file($file) ? require $file : self::fromEnv(getenv() ?: []);
        }
        return self::$cache;
    }

    /** @param array<string, string> $env */
    public static function fromEnv(array $env): array
    {
        $get = static fn(string $key, string $default = '') => $env[$key] ?? $default;

        return [
            'db' => [
                'host' => $get('DB_HOST', 'localhost'),
                'name' => $get('DB_NAME', 'dwpcinemaDB'),
                'user' => $get('DB_USER', 'root'),
                'password' => $get('DB_PASSWORD'),
                'charset' => $get('DB_CHARSET', 'utf8mb4'),
            ],
            'email' => [
                'smtp_host' => $get('MAIL_HOST'),
                'smtp_port' => (int)$get('MAIL_PORT', '587'),
                'smtp_secure' => $get('MAIL_SECURE', 'tls'),
                'username' => $get('MAIL_USERNAME'),
                'password' => $get('MAIL_PASSWORD'),
                'from_email' => $get('MAIL_FROM', 'no-reply@localhost'),
                'from_name' => $get('MAIL_FROM_NAME', 'DWP Esports Cinema'),
                'to_email' => $get('MAIL_TO', 'reservations@localhost'),
            ],
        ];
    }
}
