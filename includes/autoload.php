<?php
declare(strict_types=1);

/**
 * Minimal PSR-0-ish autoloader: ClassName -> classes|models|controllers/ClassName.php.
 * Deliberately independent of Composer so the app runs where vendor/ is not deployed.
 */
spl_autoload_register(static function (string $class): void {
    foreach (['classes', 'models'] as $dir) {
        $file = dirname(__DIR__) . "/$dir/$class.php";
        if (is_file($file)) {
            require_once $file;
            return;
        }
    }
});
