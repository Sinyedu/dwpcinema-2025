<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    public function testBuildsDatabaseSettingsFromEnvironment(): void
    {
        $config = Config::fromEnv(['DB_HOST' => 'db', 'DB_NAME' => 'cinema', 'DB_USER' => 'app', 'DB_PASSWORD' => 's3cret']);

        $this->assertSame('db', $config['db']['host']);
        $this->assertSame('cinema', $config['db']['name']);
        $this->assertSame('app', $config['db']['user']);
        $this->assertSame('s3cret', $config['db']['password']);
        $this->assertSame('utf8mb4', $config['db']['charset']);
    }

    public function testMailPortIsAnInteger(): void
    {
        $this->assertSame(2525, Config::fromEnv(['MAIL_PORT' => '2525'])['email']['smtp_port']);
        $this->assertSame(587, Config::fromEnv([])['email']['smtp_port']);
    }
}
