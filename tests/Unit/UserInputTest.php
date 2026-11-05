<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class UserInputTest extends TestCase
{
    private function valid(array $over = []): array
    {
        return array_merge([
            'firstName' => 'Ada', 'lastName' => 'Lovelace',
            'email' => 'ada@example.com', 'password' => 'long-enough-pw',
        ], $over);
    }

    public function testValidRegistrationReturnsCleanData(): void
    {
        $result = UserInput::registration($this->valid(['firstName' => '  Ada ']));

        $this->assertSame([], $result['errors']);
        $this->assertSame('Ada', $result['data']['firstName']);
    }

    public function testDoesNotHtmlEncodeInput(): void
    {
        $result = UserInput::registration($this->valid(['lastName' => "O'Brien & <Sons>"]));

        $this->assertSame("O'Brien & <Sons>", $result['data']['lastName']);
    }

    public function testPasswordIsNeverTrimmedOrAltered(): void
    {
        $result = UserInput::registration($this->valid(['password' => '  a&b<c>"\'  x ']));

        $this->assertSame('  a&b<c>"\'  x ', $result['data']['password']);
    }

    public function testRejectsBadEmail(): void
    {
        $result = UserInput::registration($this->valid(['email' => 'not-an-email']));

        $this->assertArrayHasKey('email', $result['errors']);
    }

    public function testRejectsShortPassword(): void
    {
        $result = UserInput::registration($this->valid(['password' => 'short']));

        $this->assertArrayHasKey('password', $result['errors']);
    }

    public function testRejectsPasswordsBcryptWouldSilentlyTruncate(): void
    {
        $result = UserInput::registration($this->valid(['password' => str_repeat('a', 73)]));

        $this->assertArrayHasKey('password', $result['errors']);
    }

    public function testRejectsMissingAndOverlongNames(): void
    {
        $result = UserInput::registration($this->valid(['firstName' => '   ', 'lastName' => str_repeat('x', 101)]));

        $this->assertArrayHasKey('firstName', $result['errors']);
        $this->assertArrayHasKey('lastName', $result['errors']);
    }

    public function testMissingKeysAreErrorsNotNotices(): void
    {
        $result = UserInput::registration([]);

        $this->assertCount(4, $result['errors']);
    }

    public function testNonStringValuesAreRejectedNotCrashed(): void
    {
        $result = UserInput::registration($this->valid(['firstName' => ['array']]));

        $this->assertArrayHasKey('firstName', $result['errors']);
    }

    public function testProfileDoesNotRequireAPassword(): void
    {
        $result = UserInput::profile(['firstName' => 'Ada', 'lastName' => 'L', 'email' => 'ada@example.com']);

        $this->assertSame([], $result['errors']);
        $this->assertArrayNotHasKey('password', $result['data']);
    }
}
