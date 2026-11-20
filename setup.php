<?php

declare(strict_types=1);

/**
 * One-time installer: creates the database (if needed) and imports database/schema.sql.
 * Local / development: open this file in the browser and confirm, or run `php setup.php`.
 * Production: requires NK_SETUP_TOKEN (16+ characters) as ?token= or --token=
 * After a successful run, storage/setup.lock is written and further visits 404.
 */

$root = __DIR__;
require $root . '/app/Core/Autoload.php';
App\Core\Autoload::register();

$env = (string)config('app.env', 'local');
$lock = $root . '/storage/setup.lock';
$isCli = PHP_SAPI === 'cli';

function setup_fail(string $message, int $status = 500): void
{
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, $message . PHP_EOL);
        exit(1);
    }
    http_response_code($status);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><title>Setup</title>';
    echo '<style>body{font-family:Manrope,system-ui,sans-serif;background:#FFFDF9;color:#1B2A41;padding:48px 24px;max-width:40rem;margin:auto}a{color:#8F5E0E}</style></head><body>';
    echo '<h1>Setup could not finish</h1><p>' . htmlspecialchars($message, ENT_QUOTES) . '</p>';
    echo '</body></html>';
    exit;
}

function setup_ok(string $message): void
{
    if (PHP_SAPI === 'cli') {
        echo $message . PHP_EOL;
        exit(0);
    }
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><title>Setup complete</title>';
    echo '<style>body{font-family:Manrope,system-ui,sans-serif;background:#FFFDF9;color:#1B2A41;padding:48px 24px;max-width:40rem;margin:auto}a{color:#1B2A41;background:#F2B33D;text-decoration:none;padding:10px 18px;border-radius:999px;font-weight:700;margin-right:8px;display:inline-block}p a{background:none;padding:0;color:#8F5E0E;font-weight:600}</style></head><body>';
    echo '<h1>Setup complete</h1><p>' . htmlspecialchars($message, ENT_QUOTES) . '</p>';
    echo '<p>Default admin credentials are in the README. Delete or protect this file in production.</p>';
    echo '<p><a href="./">Open the storefront</a> <a href="./admin/login">Admin sign in</a></p>';
    echo '</body></html>';
    exit;
}

if (is_file($lock)) {
    setup_fail('Setup has already been run.', 404);
}

$tokenNeeded = !in_array($env, ['local', 'development', 'dev', 'test'], true);
$provided = $isCli ? '' : (string)($_GET['token'] ?? $_POST['token'] ?? '');
if ($isCli) {
    foreach ($argv ?? [] as $arg) {
        if (str_starts_with($arg, '--token=')) {
            $provided = substr($arg, 8);
        }
    }
}
$expected = (string)nk_env('NK_SETUP_TOKEN', '');
if ($tokenNeeded) {
    if (strlen($expected) < 16 || $provided === '' || !hash_equals($expected, $provided)) {
        setup_fail('Setup is disabled in this environment. Set NK_SETUP_TOKEN (16+ characters) and pass it as token.', 403);
    }
}

$host = (string)config('db.host', '127.0.0.1');
$port = (int)config('db.port', 3306);
$name = (string)config('db.name', 'nano_kids_wholesale');
$user = (string)config('db.user', 'root');
$pass = (string)config('db.pass', '');
$schema = $root . '/database/schema.sql';
if (!is_file($schema)) {
    setup_fail('database/schema.sql is missing.');
}

$shouldRun = $isCli || (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['confirm']));

if (!$shouldRun) {
    header('Content-Type: text/html; charset=UTF-8');
    $tokenField = $tokenNeeded
        ? '<input type="hidden" name="token" value="' . htmlspecialchars($provided, ENT_QUOTES) . '">'
        : '';
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Nano Kids wholesale setup</title>';
    echo '<style>
      :root{--ink:#1B2A41;--ivory:#FFFDF9;--sun:#F2B33D;--muted:#5A6475;--line:#E4DDD0}
      *{box-sizing:border-box}
      body{margin:0;font-family:Manrope,system-ui,sans-serif;background:var(--ivory);color:var(--ink)}
      .wrap{max-width:36rem;margin:0 auto;padding:72px 24px}
      .mark{width:40px;height:40px;border-radius:10px;background:var(--sun);display:grid;place-items:center;font-family:Georgia,serif;font-style:italic;font-weight:600;margin-bottom:18px}
      h1{font-family:Georgia,"Times New Roman",serif;font-weight:500;font-size:2rem;margin:0 0 12px}
      p{color:var(--muted);line-height:1.55;margin:0 0 14px}
      dl{display:grid;grid-template-columns:8rem 1fr;gap:6px 12px;margin:20px 0 28px;font-size:14px}
      dt{color:var(--muted)} dd{margin:0;font-weight:600}
      button{height:42px;padding:0 20px;border-radius:999px;background:var(--sun);color:var(--ink);font-weight:700;border:0;font-size:14px;cursor:pointer;font-family:inherit}
      button:hover{filter:brightness(.96)}
    </style></head><body><div class="wrap"><div class="mark">N</div>';
    echo '<h1>Create the catalogue database</h1>';
    echo '<p>This will create the database if it does not exist, import the schema and seed the 23-style catalogue, then write a lock file so setup cannot run again.</p>';
    echo '<dl>';
    echo '<dt>Host</dt><dd>' . htmlspecialchars($host . ':' . $port, ENT_QUOTES) . '</dd>';
    echo '<dt>Database</dt><dd>' . htmlspecialchars($name, ENT_QUOTES) . '</dd>';
    echo '<dt>User</dt><dd>' . htmlspecialchars($user, ENT_QUOTES) . '</dd>';
    echo '<dt>Password</dt><dd>' . ($pass === '' ? '(empty)' : '••••••••') . '</dd>';
    echo '</dl>';
    echo '<form method="post" action="">' . $tokenField;
    echo '<input type="hidden" name="confirm" value="1">';
    echo '<button type="submit">Create database and import catalogue</button>';
    echo '</form></div></body></html>';
    exit;
}

try {
    $dsn = sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $host, $port);
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    $safe = '`' . str_replace('`', '``', $name) . '`';
    $pdo->exec("CREATE DATABASE IF NOT EXISTS {$safe} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE {$safe}");

    $sql = (string)file_get_contents($schema);
    $chunks = preg_split('/;\s*\n/', $sql) ?: [];
    foreach ($chunks as $chunk) {
        $chunk = trim($chunk);
        if ($chunk === '' || str_starts_with($chunk, '--') && !str_contains($chunk, "\n")) {
            continue;
        }
        $bare = trim(preg_replace('/^--.*$/m', '', $chunk) ?? $chunk);
        if ($bare === '') {
            continue;
        }
        $pdo->exec($chunk);
    }
} catch (Throwable $e) {
    app_log('setup.failed', ['error' => $e->getMessage()]);
    setup_fail('Setup failed: ' . $e->getMessage());
}

$dir = $root . '/storage';
if (!is_dir($dir)) {
    mkdir($dir, 0775, true);
}
file_put_contents($lock, date('c') . PHP_EOL);
setup_ok('The database “' . $name . '” is ready and the catalogue has been seeded.');
