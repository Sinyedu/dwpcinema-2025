<?php
declare(strict_types=1);

/** Escape a value for HTML output. Escape on output, never on input. */
function e(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Find an image for a tournament in $imgDir: first one named after the tournament,
 * then one named after its game. Returns a path relative to the site (e.g. "img/dota_2.jpg") or null.
 */
function tournament_image(string $tournamentName, string $gameName, string $imgDir): ?string
{
    $slug = static fn(string $s): string => strtolower(str_replace([' ', ':', '-', "'", '"'], '_', $s));

    foreach ([$slug($tournamentName), $slug($gameName)] as $base) {
        foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
            if (is_file(rtrim($imgDir, '/\\') . "/$base.$ext")) {
                return "img/$base.$ext";
            }
        }
    }
    return null;
}

/** A trimmed text field from $_POST. Missing or non-string values (e.g. arrays) become ''. Never HTML-encodes. */
function post_str(string $key): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? trim($value) : '';
}

/** True for fetch()/XHR requests made by our own JavaScript (they send X-Requested-With). */
function is_ajax(): bool
{
    return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
}
