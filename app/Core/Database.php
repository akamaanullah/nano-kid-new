<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }
        $host = (string)config('db.host', '127.0.0.1');
        $port = (int)config('db.port', 3306);
        $name = (string)config('db.name', 'nano_kids_wholesale');
        $user = (string)config('db.user', 'root');
        $pass = (string)config('db.pass', '');
        $charset = (string)config('db.charset', 'utf8mb4');
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $host, $port, $name, $charset);
        try {
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            $pdo->exec("SET time_zone = '+05:00'");
        } catch (PDOException $e) {
            app_log('db.connect_failed', ['error' => $e->getMessage()]);
            throw $e;
        }
        self::$pdo = $pdo;
        return $pdo;
    }

    public static function begin(): void
    {
        self::pdo()->beginTransaction();
    }

    public static function commit(): void
    {
        if (self::pdo()->inTransaction()) {
            self::pdo()->commit();
        }
    }

    public static function rollBack(): void
    {
        if (self::pdo()->inTransaction()) {
            self::pdo()->rollBack();
        }
    }
}
