<?php
/** @var string $contentView */
$page = $page ?? '';
$title = $title ?? 'Admin — Nano Kids Wholesale';
$user = current_user();
$flashError = flash('error');
$flashSuccess = flash('success');
$pendingApps = 0;
$openQuotes = 0;
$unread = 0;
$newPl = 0;
try {
    $pendingApps = \App\Models\Application::countPending();
    $openQuotes = \App\Models\Quote::countOpen();
    $unread = \App\Models\ContactMessage::countUnread();
    $newPl = \App\Models\PrivateLabel::countNew();
} catch (\Throwable $e) {
    // layout still renders if counts fail
}
$act = static fn(string $key): string => $page === $key ? ' is-active' : '';
$ico = static function (string $d): string {
    return '<svg viewBox="0 0 24 24" aria-hidden="true">' . $d . '</svg>';
};
$items = [
    ['dashboard', 'admin', 'Dashboard', $ico('<path d="M4 11l8-7 8 7"/><path d="M6 10.5V20h12v-9.5"/>')],
    ['products', 'admin/products', 'Products', $ico('<rect x="4" y="4" width="7" height="7"/><rect x="13" y="4" width="7" height="7"/><rect x="4" y="13" width="7" height="7"/><rect x="13" y="13" width="7" height="7"/>')],
    ['categories', 'admin/categories', 'Categories', $ico('<path d="M4 7h16M4 12h16M4 17h10"/>')],
    ['applications', 'admin/applications', 'Applications', $ico('<path d="M7 3h7l5 5v13H7z"/><path d="M14 3v5h5"/><path d="M10 13h6M10 17h4"/>'), $pendingApps],
    ['dealers', 'admin/dealers', 'Dealers', $ico('<circle cx="12" cy="8" r="3.2"/><path d="M5 19c1.5-3.5 4-5 7-5s5.5 1.5 7 5"/>')],
    ['quotes', 'admin/quotes', 'Quotes', $ico('<path d="M7 3h10v18H7z"/><path d="M10 7h4M10 11h4M10 15h2"/>'), $openQuotes],
    ['orders', 'admin/orders', 'Orders', $ico('<path d="M3 6.5h11v9H3zM14 10h4l3 3v2.5h-7"/><circle cx="7" cy="17.5" r="1.6"/><circle cx="17" cy="17.5" r="1.6"/>')],
    ['private-label', 'admin/private-label', 'Private label', $ico('<path d="M5 19c0-8 5-13 14-14 0 9-5 14-13 14"/><path d="M5 19c3-4 6-6 9-8"/>'), $newPl],
    ['messages', 'admin/messages', 'Messages', $ico('<path d="M4 19l1.2-3.8A8 8 0 1 1 8.8 18.8z"/>'), $unread],
    ['settings', 'admin/settings', 'Settings', $ico('<circle cx="12" cy="12" r="3"/><path d="M12 3.5v2.2M12 18.3V21M4.8 6.2l1.6 1.6M17.6 16.2l1.6 1.6M3.5 12h2.2M18.3 12H21M4.8 17.8l1.6-1.6M17.6 7.8l1.6-1.6"/>')],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="icon" href="<?= e(url('favicon.svg')) ?>" type="image/svg+xml">
<link rel="stylesheet" href="<?= e(asset_url('css/admin.css')) ?>">
</head>
<body class="admin-body">
<div class="admin-scrim" id="adminScrim" data-close-sidebar></div>
<div class="admin">
  <aside class="admin-sidebar" id="adminSidebar">
    <a class="admin-brand" href="<?= e(url('admin')) ?>">
      <span class="mark">N</span>
      <span class="word"><b>Nano<i>Kids</i></b><small>Trade admin</small></span>
    </a>
    <nav class="admin-nav" aria-label="Admin">
      <?php foreach ($items as $item):
          [$key, $href, $label, $icon] = $item;
          $badge = $item[4] ?? 0;
      ?>
        <a href="<?= e(url($href)) ?>" class="<?= $act($key) ?>">
          <?= $icon ?><span><?= e($label) ?></span>
          <?php if ($badge): ?><span class="nav-badge"><?= (int)$badge ?></span><?php endif; ?>
        </a>
      <?php endforeach; ?>
    </nav>
    <div class="admin-nav-foot">
      <a href="<?= e(url('/')) ?>" target="_blank" rel="noopener">View storefront →</a>
    </div>
  </aside>
  <div class="admin-shell">
    <header class="admin-topbar">
      <button class="admin-menu-btn" type="button" data-open-sidebar aria-label="Open menu">
        <svg viewBox="0 0 24 24"><path d="M4 8h16M4 16h16" stroke="currentColor" fill="none" stroke-width="1.7" stroke-linecap="round"/></svg>
      </button>
      <form class="admin-search" method="get" action="<?= e(url('admin/products')) ?>" role="search">
        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="M16 16l4.5 4.5"/></svg>
        <input type="search" name="q" placeholder="Search styles, SKUs…" value="<?= e((string)($_GET['q'] ?? '')) ?>" aria-label="Search products">
      </form>
      <div class="admin-top-actions">
        <div class="admin-avatar">
          <span class="ava"><?= e(initials($user['name'] ?? 'Admin')) ?></span>
          <span class="who">
            <b><?= e((string)($user['name'] ?? 'Admin')) ?></b>
            <span><?= e((string)($user['email'] ?? '')) ?></span>
          </span>
        </div>
        <form method="post" action="<?= e(url('logout')) ?>">
          <?= csrf_field() ?>
          <button class="btn btn--ghost btn--sm" type="submit">Sign out</button>
        </form>
      </div>
    </header>
    <div class="admin-main">
      <?php if ($flashError): ?><div class="flash flash--error" role="alert"><?= e((string)$flashError) ?></div><?php endif; ?>
      <?php if ($flashSuccess): ?><div class="flash flash--ok" role="status"><?= e((string)$flashSuccess) ?></div><?php endif; ?>
      <?php
        if (!empty($errorMessage) && ($contentView ?? '') === 'errors/generic') {
            require project_root() . '/views/errors/generic.php';
        } else {
            require project_root() . '/views/' . $contentView . '.php';
        }
      ?>
    </div>
  </div>
</div>
<div class="toast" id="adminToast" role="status" aria-live="polite"></div>
<div class="scrim" id="confirmScrim"></div>
<div class="confirm" id="confirmBox" role="dialog" aria-modal="true" aria-labelledby="confirmTitle">
  <h3 id="confirmTitle">Please confirm</h3>
  <p id="confirmBody">This cannot be undone.</p>
  <div class="confirm-actions">
    <button type="button" class="btn btn--ghost" data-confirm-cancel>Cancel</button>
    <button type="button" class="btn btn--danger" data-confirm-ok>Confirm</button>
  </div>
</div>
<script src="<?= e(asset_url('js/admin.js')) ?>"></script>
</body>
</html>
