<?php
//TODO: Make it more comprehensive
class SecurityController
{

    public static function sanitizeInput($input)
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    public function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function trimInput($input)
    {
        return trim($input);
    }
}
