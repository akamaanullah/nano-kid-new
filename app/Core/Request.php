<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    public function method(): string
    {
        $override = $_POST['_method'] ?? $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? null;
        if (is_string($override) && $override !== '') {
            return strtoupper($override);
        }
        return strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    }

    public function path(): string
    {
        $uri = parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
        $uri = is_string($uri) && $uri !== '' ? $uri : '/';
        $base = Kernel::basePath();
        if ($base !== '' && ($uri === $base || str_starts_with($uri, $base . '/'))) {
            $uri = substr($uri, strlen($base)) ?: '/';
        }
        if (str_starts_with($uri, '/public/')) {
            $uri = substr($uri, 7) ?: '/';
        }
        if ($uri !== '/' && str_ends_with($uri, '/')) {
            $uri = rtrim($uri, '/');
        }
        return $uri === '' ? '/' : $uri;
    }

    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    public function isAjax(): bool
    {
        $accept = (string)($_SERVER['HTTP_ACCEPT'] ?? '');
        $xr = strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));
        return $xr === 'xmlhttprequest' || str_contains($accept, 'application/json') || isset($_GET['ajax']) || isset($_GET['json']);
    }

    public function wantsJson(): bool
    {
        return $this->isAjax();
    }

    public function input(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $_POST)) {
            return $_POST[$key];
        }
        if (array_key_exists($key, $_GET)) {
            return $_GET[$key];
        }
        return $default;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($_GET, $_POST);
    }

    public function string(string $key, string $default = ''): string
    {
        $v = $this->input($key, $default);
        return is_string($v) ? trim($v) : (is_scalar($v) ? trim((string)$v) : $default);
    }

    public function int(string $key, int $default = 0): int
    {
        $v = $this->input($key, $default);
        return is_numeric($v) ? (int)$v : $default;
    }

    public function json(): array
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }
        $raw = file_get_contents('php://input');
        if (!is_string($raw) || trim($raw) === '') {
            $cached = [];
            return $cached;
        }
        $data = json_decode($raw, true);
        $cached = is_array($data) ? $data : [];
        return $cached;
    }

    public function ip(): string
    {
        return (string)($_SERVER['REMOTE_ADDR'] ?? '');
    }
}
