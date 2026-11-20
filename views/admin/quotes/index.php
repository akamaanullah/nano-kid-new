<?php $quotes = $quotes ?? []; $status = $status ?? ''; $fmtDate = static fn($d) => $d ? date('j M Y', strtotime((string)$d)) : '—'; ?>
<div class="admin-head">
  <div>
    <div class="kicker">Trade</div>
    <h1>Quotes</h1>
    <p>Review RFQs, update status, or convert a quote into an order.</p>
  </div>
</div>
<div class="panel">
  <form class="table-tools" method="get">
    <select name="status" data-auto-submit>
      <option value="">All statuses</option>
      <?php foreach (['submitted','reviewed','quoted','accepted','declined','converted'] as $s): ?>
        <option value="<?= $s ?>"<?= $status === $s ? ' selected' : '' ?>><?= ucfirst($s) ?></option>
      <?php endforeach; ?>
    </select>
    <input class="grow" type="search" data-table-search placeholder="Search reference, business, email…" aria-label="Filter quotes">
  </form>
  <?php if (!$quotes): ?>
    <div class="empty"><h3>No quotes</h3><p>Submitted quotation requests will appear in this list.</p></div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="adm-table" data-table data-page-size="12">
        <thead><tr><th>Reference</th><th>Business</th><th>Status</th><th class="num">Pieces</th><th class="num">Estimate</th><th>Date</th></tr></thead>
        <tbody>
          <?php foreach ($quotes as $q): ?>
            <tr>
              <td><a class="name" href="<?= e(url('admin/quotes/' . $q['id'])) ?>"><?= e($q['reference']) ?></a></td>
              <td class="cell-stack"><?= e($q['business_name']) ?><small><?= e($q['email']) ?></small></td>
              <td><?= status_pill($q['status']) ?></td>
              <td class="num"><?= (int)$q['pieces'] ?></td>
              <td class="num"><?= e(rs((float)$q['subtotal'])) ?></td>
              <td><?= e($fmtDate($q['created_at'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="pager" data-pager></div>
  <?php endif; ?>
</div>
