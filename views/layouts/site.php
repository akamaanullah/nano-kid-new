<?php
/** @var string $contentView */
$boot = $boot ?? store_boot();
$page = $page ?? '';
$title = $title ?? 'Nano Kids Wholesale';
$description = $description ?? 'Kids and baby garments in wholesale packs from the Nano Kids trade desk in Lahore.';
$flashError = flash('error');
$flashSuccess = flash('success');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($description) ?>">
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="icon" href="<?= e(url('favicon.svg')) ?>" type="image/svg+xml">
<link rel="icon" href="<?= e(url('favicon.ico')) ?>" sizes="32x32">
<link rel="stylesheet" href="<?= e(asset_url('css/style.css')) ?>">
<link rel="stylesheet" href="<?= e(asset_url('css/app.css')) ?>">
</head>
<body data-page="<?= e($page) ?>">
<?php require project_root() . '/views/partials/header.php'; ?>
<main id="main">
<?php if ($flashError): ?>
  <div class="wrap"><div class="flash flash--error" role="alert"><?= e((string)$flashError) ?></div></div>
<?php endif; ?>
<?php if ($flashSuccess): ?>
  <div class="wrap"><div class="flash flash--ok" role="status"><?= e((string)$flashSuccess) ?></div></div>
<?php endif; ?>
<?php require project_root() . '/views/' . $contentView . '.php'; ?>
</main>
<?php require project_root() . '/views/partials/footer.php'; ?>
<script type="application/json" id="nk-boot"><?= json_encode($boot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<script src="<?= e(asset_url('js/data.js')) ?>"></script>
<script src="<?= e(asset_url('js/main.js')) ?>"></script>
<?php if ($page === 'catalog'): ?>
<script src="<?= e(asset_url('js/catalog.js')) ?>"></script>
<?php elseif ($page === 'product'): ?>
<script src="<?= e(asset_url('js/product.js')) ?>"></script>
<?php elseif ($page === 'quote'): ?>
<script src="<?= e(asset_url('js/quote.js')) ?>"></script>
<?php elseif ($page === 'private-label'): ?>
<script src="<?= e(asset_url('js/private-label.js')) ?>"></script>
<?php elseif ($page === 'dashboard'): ?>
<script src="<?= e(asset_url('js/dashboard.js')) ?>"></script>
<?php endif; ?>
</body>
</html>
