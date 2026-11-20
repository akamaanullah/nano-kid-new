<?php $r = $row; $fmtDate = static fn($d) => $d ? date('j M Y, g:i a', strtotime((string)$d)) : '—'; ?>
<div class="admin-head">
  <div>
    <div class="kicker">Private label</div>
    <h1><?= e($r['shop_name']) ?></h1>
    <p><?= status_pill($r['status']) ?> · <?= e($fmtDate($r['created_at'])) ?></p>
  </div>
  <a class="btn btn--ghost" href="<?= e(url('admin/private-label')) ?>">Back to list</a>
</div>
<div class="form-grid">
  <div class="panel">
    <dl class="dl">
      <dt>Email</dt><dd><?= e((string)$r['email']) ?></dd>
      <dt>Phone</dt><dd><?= e((string)$r['phone']) ?></dd>
      <dt>Garment</dt><dd><?= e($r['garment']) ?></dd>
      <dt>Mark</dt><dd><?= e($r['mark']) ?></dd>
      <dt>Label</dt><dd><?= e($r['label_type']) ?></dd>
      <dt>Pack</dt><dd><?= e($r['pack']) ?></dd>
      <dt>Colour</dt><dd><?= e((string)$r['color']) ?></dd>
      <dt>Quantity</dt><dd><?= (int)$r['qty'] ?></dd>
      <?php if ($r['notes']): ?><dt>Notes</dt><dd><?= e($r['notes']) ?></dd><?php endif; ?>
    </dl>
  </div>
  <div class="panel">
    <h2>Update status</h2>
    <form method="post" action="<?= e(url('admin/private-label/' . $r['id'] . '/status')) ?>">
      <?= csrf_field() ?>
      <label class="field">Status
        <select name="status">
          <?php foreach (['new','reviewing','quoted','accepted','declined'] as $s): ?>
            <option value="<?= $s ?>"<?= $r['status'] === $s ? ' selected' : '' ?>><?= ucfirst($s) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <button class="btn btn--sun" type="submit">Save status</button>
    </form>
  </div>
</div>
