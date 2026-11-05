<?php
declare(strict_types=1);

/**
 * Validates user-supplied account fields. Returns ['data' => clean values, 'errors' => field => message].
 * Never HTML-encodes: encoding belongs at output (see e()). Passwords are never altered.
 */
final class UserInput
{
    public const PASSWORD_MIN = 8;
    public const PASSWORD_MAX_BYTES = 72; // bcrypt silently truncates beyond this

    public static function registration(array $post): array
    {
        $result = self::profile($post);
        $password = $post['password'] ?? null;

        if (!is_string($password) || $password === '') {
            $result['errors']['password'] = 'Password is required.';
        } elseif (mb_strlen($password) < self::PASSWORD_MIN) {
            $result['errors']['password'] = 'Password must be at least ' . self::PASSWORD_MIN . ' characters.';
        } elseif (strlen($password) > self::PASSWORD_MAX_BYTES) {
            $result['errors']['password'] = 'Password must be at most ' . self::PASSWORD_MAX_BYTES . ' bytes.';
        } else {
            $result['data']['password'] = $password;
        }

        return $result;
    }

    public static function profile(array $post): array
    {
        $data = [];
        $errors = [];

        foreach (['firstName' => 'First name', 'lastName' => 'Last name'] as $field => $label) {
            $value = self::text($post[$field] ?? null);
            if ($value === null || $value === '') {
                $errors[$field] = "$label is required.";
            } elseif (mb_strlen($value) > 100) {
                $errors[$field] = "$label must be at most 100 characters.";
            } else {
                $data[$field] = $value;
            }
        }

        $email = self::text($post['email'] ?? null);
        if ($email === null || $email === '' || mb_strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'A valid email address is required.';
        } else {
            $data['email'] = $email;
        }

        return ['data' => $data, 'errors' => $errors];
    }

    /** Trim a scalar string; anything else (arrays, null) is invalid. */
    public static function text(mixed $value): ?string
    {
        return is_string($value) ? trim($value) : null;
    }
}
