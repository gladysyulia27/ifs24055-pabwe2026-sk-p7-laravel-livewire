<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

foreach ([
    '/tmp/laravel-views',
    '/tmp/storage/app/public',
    '/tmp/storage/app/private/livewire-tmp',
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/logs',
] as $dir) {
    if (! is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

// Serve static build assets and /storage uploads from /tmp/storage/app/public on Vercel
$uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if (str_starts_with($uriPath, '/build/')) {
    $assetFile = __DIR__.'/../public'.$uriPath;
    if (is_file($assetFile)) {
        $ext = strtolower(pathinfo($assetFile, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'css' => 'text/css; charset=utf-8',
            'js', 'mjs' => 'application/javascript; charset=utf-8',
            'json' => 'application/json; charset=utf-8',
            'svg' => 'image/svg+xml',
            'woff2' => 'font/woff2',
            'woff' => 'font/woff',
            'ttf' => 'font/ttf',
            default => mime_content_type($assetFile) ?: 'application/octet-stream',
        };
        header("Content-Type: {$mime}");
        header('Cache-Control: public, max-age=31536000, immutable');
        readfile($assetFile);
        exit;
    }
} elseif (str_starts_with($uriPath, '/storage/')) {
    $relPath = ltrim(substr($uriPath, strlen('/storage/')), '/');
    $storageFile = '/tmp/storage/app/public/'.$relPath;
    if (! is_file($storageFile)) {
        $storageFile = __DIR__.'/../storage/app/public/'.$relPath;
    }
    if (is_file($storageFile)) {
        $mime = mime_content_type($storageFile) ?: 'application/octet-stream';
        header("Content-Type: {$mime}");
        header('Cache-Control: public, max-age=3600');
        readfile($storageFile);
        exit;
    }
}

$setEnvDefault = static function (string $key, string $value): void {
    $current = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    if ($current === false || $current === null || $current === '') {
        $_ENV[$key] = $_SERVER[$key] = $value;
        putenv("{$key}={$value}");
    }
};

$setEnvDefault('APP_ENV', 'production');
$setEnvDefault('APP_DEBUG', 'false');
$setEnvDefault('APP_KEY', 'base64:kVSsF3CcTU1MkwZ50uLSPH9KTTVbMImcQltFbZGS+1o=');
$setEnvDefault('LOG_CHANNEL', 'stderr');
$setEnvDefault('VIEW_COMPILED_PATH', '/tmp/laravel-views');
$setEnvDefault('FORCE_HTTPS', 'true');

$dbHost = $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? getenv('DB_HOST');
$useSqliteFallback = empty($dbHost) || $dbHost === '127.0.0.1' || $dbHost === 'localhost';

if ($useSqliteFallback) {
    $_ENV['DB_CONNECTION'] = $_SERVER['DB_CONNECTION'] = 'sqlite';
    putenv('DB_CONNECTION=sqlite');

    $sqlitePath = '/tmp/database.sqlite';
    if (! file_exists($sqlitePath)) {
        @touch($sqlitePath);
    }
    $_ENV['DB_DATABASE'] = $_SERVER['DB_DATABASE'] = $sqlitePath;
    putenv("DB_DATABASE={$sqlitePath}");

    $setEnvDefault('SESSION_DRIVER', 'cookie');
    $setEnvDefault('CACHE_STORE', 'array');
}

define('LARAVEL_START', microtime(true));

require __DIR__.'/../vendor/autoload.php';

/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->useStoragePath('/tmp/storage');

$request = Request::capture();

// Auto-migrate & seed ephemeral SQLite on first cold-start request on Vercel
if ($useSqliteFallback && (! file_exists('/tmp/.migrated') || @filesize('/tmp/database.sqlite') === 0)) {
    try {
        $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();
        if (! Schema::hasTable('users')) {
            Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]);
        }
        @touch('/tmp/.migrated');
    } catch (\Throwable) {
        // Ignore and allow request handling to proceed
    }
}

$app->handleRequest($request);
