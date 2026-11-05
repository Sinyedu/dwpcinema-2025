<?php
declare(strict_types=1);

/**
 * Accepts an uploaded avatar image, validates it by content (not by client-supplied name),
 * downsizes it, and re-encodes it (which strips any payload hidden in the original bytes).
 *
 * @throws RuntimeException with a user-safe message when the upload is refused
 */
class ImageUploader
{
    private const TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    private string $uploadDir;
    private int $maxWidth = 512;
    private int $maxHeight = 512;

    public function __construct(string $uploadDir = __DIR__ . '/../public/uploads/avatars/', private int $maxSize = 5242880)
    {
        $this->uploadDir = rtrim($uploadDir, '/\\') . '/';

        if (!is_dir($this->uploadDir) && !mkdir($this->uploadDir, 0755, true) && !is_dir($this->uploadDir)) {
            throw new RuntimeException('Upload directory is not available.');
        }
    }

    /** @return string path relative to public/, e.g. "uploads/avatars/1700000000_ab12cd34.png" */
    public function upload(array $file): string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('The file could not be uploaded.');
        }
        if ($file['size'] > $this->maxSize) {
            throw new RuntimeException('File too large. Maximum size is ' . round($this->maxSize / 1024 / 1024, 1) . 'MB.');
        }

        $mime = @(new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']); // false when unreadable
        $ext = is_string($mime) ? (self::TYPES[$mime] ?? null) : null;
        if ($ext === null) {
            throw new RuntimeException('Invalid file type. Use a JPG, PNG or WebP image.');
        }

        $size = @getimagesize($file['tmp_name']);
        if ($size === false || $size[0] < 1 || $size[1] < 1) {
            throw new RuntimeException('That file is not a valid image.');
        }
        [$width, $height] = $size;

        $image = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($file['tmp_name']),
            'image/png' => @imagecreatefrompng($file['tmp_name']),
            'image/webp' => @imagecreatefromwebp($file['tmp_name']),
        };
        if ($image === false) {
            throw new RuntimeException('That image could not be read.');
        }

        $ratio = min($this->maxWidth / $width, $this->maxHeight / $height, 1);
        $newWidth = max(1, (int)($width * $ratio));
        $newHeight = max(1, (int)($height * $ratio));

        $resized = imagecreatetruecolor($newWidth, $newHeight);
        if ($mime === 'image/png' || $mime === 'image/webp') {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
        }
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Random server-chosen name; the client's filename is never used.
        $fileName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $target = $this->uploadDir . $fileName;

        $saved = match ($mime) {
            'image/jpeg' => imagejpeg($resized, $target, 85),
            'image/png' => imagepng($resized, $target, 6),
            'image/webp' => imagewebp($resized, $target, 80),
        };

        imagedestroy($image);
        imagedestroy($resized);

        if (!$saved) {
            throw new RuntimeException('The image could not be saved.');
        }

        return 'uploads/avatars/' . $fileName;
    }
}
