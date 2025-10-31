<?php
class PasswordHasher {

    public static function hash($password) {
        $options = [
            'cost' => 10
        ];
        return password_hash($password, PASSWORD_BCRYPT, $options);
    }

    public static function verify($password, $hash) {
        return password_verify($password, $hash);
    }
}
?>
