<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Application extends Model
{
    public static function create(array $data): int
    {
        self::q(
            'INSERT INTO applications (business_name, contact_name, email, phone, city, ntn, shop_type, monthly_volume, notes, status)
             VALUES (?,?,?,?,?,?,?,?,?,\'pending\')',
            [
                $data['business_name'],
                $data['contact_name'],
                mb_strtolower($data['email']),
                $data['phone'],
                $data['city'],
                $data['ntn'],
                $data['shop_type'],
                $data['monthly_volume'],
                $data['notes'] ?? null,
            ]
        );
        return (int)self::pdo()->lastInsertId();
    }

    public static function find(int $id): ?array
    {
        return self::one('SELECT * FROM applications WHERE id = ?', [$id]);
    }

    public static function all(?string $status = null): array
    {
        if ($status) {
            return self::many('SELECT * FROM applications WHERE status = ? ORDER BY created_at DESC', [$status]);
        }
        return self::many('SELECT * FROM applications ORDER BY FIELD(status,\'pending\',\'approved\',\'rejected\'), created_at DESC');
    }

    public static function countPending(): int
    {
        return (int)self::col("SELECT COUNT(*) FROM applications WHERE status = 'pending'");
    }

    public static function reject(int $id, int $adminId): void
    {
        self::q(
            "UPDATE applications SET status = 'rejected', reviewed_at = NOW(), reviewed_by = ? WHERE id = ?",
            [$adminId, $id]
        );
    }

    public static function markApproved(int $id, int $adminId, int $userId): void
    {
        self::q(
            "UPDATE applications SET status = 'approved', reviewed_at = NOW(), reviewed_by = ?, user_id = ? WHERE id = ?",
            [$adminId, $userId, $id]
        );
    }
}
