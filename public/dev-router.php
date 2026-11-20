<?php

declare(strict_types=1);

/**
 * Router script for `php -S localhost:8787 -t public public/dev-router.php`
 */
if (PHP_SAPI !== 'cli-server') {
    return false;
}

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$path = __DIR__ . $uri;
if ($uri !== '/' && is_file($path)) {
    return false; // serve the static file
}

require __DIR__ . '/index.php';
