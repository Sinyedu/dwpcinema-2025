<?php
declare(strict_types=1);

/** Guards user-supplied redirect targets (e.g. ?redirect=) against open redirects. */
final class SafeRedirect
{
    /** Return $target only if it is a same-site relative path; otherwise $default. */
    public static function local(?string $target, string $default): string
    {
        if ($target === null || $target === '' || $target !== trim($target)) {
            return $default;
        }
        if (str_contains($target, '\\') || preg_match('/[\x00-\x1F\x7F]/', $target)) {
            return $default;
        }
        if (str_starts_with($target, '//')) {
            return $default;
        }
        // A scheme (javascript:, https:, data:, foo:) appears before any / ? #
        if (preg_match('#^[^/?\#]*:#', $target)) {
            return $default;
        }
        return $target;
    }
}
