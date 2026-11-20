<?php
$filters = $filters ?? [];
$result = $result ?? ['items' => [], 'total' => 0, 'page' => 1, 'pages' => 1];
$sort = $sort ?? 'featured';
$ages = $ages ?? [];
$categories = $categories ?? [];
$seasons = $seasons ?? [];
$sel = static fn($cur, $val): string => (string)$cur === (string)$val ? ' selected' : '';
$fabrics = array_values(array_unique(array_filter(array_map(static fn($p) => $p['fabric'] ?? '', $boot['products'] ?? []))));
sort($fabrics);
$moqs = array_values(array_unique(array_map(static fn($p) => (int)($p['moq'] ?? 0), $boot['products'] ?? [])));
sort($moqs);
?>
<section class="page-banner page-banner--sand">
  <div class="wrap">
    <div class="page-banner-panel">
      <div class="page-banner-copy">
        <div class="crumbs">
          <span><a href="<?= e(url('/')) ?>">Home</a></span>
          <span>Catalogue</span>
          <span id="crumbCur"><?php
            $crumb = 'Trade catalogue';
            $catKey = (string)($filters['category'] ?? '');
            if ($catKey !== '') {
              foreach ($categories as $c) {
                if (($c['slug'] ?? $c['key'] ?? '') === $catKey) { $crumb = $c['label']; break; }
              }
            }
            echo e($crumb);
          ?></span>
        </div>
        <h1 id="shopTitle">Trade <em>catalogue.</em></h1>
        <p>Wholesale prices per piece, with each style’s own MOQ, pack ratio and lead time.</p>
      </div>
      <div class="page-banner-media">
        <div class="hero-blob" aria-hidden="true"></div>
        <div class="hero-photo">
          <img src="https://images.pexels.com/photos/6725093/pexels-photo-6725093.jpeg?auto=compress&amp;cs=tinysrgb&amp;w=2000" alt="Two children in matching camel cord jumpsuits, grey studio">
        </div>
      </div>
    </div>
  </div>
</section>
<div class="wrap shop-layout">
  <aside class="filters" id="filters" aria-label="Filters">
    <form method="get" action="<?= e(url('catalog')) ?>" id="filterForm">
      <div class="filters-head"><h3>Filters</h3><a class="link" href="<?= e(url('catalog')) ?>" id="clearAll">Clear all</a></div>
      <label class="field"><span>Age group</span>
        <select name="age">
          <option value="">All</option>
          <?php foreach ($ages as $a): ?>
            <option value="<?= e(($a['slug'] ?? $a['key'])) ?>"<?= $sel($filters['age'] ?? '', ($a['slug'] ?? $a['key'])) ?>><?= e($a['label']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label class="field"><span>Category</span>
        <select name="cat">
          <option value="">All</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?= e(($c['slug'] ?? $c['key'])) ?>"<?= $sel($filters['category'] ?? '', ($c['slug'] ?? $c['key'])) ?>><?= e($c['label']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label class="field"><span>Season</span>
        <select name="season">
          <option value="">All</option>
          <?php foreach ($seasons as $s): $sk = is_array($s) ? ($s['key'] ?? '') : $s; $sl = is_array($s) ? ($s['label'] ?? $sk) : $s; ?>
            <option value="<?= e($sk) ?>"<?= $sel($filters['season'] ?? '', $sk) ?>><?= e($sl) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label class="field"><span>Fabric</span>
        <select name="fabric">
          <option value="">All</option>
          <?php foreach ($fabrics as $f): ?>
            <option value="<?= e($f) ?>"<?= $sel($filters['fabric'] ?? '', $f) ?>><?= e($f) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label class="field"><span>MOQ</span>
        <select name="moq">
          <option value="">All</option>
          <?php foreach ($moqs as $m): if (!$m) continue; ?>
            <option value="<?= (int)$m ?>"<?= $sel($filters['moq'] ?? '', $m) ?>><?= (int)$m ?> pcs</option>
          <?php endforeach; ?>
        </select>
      </label>
      <label class="field"><span>Availability</span>
        <select name="stock">
          <option value="">All</option>
          <option value="in_stock"<?= $sel($filters['status'] ?? '', 'in_stock') ?>>In stock</option>
          <option value="limited"<?= $sel($filters['status'] ?? '', 'limited') ?>>Limited</option>
          <option value="made_to_order"<?= $sel($filters['status'] ?? '', 'made_to_order') ?>>Made to order</option>
        </select>
      </label>
      <div class="field"><span>Wholesale price / pc</span>
        <div class="price-row">
          <input type="number" name="min" min="0" value="<?= e((string)($filters['min'] ?? '')) ?>" aria-label="Min price">
          <input type="number" name="max" min="0" value="<?= e((string)($filters['max'] ?? '')) ?>" aria-label="Max price">
        </div>
      </div>
      <input type="hidden" name="q" value="<?= e((string)($filters['q'] ?? '')) ?>">
      <button class="btn btn--pill btn--sm" type="submit">Apply filters</button>
    </form>
  </aside>
  <div class="filter-footer">
    <a class="btn btn--ghost" href="<?= e(url('catalog')) ?>" style="flex:1">Clear</a>
    <button class="btn" type="submit" form="filterForm" id="filterApply" style="flex:2">Show styles</button>
  </div>
  <section class="shop-main" aria-label="Styles">
    <div class="toolbar">
      <div class="toolbar-left">
        <button class="btn btn--ghost btn--sm filter-open" id="filterOpen" type="button">Filters</button>
        <span class="result-count" id="resultCount"><?= (int)$result['total'] ?> style<?= (int)$result['total'] === 1 ? '' : 's' ?></span>
      </div>
      <div class="toolbar-right">
        <label class="sr-only" for="sortSel">Sort by</label>
        <select class="sort-select" id="sortSel" form="filterForm" name="sort">
          <option value="featured"<?= $sel($sort, 'featured') ?>>Featured</option>
          <option value="moq"<?= $sel($sort, 'moq') ?>>MOQ: low to high</option>
          <option value="price-asc"<?= $sel($sort, 'price-asc') ?>>Price: low to high</option>
          <option value="price-desc"<?= $sel($sort, 'price-desc') ?>>Price: high to low</option>
          <option value="name"<?= $sel($sort, 'name') ?>>Name</option>
        </select>
      </div>
    </div>
    <div class="products" id="productGrid">
      <?php if (!$result['items']): ?>
        <p class="empty-note">No styles match those filters.</p>
      <?php else: foreach ($result['items'] as $p): include project_root() . '/views/partials/product-card.php'; endforeach; endif; ?>
    </div>
    <?php if ((int)$result['pages'] > 1): ?>
      <nav class="pager" aria-label="Catalogue pages">
        <?php for ($i = 1; $i <= (int)$result['pages']; $i++): ?>
          <a class="btn btn--ghost btn--sm<?= $i === (int)$result['page'] ? ' is-on' : '' ?>" href="<?= e(catalog_url($filters, ['sort' => $sort, 'page' => $i])) ?>"><?= $i ?></a>
        <?php endfor; ?>
      </nav>
    <?php endif; ?>
  </section>
</div>
