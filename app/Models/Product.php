<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Product extends Model
{
    public static function find(int $id): ?array
    {
        $row = self::one(
            'SELECT p.*, c.slug AS category, c.label AS category_label,
                    a.slug AS age_group, a.label AS age_label, a.range_label
             FROM products p
             JOIN categories c ON c.id = p.category_id
             JOIN age_groups a ON a.id = p.age_group_id
             WHERE p.id = ?',
            [$id]
        );
        return $row ? self::hydrate($row) : null;
    }

    public static function findBySlug(string $slug, bool $activeOnly = true): ?array
    {
        $sql = 'SELECT p.*, c.slug AS category, c.label AS category_label,
                       a.slug AS age_group, a.label AS age_label, a.range_label
                FROM products p
                JOIN categories c ON c.id = p.category_id
                JOIN age_groups a ON a.id = p.age_group_id
                WHERE p.slug = ?';
        $params = [$slug];
        if ($activeOnly) {
            $sql .= ' AND p.is_active = 1';
        }
        $row = self::one($sql, $params);
        return $row ? self::hydrate($row) : null;
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{items:list<array>,total:int,page:int,pages:int,per:int}
     */
    public static function search(array $filters, int $page = 1, int $per = 12, string $sort = 'featured'): array
    {
        $where = ['p.is_active = 1'];
        $params = [];
        if (!empty($filters['q'])) {
            $where[] = '(p.name LIKE ? OR p.sku LIKE ? OR p.description LIKE ?)';
            $q = '%' . $filters['q'] . '%';
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
        }
        if (!empty($filters['category'])) {
            $where[] = 'c.slug = ?';
            $params[] = $filters['category'];
        }
        if (!empty($filters['age']) || !empty($filters['age_group'])) {
            $where[] = 'a.slug = ?';
            $params[] = $filters['age'] ?? $filters['age_group'];
        }
        if (!empty($filters['season'])) {
            $where[] = 'p.season = ?';
            $params[] = $filters['season'];
        }
        if (!empty($filters['status']) || !empty($filters['stock_status'])) {
            $where[] = 'p.stock_status = ?';
            $params[] = $filters['status'] ?? $filters['stock_status'];
        }
        if (!empty($filters['fabric'])) {
            $where[] = 'p.fabric = ?';
            $params[] = $filters['fabric'];
        }
        if (!empty($filters['moq'])) {
            $where[] = 'p.moq = ?';
            $params[] = (int)$filters['moq'];
        }
        if (isset($filters['min']) && $filters['min'] !== '' && $filters['min'] !== null && (float)$filters['min'] > 0) {
            $where[] = 'p.base_price >= ?';
            $params[] = (float)$filters['min'];
        }
        if (isset($filters['max']) && $filters['max'] !== '' && $filters['max'] !== null && (float)$filters['max'] > 0) {
            $where[] = 'p.base_price <= ?';
            $params[] = (float)$filters['max'];
        }
        $clause = implode(' AND ', $where);

        $total = (int)self::col(
            "SELECT COUNT(*) FROM products p
             JOIN categories c ON c.id = p.category_id
             JOIN age_groups a ON a.id = p.age_group_id
             WHERE {$clause}",
            $params
        );

        $order = match ($sort) {
            'price-asc', 'price_asc' => 'p.base_price ASC, p.name ASC',
            'price-desc', 'price_desc' => 'p.base_price DESC, p.name ASC',
            'name', 'az' => 'p.name ASC',
            'moq' => 'p.moq ASC, p.name ASC',
            default => 'p.id ASC',
        };

        $page = max(1, $page);
        $pages = max(1, (int)ceil($total / $per));
        if ($page > $pages) {
            $page = $pages;
        }
        $offset = ($page - 1) * $per;

        $rows = self::many(
            "SELECT p.*, c.slug AS category, c.label AS category_label,
                    a.slug AS age_group, a.label AS age_label, a.range_label
             FROM products p
             JOIN categories c ON c.id = p.category_id
             JOIN age_groups a ON a.id = p.age_group_id
             WHERE {$clause}
             ORDER BY {$order}
             LIMIT {$per} OFFSET {$offset}",
            $params
        );

        $items = array_map([self::class, 'hydrate'], $rows);
        return compact('items', 'total', 'page', 'pages', 'per');
    }

    public static function featured(int $limit = 8): array
    {
        $rows = self::many(
            "SELECT p.*, c.slug AS category, c.label AS category_label,
                    a.slug AS age_group, a.label AS age_label, a.range_label
             FROM products p
             JOIN categories c ON c.id = p.category_id
             JOIN age_groups a ON a.id = p.age_group_id
             WHERE p.is_active = 1
             ORDER BY p.id ASC
             LIMIT {$limit}"
        );
        return array_map([self::class, 'hydrate'], $rows);
    }

    public static function related(array $product, int $limit = 4): array
    {
        $rows = self::many(
            "SELECT p.*, c.slug AS category, c.label AS category_label,
                    a.slug AS age_group, a.label AS age_label, a.range_label
             FROM products p
             JOIN categories c ON c.id = p.category_id
             JOIN age_groups a ON a.id = p.age_group_id
             WHERE p.is_active = 1 AND p.id != ? AND (p.category_id = ? OR p.age_group_id = ?)
             ORDER BY p.category_id = ? DESC, p.id ASC
             LIMIT {$limit}",
            [$product['id'], $product['category_id'], $product['age_group_id'], $product['category_id']]
        );
        return array_map([self::class, 'hydrate'], $rows);
    }

    public static function allAdmin(): array
    {
        return self::many(
            "SELECT p.*, c.label AS category_label, a.label AS age_label
             FROM products p
             JOIN categories c ON c.id = p.category_id
             JOIN age_groups a ON a.id = p.age_group_id
             ORDER BY p.id DESC"
        );
    }

    public static function countActive(): int
    {
        return (int)self::col('SELECT COUNT(*) FROM products WHERE is_active = 1');
    }

    public static function create(array $data): int
    {
        self::q(
            'INSERT INTO products (sku, name, slug, category_id, age_group_id, season, fabric, gsm, moq, base_price, lead_time_days, stock_status, description, tag, pack_units, pack_ratio, is_active)
             VALUES (:sku,:name,:slug,:category_id,:age_group_id,:season,:fabric,:gsm,:moq,:base_price,:lead_time_days,:stock_status,:description,:tag,:pack_units,:pack_ratio,:is_active)',
            self::bind($data)
        );
        return (int)self::pdo()->lastInsertId();
    }

    public static function updateRow(int $id, array $data): void
    {
        $bind = self::bind($data);
        $bind['id'] = $id;
        self::q(
            'UPDATE products SET sku=:sku, name=:name, slug=:slug, category_id=:category_id, age_group_id=:age_group_id,
             season=:season, fabric=:fabric, gsm=:gsm, moq=:moq, base_price=:base_price, lead_time_days=:lead_time_days,
             stock_status=:stock_status, description=:description, tag=:tag, pack_units=:pack_units, pack_ratio=:pack_ratio,
             is_active=:is_active WHERE id=:id',
            $bind
        );
    }

    public static function deleteRow(int $id): void
    {
        self::q('DELETE FROM products WHERE id = ?', [$id]);
    }

    public static function syncRelations(int $productId, array $images, array $sizes, array $colors, array $tiers): void
    {
        self::q('DELETE FROM product_images WHERE product_id = ?', [$productId]);
        self::q('DELETE FROM product_sizes WHERE product_id = ?', [$productId]);
        self::q('DELETE FROM product_colors WHERE product_id = ?', [$productId]);
        self::q('DELETE FROM price_tiers WHERE product_id = ?', [$productId]);

        foreach (array_values($images) as $i => $url) {
            $url = trim((string)$url);
            if ($url === '') {
                continue;
            }
            self::q('INSERT INTO product_images (product_id, url, sort_order) VALUES (?,?,?)', [$productId, $url, $i]);
        }
        foreach (array_values($sizes) as $i => $size) {
            $size = trim((string)$size);
            if ($size === '') {
                continue;
            }
            self::q('INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (?,?,?)', [$productId, $size, $i]);
        }
        $palette = setting_json('colors', []);
        foreach (array_values($colors) as $i => $key) {
            $key = trim((string)$key);
            if ($key === '') {
                continue;
            }
            $meta = is_array($palette) && isset($palette[$key]) ? $palette[$key] : [];
            $name = $meta['n'] ?? $meta['name'] ?? $key;
            $hex = $meta['h'] ?? $meta['hex'] ?? null;
            self::q(
                'INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (?,?,?,?,?)',
                [$productId, $key, $name, $hex, $i]
            );
        }
        foreach ($tiers as $t) {
            $min = (int)($t['min'] ?? 0);
            if ($min <= 0) {
                continue;
            }
            $max = isset($t['max']) && $t['max'] !== '' && $t['max'] !== null ? (int)$t['max'] : null;
            $off = (float)($t['discount'] ?? 0);
            self::q(
                'INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (?,?,?,?)',
                [$productId, $min, $max, $off]
            );
        }
    }

    public static function uniqueSlug(string $slug, ?int $ignoreId = null): string
    {
        $base = $slug !== '' ? $slug : 'product';
        $try = $base;
        $n = 2;
        while (true) {
            $sql = 'SELECT id FROM products WHERE slug = ?';
            $params = [$try];
            if ($ignoreId) {
                $sql .= ' AND id != ?';
                $params[] = $ignoreId;
            }
            if (!self::one($sql, $params)) {
                return $try;
            }
            $try = $base . '-' . $n;
            $n++;
        }
    }

    public static function uniqueSku(string $sku, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT id FROM products WHERE sku = ?';
        $params = [$sku];
        if ($ignoreId) {
            $sql .= ' AND id != ?';
            $params[] = $ignoreId;
        }
        return self::one($sql, $params) === null;
    }

    public static function slimCatalog(): array
    {
        $rows = self::many(
            "SELECT p.id, p.sku, p.name, p.slug, p.moq, p.base_price, p.stock_status, p.tag,
                    c.slug AS category, a.slug AS age_group
             FROM products p
             JOIN categories c ON c.id = p.category_id
             JOIN age_groups a ON a.id = p.age_group_id
             WHERE p.is_active = 1
             ORDER BY p.name"
        );
        $ids = array_column($rows, 'id');
        $images = self::indexImages($ids);
        foreach ($rows as &$row) {
            $row['id'] = (int)$row['id'];
            $row['moq'] = (int)$row['moq'];
            $row['base_price'] = (float)$row['base_price'];
            $imgs = $images[(int)$row['id']] ?? [];
            $row['image'] = $imgs[0] ?? '';
            $row['images'] = $imgs;
        }
        unset($row);
        return $rows;
    }

    public static function hydrate(array $row): array
    {
        $id = (int)$row['id'];
        $row['id'] = $id;
        $row['moq'] = (int)$row['moq'];
        $row['base_price'] = (float)$row['base_price'];
        $row['gsm'] = $row['gsm'] !== null ? (int)$row['gsm'] : null;
        $row['lead_time_days'] = (int)$row['lead_time_days'];
        $row['pack_units'] = $row['pack_units'] !== null ? (int)$row['pack_units'] : null;
        $row['is_active'] = (int)$row['is_active'];
        $row['images'] = array_column(
            self::many('SELECT url FROM product_images WHERE product_id = ? ORDER BY sort_order, id', [$id]),
            'url'
        );
        $row['sizes'] = array_column(
            self::many('SELECT size_label FROM product_sizes WHERE product_id = ? ORDER BY sort_order, id', [$id]),
            'size_label'
        );
        $colorRows = self::many(
            'SELECT color_key, color_name, hex FROM product_colors WHERE product_id = ? ORDER BY sort_order, id',
            [$id]
        );
        $row['colors'] = array_column($colorRows, 'color_key');
        $row['color_meta'] = $colorRows;
        $tierRows = self::many(
            'SELECT min_qty AS `min`, max_qty AS `max`, discount_pct AS discount FROM price_tiers WHERE product_id = ? ORDER BY min_qty, id',
            [$id]
        );
        $row['tiers'] = array_map(static function (array $t): array {
            return [
                'min' => (int)$t['min'],
                'max' => $t['max'] === null ? null : (int)$t['max'],
                'discount' => (float)$t['discount'],
            ];
        }, $tierRows);
        return $row;
    }

    /** @param list<int|string> $ids */
    private static function indexImages(array $ids): array
    {
        if (!$ids) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $rows = self::many(
            "SELECT product_id, url FROM product_images WHERE product_id IN ({$placeholders}) ORDER BY sort_order, id",
            array_values($ids)
        );
        $out = [];
        foreach ($rows as $r) {
            $out[(int)$r['product_id']][] = $r['url'];
        }
        return $out;
    }

    private static function bind(array $data): array
    {
        return [
            'sku' => $data['sku'],
            'name' => $data['name'],
            'slug' => $data['slug'],
            'category_id' => (int)$data['category_id'],
            'age_group_id' => (int)$data['age_group_id'],
            'season' => $data['season'] ?? 'core',
            'fabric' => $data['fabric'] ?? null,
            'gsm' => $data['gsm'] !== '' && $data['gsm'] !== null ? (int)$data['gsm'] : null,
            'moq' => (int)$data['moq'],
            'base_price' => (float)$data['base_price'],
            'lead_time_days' => (int)($data['lead_time_days'] ?? 7),
            'stock_status' => $data['stock_status'] ?? 'in_stock',
            'description' => $data['description'] ?? null,
            'tag' => $data['tag'] ?? null,
            'pack_units' => !empty($data['pack_units']) ? (int)$data['pack_units'] : null,
            'pack_ratio' => $data['pack_ratio'] ?? null,
            'is_active' => (int)($data['is_active'] ?? 1),
        ];
    }
}
