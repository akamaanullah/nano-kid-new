<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Setting extends Model
{
    public static function all(): array
    {
        $rows = self::many('SELECT setting_key, setting_value FROM settings');
        $out = [];
        foreach ($rows as $row) {
            $out[$row['setting_key']] = $row['setting_value'];
        }
        return $out;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $v = self::col('SELECT setting_value FROM settings WHERE setting_key = ?', [$key]);
        return $v === null ? $default : $v;
    }

    public static function set(string $key, string $value): void
    {
        self::q(
            'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
            [$key, $value]
        );
    }
}
