<?php $products = $products ?? []; $q = $q ?? ''; $fmtDate = static fn($d) => $d ? date('j M Y', strtotime((string)$d)) : '—'; ?>
<div class="admin-head">
  <div>
    <div class="kicker">Catalogue</div>
    <h1>Products</h1>
    <p><?= count($products) ?> style<?= count($products) === 1 ? '' : 's' ?> in the trade book.</p>
  </div>
  <a class="btn btn--sun" href="<?= e(url('admin/products/create')) ?>">New product</a>
</div>
<div class="panel">
  <div class="table-tools">
    <input class="grow" type="search" data-table-search placeholder="Filter this list…" value="<?= e($q) ?>" aria-label="Filter products">
  </div>
  <?php if (!$products): ?>
    <div class="empty">
      <h3>No products found</h3>
      <p><?= $q !== '' ? 'Nothing matched that search.' : 'Add the first style to the trade catalogue.' ?></p>
      <a class="btn" href="<?= e(url('admin/products/create')) ?>">New product</a>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="adm-table" data-table data-page-size="12">
        <thead>
          <tr>
            <th>Style</th>
            <th>Category</th>
            <th>Age</th>
            <th class="num">MOQ</th>
            <th class="num">Base price</th>
            <th>Stock</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($products as $p): ?>
            <tr>
              <td class="cell-stack">
                <a class="name" href="<?= e(url('admin/products/' . $p['id'] . '/edit')) ?>"><?= e($p['name']) ?></a>
                <small><?= e($p['sku']) ?></small>
              </td>
              <td><?= e($p['category_label'] ?? '') ?></td>
              <td><?= e($p['age_label'] ?? '') ?></td>
              <td class="num"><?= (int)$p['moq'] ?></td>
              <td class="num"><?= e(rs((float)$p['base_price'])) ?></td>
              <td><?= status_pill($p['stock_status']) ?></td>
              <td class="right"><a class="btn btn--ghost btn--sm" href="<?= e(url('admin/products/' . $p['id'] . '/edit')) ?>">Edit</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="pager" data-pager></div>
  <?php endif; ?>
</div>
