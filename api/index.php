<?php

foreach (['/tmp/laravel-views', '/tmp/storage/framework/views', '/tmp/storage/framework/cache/data', '/tmp/storage/framework/sessions', '/tmp/storage/logs'] as $dir) {
    if (! is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

$dbHost = $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? getenv('DB_HOST');
if (empty($dbHost) || $dbHost === '127.0.0.1' || $dbHost === 'localhost') {
    $_ENV['DB_CONNECTION'] = $_SERVER['DB_CONNECTION'] = 'sqlite';
    putenv('DB_CONNECTION=sqlite');

    $sqlitePath = '/tmp/database.sqlite';
    if (! file_exists($sqlitePath)) {
        @touch($sqlitePath);
    }
    $_ENV['DB_DATABASE'] = $_SERVER['DB_DATABASE'] = $sqlitePath;
    putenv("DB_DATABASE={$sqlitePath}");

    if (empty($_ENV['SESSION_DRIVER'] ?? $_SERVER['SESSION_DRIVER'] ?? getenv('SESSION_DRIVER'))) {
        $_ENV['SESSION_DRIVER'] = $_SERVER['SESSION_DRIVER'] = 'cookie';
        putenv('SESSION_DRIVER=cookie');
    }
    if (empty($_ENV['CACHE_STORE'] ?? $_SERVER['CACHE_STORE'] ?? getenv('CACHE_STORE'))) {
        $_ENV['CACHE_STORE'] = $_SERVER['CACHE_STORE'] = 'array';
        putenv('CACHE_STORE=array');
    }
}

// All dynamic requests are forwarded to Laravel's public front controller.
require __DIR__.'/../public/index.php';
