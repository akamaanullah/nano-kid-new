<?php $requests = $requests ?? []; $fmtDate = static fn($d) => $d ? date('j M Y', strtotime((string)$d)) : '—'; ?>
<div class="admin-head">
  <div>
    <div class="kicker">Programmes</div>
    <h1>Private label</h1>
    <p>Custom programme requests from the studio.</p>
  </div>
</div>
<div class="panel">
  <div class="table-tools">
    <input class="grow" type="search" data-table-search placeholder="Search requests…" aria-label="Filter requests">
  </div>
  <?php if (!$requests): ?>
    <div class="empty"><h3>No requests</h3><p>Private-label briefs submitted on the site will show here.</p></div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="adm-table" data-table data-page-size="12">
        <thead><tr><th>Shop</th><th>Garment</th><th>Qty</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
          <?php foreach ($requests as $r): ?>
            <tr>
              <td class="cell-stack"><a class="name" href="<?= e(url('admin/private-label/' . $r['id'])) ?>"><?= e($r['shop_name']) ?></a><small><?= e((string)$r['email']) ?></small></td>
              <td><?= e($r['garment']) ?> · <?= e($r['mark']) ?></td>
              <td><?= (int)$r['qty'] ?></td>
              <td><?= status_pill($r['status']) ?></td>
              <td><?= e($fmtDate($r['created_at'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="pager" data-pager></div>
  <?php endif; ?>
</div>
