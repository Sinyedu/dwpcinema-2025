<?php
declare(strict_types=1);

/** Per-session CSRF token. Holds a reference to the session array so it is testable. */
final class Csrf
{
    private array $session;

    public function __construct(array &$session)
    {
        $this->session = &$session;
    }

    public function token(): string
    {
        if (empty($this->session['csrf_token'])) {
            $this->rotate();
        }
        return $this->session['csrf_token'];
    }

    public function validate(?string $submitted): bool
    {
        $expected = $this->session['csrf_token'] ?? '';
        return $expected !== '' && $submitted !== null && hash_equals($expected, $submitted);
    }

    public function rotate(): void
    {
        $this->session['csrf_token'] = bin2hex(random_bytes(32));
    }

    public function field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . e($this->token()) . '">';
    }
}
