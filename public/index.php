<?php

declare(strict_types=1);

use App\Core\Autoload;
use App\Core\Kernel;

$root = dirname(__DIR__);
require $root . '/app/Core/Autoload.php';
Autoload::register();

$env = (string)config('app.env', 'local');
ini_set('display_errors', '0');
ini_set('log_errors', '1');
$logFile = $root . '/storage/logs/php-error.log';
ini_set('error_log', $logFile);
error_reporting(E_ALL);

if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data: https:; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com data:; script-src 'self'; connect-src 'self'; frame-ancestors 'self'; base-uri 'self'; form-action 'self'");
}

try {
    $response = Kernel::handle();
    $response->send();
} catch (\Throwable $e) {
    app_log('bootstrap', ['error' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
    $status = nk_is_database_error($e) ? 503 : 500;
    http_response_code($status);
    header('Content-Type: text/html; charset=UTF-8');
    $message = nk_is_database_error($e)
        ? 'The catalogue database is not available yet.'
        : 'The trade desk is temporarily unavailable.';
    echo nk_fatal_html($status, $message, $e);
}
