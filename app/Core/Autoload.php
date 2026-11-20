<?php

declare(strict_types=1);

namespace App\Core;

final class Autoload
{
    public static function register(): void
    {
        spl_autoload_register(static function (string $class): void {
            $prefix = 'App\\';
            if (!str_starts_with($class, $prefix)) {
                return;
            }
            $rel = str_replace('\\', '/', substr($class, strlen($prefix)));
            $file = dirname(__DIR__) . '/' . $rel . '.php';
            if (is_file($file)) {
                require $file;
            }
        });

        require_once dirname(__DIR__) . '/Helpers/functions.php';

        $tz = 'Asia/Karachi';
        try {
            $tz = (string)config('app.timezone', $tz);
        } catch (\Throwable $e) {
            // config may not be ready yet
        }
        date_default_timezone_set($tz ?: 'Asia/Karachi');

        $logDir = project_root() . '/storage/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0775, true);
        }
    }
}
