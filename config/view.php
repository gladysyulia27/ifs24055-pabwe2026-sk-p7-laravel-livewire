<?php

$defaultCompiledPath = (env('APP_ENV') === 'vercel' || isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL']))
    ? '/tmp/laravel-views'
    : storage_path('framework/views');

$compiledPath = env('VIEW_COMPILED_PATH') ?: $defaultCompiledPath;

if (is_string($compiledPath) && $compiledPath !== '' && ! is_dir($compiledPath)) {
    @mkdir($compiledPath, 0777, true);
}

return [
    'paths' => [
        resource_path('views'),
    ],
    'compiled' => $compiledPath,
];
