<?php
class ImageUploader
{
    private array $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
    private array $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
    private int $maxSize;
    private string $uploadDir;
    private int $maxWidth = 512;
    private int $maxHeight = 512;

    public function __construct(string $uploadDir = __DIR__ . '/../public/uploads/avatars/', int $maxSize = 5242880)
    {
        $this->uploadDir = rtrim($uploadDir, '/') . '/';
        $this->maxSize = $maxSize;

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    public function upload(array $file): string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload error (code {$file['error']}).");
        }

        if ($file['size'] > $this->maxSize) {
            throw new Exception("File too large. Maximum size is " . ($this->maxSize / 1024 / 1024) . "MB.");
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!in_array($mime, $this->allowedMime)) {
            throw new Exception("Invalid file type.");
        }

        $safeName = preg_replace("/[^a-zA-Z0-9_\.-]/", "_", basename($file['name']));
        $ext = strtolower(pathinfo($safeName, PATHINFO_EXTENSION));

        if (!in_array($ext, $this->allowedExtensions)) {
            throw new Exception("Unsupported file extension.");
        }

        [$width, $height] = getimagesize($file['tmp_name']);
        $ratio = min($this->maxWidth / $width, $this->maxHeight / $height, 1);
        $newWidth = (int)($width * $ratio);
        $newHeight = (int)($height * $ratio);

        $image = match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($file['tmp_name']),
            'image/png'  => imagecreatefrompng($file['tmp_name']),
            'image/webp' => imagecreatefromwebp($file['tmp_name']),
            default      => throw new Exception("Unsupported image type."),
        };

        $resized = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        $newFileName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $targetPath = $this->uploadDir . $newFileName;

        switch ($mime) {
            case 'image/jpeg':
                imagejpeg($resized, $targetPath, 85);
                break;
            case 'image/png':
                imagepng($resized, $targetPath, 6);
                break;
            case 'image/webp':
                imagewebp($resized, $targetPath, 80);
                break;
        }

        imagedestroy($image);
        imagedestroy($resized);

        return "uploads/avatars/" . $newFileName;
    }
}
