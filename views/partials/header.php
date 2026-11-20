<?php
$user = current_user();
$page = $page ?? '';
$boot = $boot ?? store_boot();
$navAges = $boot['ages'] ?? [];
$navCats = $boot['cats'] ?? [];
$navSeasons = $boot['seasons'] ?? [];
$contact = $boot['contact'] ?? [];
$act = static fn(string $p): string => $page === $p ? 'is-active' : '';
$logo = '<a class="logo" href="' . e(url('/')) . '" aria-label="Nano Kids Wholesale home"><span class="logo-word">Nano<i>Kids</i></span><span class="logo-sub">Wholesale Trade</span></a>';
$mid = (int)ceil(count($navCats) / 2);

$col = static function (string $title, array $list, string $param) use ($page): string {
    $items = '';
    foreach ($list as $x) {
        $key = $x['key'] ?? $x['slug'] ?? '';
        $label = $x['label'] ?? $key;
        $range = $x['range'] ?? '';
        $href = catalog_url([$param === 'cat' ? 'cat' : $param => $key]);
        $items .= '<li><a href="' . e($href) . '">' . e($label) . ($range !== '' ? '<small>' . e($range) . '</small>' : '') . '</a></li>';
    }
    return '<div><h4>' . e($title) . '</h4><ul>' . $items . '</ul></div>';
};

$accountHref = $user ? (($user['role'] ?? '') === 'admin' ? url('admin') : url('dashboard')) : url('login');
$accountLabel = $user ? 'Dashboard' : 'Trade login';
?>
<div class="site-chrome">
  <div class="announce" role="region" aria-label="Announcements">
    <div class="announce-msg is-on">Trade pricing · MOQ is set per style, from 12 pieces</div>
    <div class="announce-msg">Dispatch in 7–10 working days on in-stock programmes</div>
    <div class="announce-msg">Private label and custom programmes <a href="<?= e(url('private-label')) ?>">Enquire</a></div>
  </div>
  <header class="site-header" id="siteHeader">
    <div class="wrap header-inner">
      <button class="icon-btn menu-toggle" data-open-menu aria-label="Open menu"><?= nkw_icon('menu') ?></button>
      <ul class="main-nav">
        <li class="has-mega">
          <a href="<?= e(url('catalog')) ?>" class="<?= $act('catalog') ?>">Catalogue</a>
          <div class="mega">
            <div class="mega-inner">
              <?= $col('By age', $navAges, 'age') ?>
              <?= $col('By season', $navSeasons, 'season') ?>
              <?= $col('Category', array_slice($navCats, 0, $mid), 'cat') ?>
              <?= $col('More categories', array_slice($navCats, $mid), 'cat') ?>
            </div>
            <div class="mega-foot"><a class="mega-cta" href="<?= e(url('private-label')) ?>">Private label programmes <span aria-hidden="true">→</span></a></div>
          </div>
        </li>
        <li><a class="nav-accent <?= $act('private-label') ?>" href="<?= e(url('private-label')) ?>">Private Label</a></li>
        <li><a href="<?= e(url('apply')) ?>" class="<?= $act('apply') ?>">Become a dealer</a></li>
        <li><a href="<?= e(url('about')) ?>" class="<?= $act('about') ?>">About</a></li>
      </ul>
      <?= $logo ?>
      <div class="header-actions">
        <button class="icon-btn" data-open-search aria-label="Search"><?= nkw_icon('search') ?></button>
        <a class="icon-btn hide-sm" href="<?= e($accountHref) ?>" aria-label="<?= e($accountLabel) ?>"><?= nkw_icon('user') ?></a>
        <a class="icon-btn" href="<?= e(url('quote')) ?>" aria-label="Quote list"><?= nkw_icon('file') ?><span class="badge" data-quote-count>0</span></a>
      </div>
    </div>
  </header>
</div>
<aside class="mobile-menu" id="mobileMenu" aria-label="Menu">
  <div class="mm-top"><?= $logo ?><button class="icon-btn" data-close-menu aria-label="Close menu"><?= nkw_icon('close') ?></button></div>
  <ul class="mm-list">
    <li><a href="<?= e(url('catalog')) ?>">Catalogue</a></li>
    <li>
      <button class="mm-acc-btn" aria-expanded="false">Shop by <?= nkw_icon('plus') ?></button>
      <div class="mm-sub"><div class="mm-sub-inner">
        <h5>By age</h5>
        <?php foreach ($navAges as $a): ?><a href="<?= e(catalog_url(['age' => $a['key']])) ?>"><?= e($a['label']) ?></a><?php endforeach; ?>
        <h5>By season</h5>
        <?php foreach ($navSeasons as $s): $sk = is_array($s) ? ($s['key'] ?? '') : $s; $sl = is_array($s) ? ($s['label'] ?? $sk) : $s; ?>
          <a href="<?= e(catalog_url(['season' => $sk])) ?>"><?= e($sl) ?></a>
        <?php endforeach; ?>
        <h5>By category</h5>
        <?php foreach ($navCats as $c): ?><a href="<?= e(catalog_url(['cat' => $c['key']])) ?>"><?= e($c['label']) ?></a><?php endforeach; ?>
      </div></div>
    </li>
    <li><a href="<?= e(url('private-label')) ?>" style="color:var(--accent-text)">Private label</a></li>
    <li><a href="<?= e(url('apply')) ?>">Become a dealer</a></li>
    <li><a href="<?= e(url('about')) ?>">About</a></li>
    <li><a href="<?= e(url('faq')) ?>">FAQ</a></li>
    <li><a href="<?= e(url('contact')) ?>">Contact</a></li>
    <li><a href="<?= e($accountHref) ?>"><?= e($accountLabel) ?></a></li>
  </ul>
  <div class="mm-foot"><?= e((string)($contact['email'] ?? '')) ?><br><?= e((string)($contact['phone'] ?? '')) ?></div>
</aside>
<div class="scrim" id="scrim"></div>
<div class="search-overlay" id="searchOverlay" role="dialog" aria-label="Search">
  <div class="search-top">
    <input type="search" id="searchInput" placeholder="Search styles, SKUs, fabrics…" autocomplete="off">
    <button class="icon-btn" data-close-search aria-label="Close search"><?= nkw_icon('close') ?></button>
  </div>
  <div class="search-body"><div class="search-results" id="searchResults"></div></div>
</div>
