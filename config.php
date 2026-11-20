<?php

declare(strict_types=1);

if (!function_exists('nk_load_dotenv')) {
    require_once __DIR__ . '/app/Helpers/functions.php';
}

nk_load_dotenv(__DIR__ . '/.env');

$cfg = [
    'app' => [
        'name' => 'Nano Kids Wholesale',
        'env' => nk_env('NK_APP_ENV', 'local') ?: 'local',
        'key' => nk_env('NK_APP_KEY', '') ?: '',
        'force_https' => in_array(strtolower((string)nk_env('NK_FORCE_HTTPS', 'false')), ['1', 'true', 'yes', 'on'], true),
        'timezone' => 'Asia/Karachi',
    ],
    'db' => [
        'host' => nk_env('NK_DB_HOST', '127.0.0.1') ?: '127.0.0.1',
        'port' => (int)(nk_env('NK_DB_PORT', '3306') ?: 3306),
        'name' => nk_env('NK_DB_NAME', 'nano_kids_wholesale') ?: 'nano_kids_wholesale',
        'user' => nk_env('NK_DB_USER', 'root') ?: 'root',
        'pass' => nk_env('NK_DB_PASS', '') ?? '',
        'charset' => 'utf8mb4',
    ],
    'session' => [
        'name' => 'nk_wholesale_sid',
    ],
];

$local = __DIR__ . '/config.local.php';
if (is_file($local)) {
    $override = require $local;
    if (is_array($override)) {
        $cfg = array_replace_recursive($cfg, $override);
    }
}

return $cfg;
