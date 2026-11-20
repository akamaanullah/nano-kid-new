<?php
/** Standalone error page — does not query the database or use the site layout. */
$errorStatus = $errorStatus ?? 500;
$isSetupNeeded = $isSetupNeeded ?? false;
$setupUrl = $setupUrl ?? '/setup.php';
$isLocal = $isLocal ?? false;
$errorMessage = $errorMessage ?? 'Something went wrong.';
$errorDetail = $errorDetail ?? null;
$title = $isSetupNeeded ? 'Database setup required' : 'The trade desk is unavailable';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
<style>
  :root { --ink:#1B2A41; --ivory:#FFFDF9; --sun:#F2B33D; --muted:#5A6475; --line:#E4DDD0; --accent:#8F5E0E; }
  * { box-sizing: border-box; }
  body { margin:0; font-family: Manrope, system-ui, sans-serif; background: var(--ivory); color: var(--ink); }
  .wrap { max-width: 36rem; margin: 0 auto; padding: 72px 24px; }
  .mark { width:40px; height:40px; border-radius:10px; background:var(--sun); display:grid; place-items:center; font-family: Georgia, serif; font-style:italic; font-weight:600; margin-bottom:18px; }
  .kicker { font-size:11px; letter-spacing:.14em; text-transform:uppercase; color:var(--muted); font-weight:700; margin:0 0 8px; }
  h1 { font-family: Georgia, "Times New Roman", serif; font-weight:500; font-size:2rem; margin:0 0 12px; letter-spacing:-.02em; }
  h1 em { font-style:italic; color:var(--accent); }
  p { color:var(--muted); line-height:1.55; margin:0 0 14px; }
  .btn { display:inline-flex; align-items:center; justify-content:center; height:40px; padding:0 18px; border-radius:999px; background:var(--sun); color:var(--ink); font-weight:700; text-decoration:none; border:0; font-size:14px; }
  .btn:hover { filter: brightness(.96); }
  .detail { margin-top:22px; padding:12px 14px; border:1px solid var(--line); border-radius:10px; background:#fff; font-family: ui-monospace, Consolas, monospace; font-size:12.5px; color:var(--ink); white-space:pre-wrap; word-break:break-word; }
</style>
</head>
<body>
  <div class="wrap">
    <div class="mark">N</div>
    <p class="kicker"><?= (int)$errorStatus === 0 ? 'Error' : (int)$errorStatus ?></p>
    <?php if ($isSetupNeeded): ?>
      <h1>The catalogue database is not <em>set up</em> yet.</h1>
      <p>MySQL is running, but the <code>nano_kids_wholesale</code> database has not been created. Open the installer once to create it and import the 23-style catalogue.</p>
      <p><a class="btn" href="<?= e($setupUrl) ?>">Open setup.php</a></p>
    <?php else: ?>
      <h1>The trade desk is temporarily <em>unavailable.</em></h1>
      <p><?= e($errorMessage) ?></p>
    <?php endif; ?>
    <?php if ($isLocal && $errorDetail): ?>
      <div class="detail"><?= e($errorDetail) ?></div>
    <?php endif; ?>
  </div>
</body>
</html>
