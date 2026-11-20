<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class PrivateLabel extends Model
{
    public static function create(array $data): int
    {
        self::q(
            'INSERT INTO private_label_requests (user_id, shop_name, email, phone, garment, mark, label_type, pack, color, qty, notes)
             VALUES (?,?,?,?,?,?,?,?,?,?,?)',
            [
                $data['user_id'] ?? null,
                $data['shop_name'],
                $data['email'] ?? null,
                $data['phone'] ?? null,
                $data['garment'],
                $data['mark'],
                $data['label_type'],
                $data['pack'],
                $data['color'] ?? null,
                (int)$data['qty'],
                $data['notes'] ?? null,
            ]
        );
        return (int)self::pdo()->lastInsertId();
    }

    public static function all(): array
    {
        return self::many('SELECT * FROM private_label_requests ORDER BY created_at DESC');
    }

    public static function find(int $id): ?array
    {
        return self::one('SELECT * FROM private_label_requests WHERE id = ?', [$id]);
    }

    public static function setStatus(int $id, string $status): void
    {
        self::q('UPDATE private_label_requests SET status = ? WHERE id = ?', [$status, $id]);
    }

    public static function countNew(): int
    {
        return (int)self::col("SELECT COUNT(*) FROM private_label_requests WHERE status = 'new'");
    }
}
