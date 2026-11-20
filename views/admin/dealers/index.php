<?php $dealers = $dealers ?? []; $fmtDate = static fn($d) => $d ? date('j M Y', strtotime((string)$d)) : '—'; ?>
<div class="admin-head">
  <div>
    <div class="kicker">Accounts</div>
    <h1>Dealers</h1>
    <p><?= count($dealers) ?> trade account<?= count($dealers) === 1 ? '' : 's' ?>.</p>
  </div>
</div>
<div class="panel">
  <div class="table-tools">
    <input class="grow" type="search" data-table-search placeholder="Search dealers…" aria-label="Filter dealers">
  </div>
  <?php if (!$dealers): ?>
    <div class="empty"><h3>No dealers yet</h3><p>Approving an application will create the first dealer login.</p></div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="adm-table" data-table data-page-size="12">
        <thead><tr><th>Business</th><th>Contact</th><th>City</th><th>Status</th><th>Joined</th></tr></thead>
        <tbody>
          <?php foreach ($dealers as $d): ?>
            <tr>
              <td class="cell-stack"><a class="name" href="<?= e(url('admin/dealers/' . $d['id'])) ?>"><?= e($d['business_name'] ?: $d['name']) ?></a><small><?= e($d['email']) ?></small></td>
              <td><?= e($d['name']) ?></td>
              <td><?= e((string)$d['city']) ?></td>
              <td><?= status_pill($d['is_active'] ? 'approved' : 'paused') ?></td>
              <td><?= e($fmtDate($d['created_at'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="pager" data-pager></div>
  <?php endif; ?>
</div>
