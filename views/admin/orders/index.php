<?php $orders = $orders ?? []; $fmtDate = static fn($d) => $d ? date('j M Y', strtotime((string)$d)) : '—'; ?>
<div class="admin-head">
  <div>
    <div class="kicker">Trade</div>
    <h1>Orders</h1>
    <p>Orders created from converted quotes.</p>
  </div>
</div>
<div class="panel">
  <div class="table-tools">
    <input class="grow" type="search" data-table-search placeholder="Search orders…" aria-label="Filter orders">
  </div>
  <?php if (!$orders): ?>
    <div class="empty"><h3>No orders yet</h3><p>Convert a quote to create the first order.</p></div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="adm-table" data-table data-page-size="12">
        <thead><tr><th>Reference</th><th>Status</th><th class="num">Pieces</th><th class="num">Total</th><th>Date</th></tr></thead>
        <tbody>
          <?php foreach ($orders as $o): ?>
            <tr>
              <td><a class="name" href="<?= e(url('admin/orders/' . $o['id'])) ?>"><?= e($o['reference']) ?></a></td>
              <td><?= status_pill($o['status']) ?></td>
              <td class="num"><?= (int)$o['pieces'] ?></td>
              <td class="num"><?= e(rs((float)$o['total'])) ?></td>
              <td><?= e($fmtDate($o['created_at'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="pager" data-pager></div>
  <?php endif; ?>
</div>
