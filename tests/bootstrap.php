<?php
declare(strict_types=1);

// Tests need pdo_sqlite (and gd for the avatar uploader). On Windows they are not
// enabled by default; run:  php -d extension=pdo_sqlite -d extension=gd vendor/bin/phpunit
foreach (['pdo_sqlite'] as $ext) {
    if (!extension_loaded($ext)) {
        fwrite(STDERR, "Missing PHP extension '$ext'. See tests/bootstrap.php.\n");
        exit(1);
    }
}

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../includes/autoload.php';
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/Support/TestDatabase.php';
require __DIR__ . '/Support/Users.php';
require __DIR__ . '/Support/Cinema.php';
