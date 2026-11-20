<?php
$o = $order; $items = $o['items'] ?? [];
$fmtDate = static fn($d) => $d ? date('j M Y, g:i a', strtotime((string)$d)) : '—';
$statuses = ['pending','confirmed','in_packing','dispatched','delivered','cancelled'];
?>
<div class="admin-head">
  <div>
    <div class="kicker">Order</div>
    <h1><?= e($o['reference']) ?></h1>
    <p><?= status_pill($o['status']) ?> · <?= e($fmtDate($o['created_at'])) ?></p>
  </div>
  <a class="btn btn--ghost" href="<?= e(url('admin/orders')) ?>">Back to list</a>
</div>
<div class="form-grid">
  <div class="panel">
    <h2>Lines</h2>
    <table class="adm-table">
      <thead><tr><th>Style</th><th>Colour</th><th class="num">Qty</th><th class="num">Unit</th><th class="num">Total</th></tr></thead>
      <tbody>
        <?php foreach ($items as $it): ?>
          <tr>
            <td class="cell-stack"><b><?= e($it['name']) ?></b><small><?= e((string)$it['sku']) ?></small></td>
            <td><?= e((string)$it['color']) ?></td>
            <td class="num"><?= (int)$it['qty'] ?></td>
            <td class="num"><?= e(rs((float)$it['unit_price'])) ?></td>
            <td class="num"><?= e(rs((float)$it['line_total'])) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <p style="display:flex;justify-content:space-between;margin-top:12px"><span><?= (int)$o['pieces'] ?> pieces</span><b><?= e(rs((float)$o['total'])) ?></b></p>
  </div>
  <div class="panel">
    <h2>Update status</h2>
    <form method="post" action="<?= e(url('admin/orders/' . $o['id'] . '/status')) ?>">
      <?= csrf_field() ?>
      <label class="field">Status
        <select name="status">
          <?php foreach ($statuses as $s): ?>
            <option value="<?= $s ?>"<?= $o['status'] === $s ? ' selected' : '' ?>><?= e(ucfirst(str_replace('_',' ',$s))) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <button class="btn btn--sun" type="submit">Save status</button>
    </form>
    <?php if ($o['quote_id']): ?>
      <p style="margin-top:14px"><a href="<?= e(url('admin/quotes/' . $o['quote_id'])) ?>">View source quote →</a></p>
    <?php endif; ?>
  </div>
</div>
