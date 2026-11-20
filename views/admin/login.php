<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'Admin sign in') ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="icon" href="<?= e(url('favicon.svg')) ?>" type="image/svg+xml">
<link rel="stylesheet" href="<?= e(asset_url('css/admin.css')) ?>">
</head>
<body class="admin-body">
  <div class="admin-login">
    <div class="login-card">
      <div class="mark">N</div>
      <p class="kicker muted" style="letter-spacing:.14em;text-transform:uppercase;font-size:11px;font-weight:700;margin:0 0 6px">Trade admin</p>
      <h1>Sign in to the <em>desk.</em></h1>
      <p class="lead">Manage styles, dealer applications, quotes and orders.</p>
      <?php if (!empty($error)): ?><div class="flash flash--error"><?= e((string)$error) ?></div><?php endif; ?>
      <form method="post" action="<?= e(url('admin/login')) ?>">
        <?= csrf_field() ?>
        <label class="field">Work email<input type="email" name="email" required autocomplete="username" value="<?= e(old('email')) ?>"></label>
        <label class="field">Password<input type="password" name="password" required autocomplete="current-password"></label>
        <button class="btn btn--sun" type="submit" style="width:100%;margin-top:4px">Sign in</button>
      </form>
    </div>
  </div>
</body>
</html>
