<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Order extends Model
{
    public static function nextReference(): string
    {
        $n = ((int)self::col('SELECT COUNT(*) FROM orders')) + 1;
        return 'ORD-' . date('ymd') . '-' . str_pad((string)$n, 3, '0', STR_PAD_LEFT);
    }

    public static function fromQuote(array $quote): int
    {
        self::q(
            'INSERT INTO orders (reference, quote_id, user_id, status, pieces, total, notes)
             VALUES (?,?,?,?,\'pending\',?,?)',
            [
                self::nextReference(),
                $quote['id'],
                $quote['user_id'] ?? null,
                (int)$quote['pieces'],
                (float)$quote['subtotal'],
                $quote['notes'] ?? null,
            ]
        );
        $id = (int)self::pdo()->lastInsertId();
        $items = $quote['items'] ?? Quote::items((int)$quote['id']);
        foreach ($items as $item) {
            self::q(
                'INSERT INTO order_items (order_id, product_id, sku, name, color, sizes_json, qty, unit_price, discount_pct, line_total)
                 VALUES (?,?,?,?,?,?,?,?,?,?)',
                [
                    $id,
                    $item['product_id'] ?? null,
                    $item['sku'] ?? null,
                    $item['name'],
                    $item['color'] ?? null,
                    $item['sizes_json'] ?? (isset($item['sizes']) ? json_encode($item['sizes']) : null),
                    (int)$item['qty'],
                    (float)$item['unit_price'],
                    (float)($item['discount_pct'] ?? 0),
                    (float)$item['line_total'],
                ]
            );
        }
        Quote::setStatus((int)$quote['id'], 'converted');
        return $id;
    }

    public static function find(int $id): ?array
    {
        $row = self::one('SELECT * FROM orders WHERE id = ?', [$id]);
        if (!$row) {
            return null;
        }
        $row['items'] = self::many('SELECT * FROM order_items WHERE order_id = ? ORDER BY id', [$id]);
        return $row;
    }

    public static function all(): array
    {
        return self::many('SELECT * FROM orders ORDER BY created_at DESC');
    }

    public static function forUser(int $userId): array
    {
        return self::many('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC', [$userId]);
    }

    public static function setStatus(int $id, string $status): void
    {
        self::q('UPDATE orders SET status = ? WHERE id = ?', [$status, $id]);
    }

    public static function countOpen(): int
    {
        return (int)self::col("SELECT COUNT(*) FROM orders WHERE status NOT IN ('delivered','cancelled')");
    }

    public static function countSince(string $since): int
    {
        return (int)self::col('SELECT COUNT(*) FROM orders WHERE created_at >= ?', [$since]);
    }

    public static function countBetween(string $from, string $to): int
    {
        return (int)self::col('SELECT COUNT(*) FROM orders WHERE created_at >= ? AND created_at < ?', [$from, $to]);
    }

    /** @return list<array{label:string,count:int}> */
    public static function weeklyCounts(int $weeks = 8): array
    {
        return \App\Models\Quote::weeklyFor('orders', $weeks);
    }
}
