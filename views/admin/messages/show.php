<?php $m = $message; $fmtDate = static fn($d) => $d ? date('j M Y, g:i a', strtotime((string)$d)) : '—'; ?>
<div class="admin-head">
  <div>
    <div class="kicker">Message</div>
    <h1><?= e($m['business_name']) ?></h1>
    <p><?= e($m['email']) ?> · <?= e($m['phone']) ?> · <?= e($fmtDate($m['created_at'])) ?></p>
  </div>
  <a class="btn btn--ghost" href="<?= e(url('admin/messages')) ?>">Back to inbox</a>
</div>
<div class="panel">
  <p style="white-space:pre-wrap;margin:0"><?= e($m['message']) ?></p>
</div>
