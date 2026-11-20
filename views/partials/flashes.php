<?php
$error = flash('error');
$success = flash('success');
if ($error): ?>
  <div class="flash flash--error" role="alert"><?= e((string)$error) ?></div>
<?php endif; if ($success): ?>
  <div class="flash flash--ok" role="status"><?= e((string)$success) ?></div>
<?php endif; ?>
