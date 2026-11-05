<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ImageUploaderTest extends TestCase
{
    private string $dir;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        if (!extension_loaded('gd')) {
            $this->markTestSkipped('gd extension not loaded (php -d extension=gd ...)');
        }
        $this->dir = sys_get_temp_dir() . '/dwp_uploads_' . bin2hex(random_bytes(4)) . '/';
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . '*') ?: [] as $f) {
            unlink($f);
        }
        if (is_dir($this->dir)) {
            rmdir($this->dir);
        }
        foreach ($this->tmpFiles as $f) {
            @unlink($f);
        }
    }

    private function upload(string $contents, string $clientName, int $error = UPLOAD_ERR_OK): array
    {
        $tmp = tempnam(sys_get_temp_dir(), 'up');
        file_put_contents($tmp, $contents);
        $this->tmpFiles[] = $tmp;
        return ['name' => $clientName, 'tmp_name' => $tmp, 'size' => strlen($contents), 'error' => $error];
    }

    private function png(int $w = 40, int $h = 40): string
    {
        $img = imagecreatetruecolor($w, $h);
        ob_start();
        imagepng($img);
        return ob_get_clean();
    }

    private function jpeg(): string
    {
        $img = imagecreatetruecolor(20, 20);
        ob_start();
        imagejpeg($img);
        return ob_get_clean();
    }

    private function uploader(int $max = 5242880): ImageUploader
    {
        return new ImageUploader($this->dir, $max);
    }

    public function testStoresAValidPngAndReturnsASiteRelativePath(): void
    {
        $path = $this->uploader()->upload($this->upload($this->png(), 'me.png'));

        $this->assertMatchesRegularExpression('#^uploads/avatars/[0-9]+_[a-f0-9]{8}\.png$#', $path);
        $this->assertFileExists($this->dir . basename($path));
    }

    public function testExtensionComesFromTheContentNotTheClientFilename(): void
    {
        $path = $this->uploader()->upload($this->upload($this->png(), 'shell.php'));

        $this->assertStringEndsWith('.png', $path);
        $this->assertStringNotContainsString('php', $path);
    }

    public function testJpegContentGetsAJpgExtensionEvenIfNamedPng(): void
    {
        $path = $this->uploader()->upload($this->upload($this->jpeg(), 'photo.png'));

        $this->assertStringEndsWith('.jpg', $path);
    }

    public function testRejectsNonImagesEvenWithAnImageExtension(): void
    {
        $this->expectException(RuntimeException::class);
        $this->uploader()->upload($this->upload('<?php system($_GET["c"]); ?>', 'avatar.png'));
    }

    public function testRejectsAPolyglotThatOnlyStartsLikeAnImage(): void
    {
        $this->expectException(RuntimeException::class);
        $this->uploader()->upload($this->upload("GIF89a<?php echo 1; ?>", 'avatar.gif'));
    }

    public function testRejectsOversizedFiles(): void
    {
        $this->expectException(RuntimeException::class);
        $this->uploader(max: 100)->upload($this->upload($this->png(200, 200), 'big.png'));
    }

    public function testRejectsPhpUploadErrors(): void
    {
        $this->expectException(RuntimeException::class);
        $this->uploader()->upload($this->upload('', 'x.png', UPLOAD_ERR_INI_SIZE));
    }

    public function testDownscalesLargeImages(): void
    {
        $path = $this->uploader()->upload($this->upload($this->png(2000, 1000), 'huge.png'));

        [$w, $h] = getimagesize($this->dir . basename($path));
        $this->assertLessThanOrEqual(512, $w);
        $this->assertLessThanOrEqual(512, $h);
    }

    public function testReEncodesSoEmbeddedPayloadsDoNotSurvive(): void
    {
        $png = $this->png();
        $withPayload = $png . '<?php echo "pwned"; ?>';

        $path = $this->uploader()->upload($this->upload($withPayload, 'a.png'));

        $this->assertStringNotContainsString('pwned', file_get_contents($this->dir . basename($path)));
    }
}
