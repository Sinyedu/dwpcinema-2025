<?php
declare(strict_types=1);

/** Test helper: insert users straight into the database. */
final class Users
{
    public static function insert(PDO $pdo, string $email, string $password, array $overrides = []): array
    {
        $row = array_merge([
            'firstName' => 'Test', 'lastName' => 'User', 'userEmail' => $email,
            'isAdmin' => 0, 'isActive' => 1,
            'passwordHash' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 4]),
        ], $overrides);

        $cols = implode(',', array_keys($row));
        $marks = implode(',', array_fill(0, count($row), '?'));
        $pdo->prepare("INSERT INTO \"User\" ($cols) VALUES ($marks)")->execute(array_values($row));
        $row['userID'] = (int)$pdo->lastInsertId();
        return $row;
    }
}
