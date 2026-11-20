<?php
$d = $dealer; $quotes = $quotes ?? []; $orders = $orders ?? [];
$fmtDate = static fn($x) => $x ? date('j M Y', strtotime((string)$x)) : '—';
?>
<div class="admin-head">
  <div>
    <div class="kicker">Dealer</div>
    <h1><?= e($d['business_name'] ?: $d['name']) ?></h1>
    <p><?= e($d['email']) ?> · <?= status_pill($d['is_active'] ? 'active' : 'paused') ?></p>
  </div>
  <form method="post" action="<?= e(url('admin/dealers/' . $d['id'] . '/toggle')) ?>">
    <?= csrf_field() ?>
    <button class="btn <?= $d['is_active'] ? 'btn--danger' : 'btn--sun' ?>" type="submit" data-confirm="<?= $d['is_active'] ? 'Pause this dealer login?' : 'Reactivate this dealer?' ?>">
      <?= $d['is_active'] ? 'Pause account' : 'Reactivate account' ?>
    </button>
  </form>
</div>
<div class="form-grid">
  <div class="panel">
    <h2>Profile</h2>
    <dl class="dl">
      <dt>Contact</dt><dd><?= e($d['name']) ?></dd>
      <dt>Phone</dt><dd><?= e((string)$d['phone']) ?></dd>
      <dt>City</dt><dd><?= e((string)$d['city']) ?></dd>
      <dt>NTN</dt><dd><?= e((string)$d['ntn']) ?></dd>
      <dt>Shop type</dt><dd><?= e((string)$d['shop_type']) ?></dd>
      <dt>Volume</dt><dd><?= e((string)$d['monthly_volume']) ?></dd>
      <dt>Last login</dt><dd><?= e($fmtDate($d['last_login_at'] ?? null)) ?></dd>
    </dl>
  </div>
  <div class="panel">
    <h2>Quotes</h2>
    <?php if (!$quotes): ?><p class="muted">No quotes yet.</p>
    <?php else: ?>
      <table class="adm-table">
        <thead><tr><th>Ref</th><th>Status</th><th class="num">Total</th></tr></thead>
        <tbody>
          <?php foreach ($quotes as $q): ?>
            <tr>
              <td><a href="<?= e(url('admin/quotes/' . $q['id'])) ?>"><?= e($q['reference']) ?></a></td>
              <td><?= status_pill($q['status']) ?></td>
              <td class="num"><?= e(rs((float)$q['subtotal'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
    <h2 style="margin-top:18px">Orders</h2>
    <?php if (!$orders): ?><p class="muted">No orders yet.</p>
    <?php else: ?>
      <table class="adm-table">
        <thead><tr><th>Ref</th><th>Status</th><th class="num">Total</th></tr></thead>
        <tbody>
          <?php foreach ($orders as $o): ?>
            <tr>
              <td><a href="<?= e(url('admin/orders/' . $o['id'])) ?>"><?= e($o['reference']) ?></a></td>
              <td><?= status_pill($o['status']) ?></td>
              <td class="num"><?= e(rs((float)$o['total'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>
