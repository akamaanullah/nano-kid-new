<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class ContactMessage extends Model
{
    public static function create(array $data): int
    {
        self::q(
            'INSERT INTO contact_messages (business_name, email, phone, message) VALUES (?,?,?,?)',
            [$data['business_name'], $data['email'], $data['phone'], $data['message']]
        );
        return (int)self::pdo()->lastInsertId();
    }

    public static function all(): array
    {
        return self::many('SELECT * FROM contact_messages ORDER BY created_at DESC');
    }

    public static function find(int $id): ?array
    {
        return self::one('SELECT * FROM contact_messages WHERE id = ?', [$id]);
    }

    public static function markRead(int $id): void
    {
        self::q('UPDATE contact_messages SET is_read = 1 WHERE id = ?', [$id]);
    }

    public static function countUnread(): int
    {
        return (int)self::col('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0');
    }
}
