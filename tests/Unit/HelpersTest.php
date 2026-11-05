<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class HelpersTest extends TestCase
{
    protected function tearDown(): void
    {
        $_POST = [];
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    public function testEscapesHtmlSpecialCharactersAndQuotes(): void
    {
        $this->assertSame(
            '&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt; &amp; &#039;quoted&#039;',
            e('<script>alert("x")</script> & \'quoted\'')
        );
    }

    public function testEscapesAttributeBreakouts(): void
    {
        $this->assertSame('&quot; onmouseover=&quot;alert(1)', e('" onmouseover="alert(1)'));
    }

    public function testDoesNotDoubleEscapeOnASecondPass(): void
    {
        // Escaping is for output only; calling it on already-escaped text escapes the ampersand again,
        // which is exactly why input must be stored raw.
        $this->assertSame('&amp;lt;', e('&lt;'));
    }

    public function testNullAndNumbersBecomeStrings(): void
    {
        $this->assertSame('', e(null));
        $this->assertSame('42', e(42));
        $this->assertSame('1.5', e(1.5));
    }

    public function testInvalidUtf8IsSubstitutedNotDropped(): void
    {
        // A stray 0xC3 is invalid UTF-8; it must become U+FFFD rather than make e() return an empty string.
        $this->assertSame("a\u{FFFD}(b", e("a\xC3\x28b"));
    }

    public function testPostStrTrimsAndDefaultsToEmpty(): void
    {
        $_POST = ['name' => "  Ada \n", 'list' => ['x']];

        $this->assertSame('Ada', post_str('name'));
        $this->assertSame('', post_str('missing'));
        $this->assertSame('', post_str('list'), 'arrays are not valid text');
    }

    public function testPostStrNeverHtmlEncodes(): void
    {
        $_POST = ['name' => "O'Brien & <Sons>"];

        $this->assertSame("O'Brien & <Sons>", post_str('name'));
    }

    public function testIsAjaxDetectsXmlHttpRequest(): void
    {
        $this->assertFalse(is_ajax());

        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->assertTrue(is_ajax());

        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'something-else';
        $this->assertFalse(is_ajax());
    }
}
