<?php

declare(strict_types=1);

function nk_env(string $key, ?string $default = null): ?string
{
    if (array_key_exists($key, $_ENV) && $_ENV[$key] !== null) {
        return (string)$_ENV[$key];
    }
    $v = getenv($key);
    if ($v === false) {
        return $default;
    }
    return $v;
}

function nk_load_dotenv(string $path): void
{
    if (!is_file($path) || !is_readable($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        return;
    }
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$k, $v] = explode('=', $line, 2);
        $k = trim($k);
        $v = trim($v);
        if ($k === '') {
            continue;
        }
        $len = strlen($v);
        if ($len >= 2) {
            $q = $v[0];
            if (($q === '"' || $q === "'") && $v[$len - 1] === $q) {
                $v = substr($v, 1, -1);
            }
        }
        if (getenv($k) === false && !array_key_exists($k, $_ENV)) {
            putenv($k . '=' . $v);
            $_ENV[$k] = $v;
        }
    }
}

function project_root(): string
{
    return dirname(__DIR__, 2);
}

function nk_request_is_https(): bool
{
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return true;
    }
    if ((int)($_SERVER['SERVER_PORT'] ?? 0) === 443) {
        return true;
    }
    $fwd = strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
    return $fwd === 'https';
}

function config(?string $key = null, mixed $default = null): mixed
{
    static $cfg = null;
    if ($cfg === null) {
        $file = project_root() . '/config.php';
        $cfg = is_file($file) ? require $file : [];
        if (!is_array($cfg)) {
            $cfg = [];
        }
    }
    if ($key === null) {
        return $cfg;
    }
    $cur = $cfg;
    foreach (explode('.', $key) as $part) {
        if (!is_array($cur) || !array_key_exists($part, $cur)) {
            return $default;
        }
        $cur = $cur[$part];
    }
    return $cur;
}

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function app_log(string $message, array $context = []): void
{
    $dir = project_root() . '/storage/logs';
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    $line = date('c') . ' ' . $message;
    if ($context) {
        $json = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $line .= ' ' . ($json !== false ? $json : '');
    }
    @file_put_contents($dir . '/app.log', $line . PHP_EOL, FILE_APPEND | LOCK_EX);
}

function url(string $path = ''): string
{
    $base = rtrim(\App\Core\Kernel::basePath(), '/');
    $path = trim($path);
    if ($path === '' || $path === '/') {
        return $base === '' ? '/' : $base . '/';
    }
    return $base . '/' . ltrim($path, '/');
}

function asset_url(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function upload_url(string $path): string
{
    return url('uploads/' . ltrim($path, '/'));
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(\App\Core\Csrf::token()) . '">';
}

function csrf_token(): string
{
    return \App\Core\Csrf::token();
}

function old(string $key, mixed $default = ''): string
{
    $flash = \App\Core\Session::get('_old', []);
    if (is_array($flash) && array_key_exists($key, $flash)) {
        return (string)$flash[$key];
    }
    return (string)$default;
}

function rs(float|int|string|null $amount): string
{
    $n = (float)$amount;
    return 'Rs. ' . number_format($n, 0, '.', ',');
}

function redirect_to(string $path, int $status = 302): never
{
    throw new \App\Core\HttpException($status, 'Redirect', url($path));
}

function setting(string $key, mixed $default = null): mixed
{
    static $all = null;
    if ($all === null) {
        try {
            $all = \App\Models\Setting::all();
        } catch (\Throwable $e) {
            $all = [];
        }
    }
    return $all[$key] ?? $default;
}

function setting_json(string $key, mixed $default = null): mixed
{
    $raw = setting($key, null);
    if ($raw === null || $raw === '') {
        return $default;
    }
    if (is_array($raw)) {
        return $raw;
    }
    $decoded = json_decode((string)$raw, true);
    return is_array($decoded) ? $decoded : $default;
}

function current_user(): ?array
{
    return $GLOBALS['nk_auth_user'] ?? null;
}

function is_admin(): bool
{
    $u = current_user();
    return $u && ($u['role'] ?? '') === 'admin';
}

function is_dealer(): bool
{
    $u = current_user();
    return $u && ($u['role'] ?? '') === 'dealer';
}

function flash(string $key, mixed $value = null): mixed
{
    if (func_num_args() === 1) {
        return \App\Core\Session::pullFlash($key);
    }
    \App\Core\Session::flash($key, $value);
    return null;
}

function store_boot(array $extra = []): array
{
    $user = current_user();
    $ages = [];
    foreach (\App\Models\AgeGroup::all() as $a) {
        $ages[] = ['key' => $a['slug'], 'label' => $a['label'], 'range' => $a['range_label'], 'image' => $a['image_url']];
    }
    $cats = [];
    foreach (\App\Models\Category::all() as $c) {
        $cats[] = ['key' => $c['slug'], 'label' => $c['label'], 'image' => $c['image_url']];
    }
    $boot = [
        'csrf' => csrf_token(),
        'base' => rtrim(\App\Core\Kernel::basePath(), '/'),
        'user' => $user ? [
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'business_name' => $user['business_name'],
        ] : null,
        'ages' => $ages,
        'cats' => $cats,
        'colors' => setting_json('colors', []),
        'seasons' => setting_json('seasons', []),
        'campaign' => setting_json('campaign', []),
        'contact' => setting_json('trade_contact', []),
        'manager' => setting_json('account_manager', []),
        'products' => \App\Models\Product::search([], 1, 500, 'featured')['items'],
    ];
    return array_merge($boot, $extra);
}

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? $text;
    return trim($text, '-') ?: 'item';
}

require_once __DIR__ . '/view.php';

function nk_is_local(): bool
{
    $env = strtolower((string)config('app.env', 'local'));
    return in_array($env, ['local', 'development', 'dev', 'test'], true);
}

function nk_setup_url(): string
{
    $base = rtrim(\App\Core\Kernel::basePath(), '/');
    return ($base === '' ? '' : $base) . '/setup.php';
}

function nk_is_database_error(\Throwable $e): bool
{
    $m = $e->getMessage();
    return str_contains($m, 'Unknown database')
        || str_contains($m, 'SQLSTATE[HY000] [1049]')
        || str_contains($m, 'SQLSTATE[HY000] [1045]')
        || str_contains($m, 'SQLSTATE[HY000] [2002]')
        || str_contains($m, 'SQLSTATE[HY000] [1044]')
        || str_contains($m, 'Access denied for user')
        || str_contains($m, 'Connection refused')
        || str_contains($m, 'No connection could be made');
}

function nk_fatal_html(int $status, string $message, ?\Throwable $error = null): string
{
    $isLocal = nk_is_local();
    $isSetupNeeded = $error ? nk_is_database_error($error) : (str_contains($message, 'Unknown database') || str_contains($message, '1049'));
    $setupUrl = nk_setup_url();
    $errorStatus = $status;
    $errorMessage = $message;
    $errorDetail = ($isLocal && $error) ? $error->getMessage() : null;
    $file = project_root() . '/views/errors/fatal.php';
    if (!is_file($file)) {
        $body = $isLocal && $errorDetail ? e($errorDetail) : e($message);
        return '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Error</title></head><body><p>' . $body . '</p></body></html>';
    }
    ob_start();
    require $file;
    return (string)ob_get_clean();
}

