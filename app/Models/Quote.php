<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Quote extends Model
{
    public static function nextReference(): string
    {
        $n = ((int)self::col('SELECT COUNT(*) FROM quotes')) + 1;
        return 'RFQ-' . date('ymd') . '-' . str_pad((string)$n, 3, '0', STR_PAD_LEFT);
    }

    public static function create(array $data, array $items): int
    {
        self::q(
            'INSERT INTO quotes (reference, user_id, status, business_name, contact_name, email, phone, city, ntn, notes, pieces, subtotal)
             VALUES (:reference,:user_id,\'submitted\',:business_name,:contact_name,:email,:phone,:city,:ntn,:notes,:pieces,:subtotal)',
            [
                'reference' => $data['reference'],
                'user_id' => $data['user_id'] ?? null,
                'business_name' => $data['business_name'],
                'contact_name' => $data['contact_name'],
                'email' => mb_strtolower($data['email']),
                'phone' => $data['phone'],
                'city' => $data['city'],
                'ntn' => $data['ntn'] ?? null,
                'notes' => $data['notes'] ?? null,
                'pieces' => (int)$data['pieces'],
                'subtotal' => (float)$data['subtotal'],
            ]
        );
        $id = (int)self::pdo()->lastInsertId();
        foreach ($items as $item) {
            self::q(
                'INSERT INTO quote_items (quote_id, product_id, sku, name, color, sizes_json, qty, unit_price, discount_pct, line_total, moq, is_custom, custom_meta)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)',
                [
                    $id,
                    $item['product_id'] ?? null,
                    $item['sku'] ?? null,
                    $item['name'],
                    $item['color'] ?? null,
                    isset($item['sizes']) ? json_encode($item['sizes']) : ($item['sizes_json'] ?? null),
                    (int)$item['qty'],
                    (float)$item['unit_price'],
                    (float)($item['discount_pct'] ?? 0),
                    (float)$item['line_total'],
                    (int)($item['moq'] ?? 0),
                    !empty($item['is_custom']) ? 1 : 0,
                    isset($item['custom_meta']) ? (is_string($item['custom_meta']) ? $item['custom_meta'] : json_encode($item['custom_meta'])) : null,
                ]
            );
        }
        return $id;
    }

    public static function find(int $id): ?array
    {
        $row = self::one('SELECT * FROM quotes WHERE id = ?', [$id]);
        if (!$row) {
            return null;
        }
        $row['items'] = self::items($id);
        return $row;
    }

    public static function findByReference(string $ref): ?array
    {
        $row = self::one('SELECT * FROM quotes WHERE reference = ?', [$ref]);
        if (!$row) {
            return null;
        }
        $row['items'] = self::items((int)$row['id']);
        return $row;
    }

    public static function items(int $quoteId): array
    {
        $rows = self::many('SELECT * FROM quote_items WHERE quote_id = ? ORDER BY id', [$quoteId]);
        foreach ($rows as &$r) {
            $r['sizes'] = $r['sizes_json'] ? json_decode($r['sizes_json'], true) : [];
        }
        unset($r);
        return $rows;
    }

    public static function all(?string $status = null): array
    {
        if ($status) {
            return self::many('SELECT * FROM quotes WHERE status = ? ORDER BY created_at DESC', [$status]);
        }
        return self::many('SELECT * FROM quotes ORDER BY created_at DESC');
    }

    public static function forUser(int $userId): array
    {
        return self::many('SELECT * FROM quotes WHERE user_id = ? ORDER BY created_at DESC', [$userId]);
    }

    public static function setStatus(int $id, string $status): void
    {
        self::q('UPDATE quotes SET status = ? WHERE id = ?', [$status, $id]);
    }

    public static function countOpen(): int
    {
        return (int)self::col("SELECT COUNT(*) FROM quotes WHERE status IN ('submitted','reviewed','quoted')");
    }

    public static function countSince(string $since): int
    {
        return (int)self::col('SELECT COUNT(*) FROM quotes WHERE created_at >= ?', [$since]);
    }

    public static function countBetween(string $from, string $to): int
    {
        return (int)self::col('SELECT COUNT(*) FROM quotes WHERE created_at >= ? AND created_at < ?', [$from, $to]);
    }

    /** @return list<array{label:string,count:int}> */
    public static function weeklyCounts(int $weeks = 8): array
    {
        return self::weeklyFor('quotes', $weeks);
    }

    /** @return list<array{label:string,count:int}> */
    public static function weeklyFor(string $table, int $weeks = 8): array
    {
        $allowed = ['quotes' => 'quotes', 'orders' => 'orders'];
        $table = $allowed[$table] ?? 'quotes';
        $out = [];
        $start = new \DateTimeImmutable('monday this week', new \DateTimeZone('Asia/Karachi'));
        $start = $start->modify('-' . (($weeks - 1) * 7) . ' days');
        $end = $start->modify('+' . ($weeks * 7) . ' days');
        $rows = self::many(
            "SELECT DATE(created_at) AS d, COUNT(*) AS n FROM {$table} WHERE created_at >= ? AND created_at < ? GROUP BY DATE(created_at)",
            [$start->format('Y-m-d 00:00:00'), $end->format('Y-m-d 00:00:00')]
        );
        $byDay = [];
        foreach ($rows as $r) {
            $byDay[$r['d']] = (int)$r['n'];
        }
        for ($i = 0; $i < $weeks; $i++) {
            $weekStart = $start->modify('+' . ($i * 7) . ' days');
            $n = 0;
            for ($d = 0; $d < 7; $d++) {
                $key = $weekStart->modify('+' . $d . ' days')->format('Y-m-d');
                $n += $byDay[$key] ?? 0;
            }
            $out[] = ['label' => $weekStart->format('j M'), 'count' => $n];
        }
        return $out;
    }
}
