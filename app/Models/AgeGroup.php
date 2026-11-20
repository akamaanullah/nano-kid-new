<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class AgeGroup extends Model
{
    public static function all(bool $activeOnly = true): array
    {
        $sql = 'SELECT * FROM age_groups';
        if ($activeOnly) {
            $sql .= ' WHERE is_active = 1';
        }
        $sql .= ' ORDER BY sort_order, label';
        return self::many($sql);
    }

    public static function find(int $id): ?array
    {
        return self::one('SELECT * FROM age_groups WHERE id = ?', [$id]);
    }

    public static function findBySlug(string $slug): ?array
    {
        return self::one('SELECT * FROM age_groups WHERE slug = ?', [$slug]);
    }
}
