<?php

/**
 * Here is the serverless function entry
 * for deployment with Vercel.
 */

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->call('migrate', ['--force' => true]);

require __DIR__.'/../public/index.php';
