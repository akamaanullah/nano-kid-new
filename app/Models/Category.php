<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Category extends Model
{
    public static function all(bool $activeOnly = true): array
    {
        $sql = 'SELECT * FROM categories';
        if ($activeOnly) {
            $sql .= ' WHERE is_active = 1';
        }
        $sql .= ' ORDER BY sort_order, label';
        return self::many($sql);
    }

    public static function find(int $id): ?array
    {
        return self::one('SELECT * FROM categories WHERE id = ?', [$id]);
    }

    public static function findBySlug(string $slug): ?array
    {
        return self::one('SELECT * FROM categories WHERE slug = ?', [$slug]);
    }

    public static function create(array $data): int
    {
        self::q(
            'INSERT INTO categories (slug, label, image_url, sort_order, is_active) VALUES (?,?,?,?,?)',
            [$data['slug'], $data['label'], $data['image_url'] ?? null, (int)($data['sort_order'] ?? 0), (int)($data['is_active'] ?? 1)]
        );
        return (int)self::pdo()->lastInsertId();
    }

    public static function updateRow(int $id, array $data): void
    {
        self::q(
            'UPDATE categories SET slug=?, label=?, image_url=?, sort_order=?, is_active=? WHERE id=?',
            [$data['slug'], $data['label'], $data['image_url'] ?? null, (int)($data['sort_order'] ?? 0), (int)($data['is_active'] ?? 1), $id]
        );
    }
}
