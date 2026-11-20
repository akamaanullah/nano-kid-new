<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class User extends Model
{
    public static function find(int $id): ?array
    {
        return self::one('SELECT * FROM users WHERE id = ?', [$id]);
    }

    public static function findByEmail(string $email): ?array
    {
        return self::one('SELECT * FROM users WHERE email = ?', [mb_strtolower($email)]);
    }

    public static function allDealers(): array
    {
        return self::many("SELECT * FROM users WHERE role = 'dealer' ORDER BY created_at DESC");
    }

    public static function create(array $data): int
    {
        self::q(
            'INSERT INTO users (role, email, password_hash, name, business_name, phone, city, ntn, shop_type, monthly_volume, is_active, application_id)
             VALUES (:role, :email, :password_hash, :name, :business_name, :phone, :city, :ntn, :shop_type, :monthly_volume, :is_active, :application_id)',
            [
                'role' => $data['role'] ?? 'dealer',
                'email' => mb_strtolower((string)$data['email']),
                'password_hash' => $data['password_hash'],
                'name' => $data['name'],
                'business_name' => $data['business_name'] ?? null,
                'phone' => $data['phone'] ?? null,
                'city' => $data['city'] ?? null,
                'ntn' => $data['ntn'] ?? null,
                'shop_type' => $data['shop_type'] ?? null,
                'monthly_volume' => $data['monthly_volume'] ?? null,
                'is_active' => $data['is_active'] ?? 1,
                'application_id' => $data['application_id'] ?? null,
            ]
        );
        return (int)self::pdo()->lastInsertId();
    }

    public static function touchLogin(int $id): void
    {
        self::q('UPDATE users SET last_login_at = NOW() WHERE id = ?', [$id]);
    }

    public static function setActive(int $id, bool $active): void
    {
        self::q('UPDATE users SET is_active = ? WHERE id = ?', [$active ? 1 : 0, $id]);
    }
}
