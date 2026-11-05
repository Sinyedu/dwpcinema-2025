<?php
declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SafeRedirectTest extends TestCase
{
    #[DataProvider('safeTargets')]
    public function testAllowsLocalPaths(string $target): void
    {
        $this->assertSame($target, SafeRedirect::local($target, 'index.php'));
    }

    public static function safeTargets(): array
    {
        return [
            'plain page'        => ['showings.php'],
            'with query'        => ['view_showing.php?showingID=4'],
            'absolute path'     => ['/tournaments.php'],
            'nested'            => ['views/news.php?edit=2'],
        ];
    }

    #[DataProvider('unsafeTargets')]
    public function testFallsBackForAnythingThatCouldLeaveTheSite(?string $target): void
    {
        $this->assertSame('index.php', SafeRedirect::local($target, 'index.php'));
    }

    public static function unsafeTargets(): array
    {
        return [
            'null'                 => [null],
            'empty'                => [''],
            'absolute url'         => ['https://evil.example/phish'],
            'protocol relative'    => ['//evil.example'],
            'backslash trick'      => ['/\evil.example'],
            'javascript scheme'    => ['javascript:alert(1)'],
            'data scheme'          => ['data:text/html,x'],
            'embedded scheme'      => ['foo:bar'],
            'header injection LF'  => ["index.php\nSet-Cookie: x=1"],
            'header injection CR'  => ["index.php\rLocation: x"],
            'leading space'        => [' https://evil.example'],
        ];
    }
}
