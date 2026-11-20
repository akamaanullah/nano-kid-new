<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOStatement;

abstract class Model
{
    protected static function pdo(): PDO
    {
        return Database::pdo();
    }

    protected static function q(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /** @return array<string, mixed>|null */
    protected static function one(string $sql, array $params = []): ?array
    {
        $row = self::q($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    /** @return list<array<string, mixed>> */
    protected static function many(string $sql, array $params = []): array
    {
        return self::q($sql, $params)->fetchAll();
    }

    protected static function col(string $sql, array $params = []): mixed
    {
        $v = self::q($sql, $params)->fetchColumn();
        return $v === false ? null : $v;
    }
}
