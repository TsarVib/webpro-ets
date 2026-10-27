<?php

use Illuminate\Contracts\Console\Kernel;

// Router for PHP's built-in server on Wasmer: serve built assets directly, send everything else to Laravel.

foreach (['LARAVEL_STORAGE_PATH', 'DB_DATABASE'] as $name) {
    if (($value = getenv($name)) !== false) {
        $_ENV[$name] = $_SERVER[$name] = $value;
    }
}

// Storage and the SQLite file live on the persistent volume, which starts empty.
$storage = $_ENV['LARAVEL_STORAGE_PATH'] ?? null;

if ($storage !== null && ! is_dir($storage.'/framework/views')) {
    foreach (['app/public', 'framework/cache/data', 'framework/sessions', 'framework/views', 'logs'] as $dir) {
        @mkdir($storage.'/'.$dir, 0775, true);
    }
}

// Migrate and seed once per schema version (build.sh stamps the migration list); both seeders are idempotent.
$stamp = trim((string) @file_get_contents(__DIR__.'/migrations.stamp'));
$marker = $storage !== null && $stamp !== '' ? $storage.'/migrated-'.$stamp : null;

if ($marker !== null && ! is_file($marker)) {
    $database = $_ENV['DB_DATABASE'] ?? null;

    if ($database !== null && ! is_file($database)) {
        touch($database);
    }

    require __DIR__.'/vendor/autoload.php';
    $app = require __DIR__.'/bootstrap/app.php';
    $app->make(Kernel::class)->call('migrate', ['--force' => true, '--seed' => true]);
    touch($marker);

    // public/index.php boots its own app instance, so serve this request fresh.
    header('Location: '.$_SERVER['REQUEST_URI'], true, 303);

    return;
}

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '');

if ($uri !== '/' && is_file(__DIR__.'/public'.$uri)) {
    return false;
}

require __DIR__.'/public/index.php';
