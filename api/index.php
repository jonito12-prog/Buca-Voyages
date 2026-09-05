<?php

// Create writable storage directories in /tmp for Vercel Serverless environment
$storageDirs = [
    '/tmp/storage/app/public',
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/logs',
];

foreach ($storageDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

putenv('APP_CONFIG_CACHE=/tmp/storage/framework/cache/config.php');
putenv('APP_SERVICES_CACHE=/tmp/storage/framework/cache/services.php');
putenv('APP_PACKAGES_CACHE=/tmp/storage/framework/cache/packages.php');
putenv('APP_ROUTES_CACHE=/tmp/storage/framework/cache/routes.php');
putenv('VIEW_COMPILED_PATH=/tmp/storage/framework/views');

require __DIR__ . '/../public/index.php';
