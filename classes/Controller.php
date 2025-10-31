<?php
require_once __DIR__ . '/Database.php';

class Controller {
    protected $pdo;

    public function __construct($pdo = null) {
        $this->pdo = $pdo ?? Database::getInstance();
    }

    protected function model($model) {
        $path = __DIR__ . "/../models/{$model}.php";
        if (file_exists($path)) {
            require_once $path;
            return new $model($this->pdo);
        } else {
            throw new Exception("Model '{$model}' not found at {$path}");
        }
    }
}
