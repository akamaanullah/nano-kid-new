<?php $a = $application; $fmtDate = static fn($d) => $d ? date('j M Y, g:i a', strtotime((string)$d)) : '—'; ?>
<div class="admin-head">
  <div>
    <div class="kicker">Application</div>
    <h1><?= e($a['business_name']) ?></h1>
    <p><?= status_pill($a['status']) ?> · Received <?= e($fmtDate($a['created_at'])) ?></p>
  </div>
  <a class="btn btn--ghost" href="<?= e(url('admin/applications')) ?>">Back to list</a>
</div>
<div class="form-grid">
  <div class="panel">
    <h2>Details</h2>
    <dl class="dl">
      <dt>Contact</dt><dd><?= e($a['contact_name']) ?></dd>
      <dt>Email</dt><dd><?= e($a['email']) ?></dd>
      <dt>Phone</dt><dd><?= e($a['phone']) ?></dd>
      <dt>City</dt><dd><?= e($a['city']) ?></dd>
      <dt>NTN</dt><dd><?= e($a['ntn']) ?></dd>
      <dt>Shop type</dt><dd><?= e($a['shop_type']) ?></dd>
      <dt>Volume</dt><dd><?= e($a['monthly_volume']) ?></dd>
      <?php if ($a['notes']): ?><dt>Notes</dt><dd><?= e($a['notes']) ?></dd><?php endif; ?>
    </dl>
  </div>
  <div class="panel">
    <?php if ($a['status'] === 'pending'): ?>
      <h2>Approve &amp; create login</h2>
      <p class="muted">Set a password for the new dealer. Share it with them directly — it is not emailed automatically.</p>
      <form method="post" action="<?= e(url('admin/applications/' . $a['id'] . '/approve')) ?>">
        <?= csrf_field() ?>
        <label class="field">Dealer password (min. 8 characters)<input type="text" name="password" minlength="8" required autocomplete="off"></label>
        <button class="btn btn--sun" type="submit" id="approveBtn">Approve and create account</button>
      </form>
      <form method="post" action="<?= e(url('admin/applications/' . $a['id'] . '/reject')) ?>" style="margin-top:16px">
        <?= csrf_field() ?>
        <button class="btn btn--danger" type="submit" data-confirm="Reject this application?">Reject application</button>
      </form>
    <?php elseif ($a['status'] === 'approved'): ?>
      <h2>Approved</h2>
      <p>A dealer login exists for <b><?= e($a['email']) ?></b><?= $a['user_id'] ? ' (user #' . (int)$a['user_id'] . ')' : '' ?>.</p>
      <?php if ($a['user_id']): ?>
        <a class="btn" href="<?= e(url('admin/dealers/' . $a['user_id'])) ?>">Open dealer record</a>
      <?php endif; ?>
    <?php else: ?>
      <h2>Rejected</h2>
      <p class="muted">Reviewed <?= e($fmtDate($a['reviewed_at'])) ?>.</p>
    <?php endif; ?>
  </div>
</div>
