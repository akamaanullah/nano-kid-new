<?php
$q = $quote;
$items = $q['items'] ?? [];
$fmtDate = static fn($d) => $d ? date('j M Y, g:i a', strtotime((string)$d)) : '—';
$statuses = ['submitted','reviewed','quoted','accepted','declined','converted'];
?>
<div class="admin-head">
  <div>
    <div class="kicker">Quote</div>
    <h1><?= e($q['reference']) ?></h1>
    <p><?= status_pill($q['status']) ?> · <?= e($fmtDate($q['created_at'])) ?></p>
  </div>
  <a class="btn btn--ghost" href="<?= e(url('admin/quotes')) ?>">Back to list</a>
</div>
<div class="form-grid">
  <div class="panel">
    <h2>Lines</h2>
    <div class="table-wrap">
      <table class="adm-table">
        <thead><tr><th>Style</th><th>Colour / sizes</th><th class="num">Qty</th><th class="num">Unit</th><th class="num">Total</th></tr></thead>
        <tbody>
          <?php foreach ($items as $it):
            $sizes = $it['sizes'] ?? [];
            $sizeStr = is_array($sizes) ? implode(' · ', array_map(static fn($k, $v) => $k . ': ' . $v, array_keys($sizes), $sizes)) : '';
          ?>
            <tr>
              <td class="cell-stack"><b><?= e($it['name']) ?></b><small><?= e((string)$it['sku']) ?> · MOQ <?= (int)$it['moq'] ?></small></td>
              <td><?= e((string)$it['color']) ?><?= $sizeStr !== '' ? '<div class="muted">' . e($sizeStr) . '</div>' : '' ?></td>
              <td class="num"><?= (int)$it['qty'] ?></td>
              <td class="num"><?= e(rs((float)$it['unit_price'])) ?><?= $it['discount_pct'] ? '<small> –' . e((string)$it['discount_pct']) . '%</small>' : '' ?></td>
              <td class="num"><?= e(rs((float)$it['line_total'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <p class="sum-line" style="display:flex;justify-content:space-between;margin-top:12px"><span><?= (int)$q['pieces'] ?> pieces</span><b><?= e(rs((float)$q['subtotal'])) ?></b></p>
  </div>
  <div class="panel">
    <h2>Business</h2>
    <dl class="dl">
      <dt>Business</dt><dd><?= e($q['business_name']) ?></dd>
      <dt>Contact</dt><dd><?= e($q['contact_name']) ?></dd>
      <dt>Email</dt><dd><?= e($q['email']) ?></dd>
      <dt>Phone</dt><dd><?= e($q['phone']) ?></dd>
      <dt>City</dt><dd><?= e($q['city']) ?></dd>
      <dt>NTN</dt><dd><?= e((string)$q['ntn']) ?></dd>
      <?php if ($q['notes']): ?><dt>Notes</dt><dd><?= e($q['notes']) ?></dd><?php endif; ?>
    </dl>
    <form method="post" action="<?= e(url('admin/quotes/' . $q['id'] . '/status')) ?>" style="margin-top:16px">
      <?= csrf_field() ?>
      <label class="field">Update status
        <select name="status">
          <?php foreach ($statuses as $s): ?>
            <option value="<?= $s ?>"<?= $q['status'] === $s ? ' selected' : '' ?>><?= ucfirst($s) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <button class="btn" type="submit">Save status</button>
    </form>
    <?php if ($q['status'] !== 'converted'): ?>
      <form method="post" action="<?= e(url('admin/quotes/' . $q['id'] . '/convert')) ?>" style="margin-top:12px">
        <?= csrf_field() ?>
        <button class="btn btn--sun" type="submit" data-confirm="Convert this quote into an order?">Convert to order</button>
      </form>
    <?php endif; ?>
  </div>
</div>
