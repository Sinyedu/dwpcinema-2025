<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class CsrfTest extends TestCase
{
    public function testTokenIsStableWithinASession(): void
    {
        $session = [];
        $csrf = new Csrf($session);

        $this->assertSame($csrf->token(), $csrf->token());
        $this->assertSame(64, strlen($csrf->token()));
    }

    public function testValidatesTheIssuedToken(): void
    {
        $session = [];
        $csrf = new Csrf($session);

        $this->assertTrue($csrf->validate($csrf->token()));
    }

    public function testRejectsMissingEmptyAndWrongTokens(): void
    {
        $session = [];
        $csrf = new Csrf($session);
        $csrf->token();

        $this->assertFalse($csrf->validate(null));
        $this->assertFalse($csrf->validate(''));
        $this->assertFalse($csrf->validate('nope'));
    }

    public function testNoTokenIssuedYetMeansNothingValidates(): void
    {
        $session = [];
        $csrf = new Csrf($session);

        $this->assertFalse($csrf->validate(''));
        $this->assertFalse($csrf->validate('anything'));
    }

    public function testRotateInvalidatesTheOldToken(): void
    {
        $session = [];
        $csrf = new Csrf($session);
        $old = $csrf->token();

        $csrf->rotate();

        $this->assertFalse($csrf->validate($old));
        $this->assertNotSame($old, $csrf->token());
    }

    public function testFieldRendersAnEscapedHiddenInput(): void
    {
        $session = [];
        $csrf = new Csrf($session);

        $this->assertSame(
            '<input type="hidden" name="csrf_token" value="' . $csrf->token() . '">',
            $csrf->field()
        );
    }

    public function testSharesStateWithTheSessionArray(): void
    {
        $session = [];
        $csrf = new Csrf($session);
        $token = $csrf->token();

        $this->assertSame($token, $session['csrf_token']);
    }
}
