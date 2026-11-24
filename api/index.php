<?php

/**
 * Here is the serverless function entry
 * for deployment with Vercel.
 */

define('LARAVEL_START', microtime(true));

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Ejecutar migraciones automáticamente
try {
    $app->make('db')->connection()->statement('SELECT 1 FROM menu_imagenes LIMIT 1');
} catch (\Exception $e) {
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
}

require __DIR__.'/../public/index.php';
