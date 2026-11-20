<?php $applications = $applications ?? []; $status = $status ?? ''; $fmtDate = static fn($d) => $d ? date('j M Y', strtotime((string)$d)) : '—'; ?>
<div class="admin-head">
  <div>
    <div class="kicker">Accounts</div>
    <h1>Dealer applications</h1>
    <p>Approve a request to create a dealer login.</p>
  </div>
</div>
<div class="panel">
  <form class="table-tools" method="get" action="<?= e(url('admin/applications')) ?>">
    <select name="status" data-auto-submit>
      <option value="">All statuses</option>
      <?php foreach (['pending','approved','rejected'] as $s): ?>
        <option value="<?= $s ?>"<?= $status === $s ? ' selected' : '' ?>><?= ucfirst($s) ?></option>
      <?php endforeach; ?>
    </select>
    <input class="grow" type="search" data-table-search placeholder="Search business, email, city…" aria-label="Filter applications">
  </form>
  <?php if (!$applications): ?>
    <div class="empty"><h3>No applications</h3><p>New dealership requests from the public form will appear here.</p></div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="adm-table" data-table data-page-size="12">
        <thead><tr><th>Business</th><th>Contact</th><th>City</th><th>Volume</th><th>Status</th><th>Received</th></tr></thead>
        <tbody>
          <?php foreach ($applications as $a): ?>
            <tr>
              <td class="cell-stack"><a class="name" href="<?= e(url('admin/applications/' . $a['id'])) ?>"><?= e($a['business_name']) ?></a><small><?= e($a['shop_type']) ?></small></td>
              <td class="cell-stack"><?= e($a['contact_name']) ?><small><?= e($a['email']) ?></small></td>
              <td><?= e($a['city']) ?></td>
              <td><?= e($a['monthly_volume']) ?></td>
              <td><?= status_pill($a['status']) ?></td>
              <td><?= e($fmtDate($a['created_at'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="pager" data-pager></div>
  <?php endif; ?>
</div>
