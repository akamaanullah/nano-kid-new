<?php
$site_name = $site_name ?? 'Nano Kids Wholesale';
$contact = $contact ?? [];
$manager = $manager ?? [];
?>
<div class="admin-head">
  <div>
    <div class="kicker">Desk</div>
    <h1>Settings</h1>
    <p>Trade contact details shown on the public site.</p>
  </div>
</div>
<form method="post" action="<?= e(url('admin/settings')) ?>" class="form-grid">
  <?= csrf_field() ?>
  <div class="panel">
    <h2>Trade desk</h2>
    <label class="field">Site name<input type="text" name="site_name" value="<?= e((string)$site_name) ?>"></label>
    <label class="field">Email<input type="email" name="contact_email" value="<?= e((string)($contact['email'] ?? '')) ?>"></label>
    <label class="field">Phone<input type="text" name="contact_phone" value="<?= e((string)($contact['phone'] ?? '')) ?>"></label>
    <label class="field">WhatsApp<input type="text" name="contact_whatsapp" value="<?= e((string)($contact['whatsapp'] ?? '')) ?>"></label>
    <label class="field">Warehouse<textarea name="contact_warehouse" rows="2"><?= e((string)($contact['warehouse'] ?? '')) ?></textarea></label>
    <label class="field">Showroom<textarea name="contact_showroom" rows="2"><?= e((string)($contact['showroom'] ?? '')) ?></textarea></label>
  </div>
  <div class="panel">
    <h2>Account manager</h2>
    <label class="field">Name<input type="text" name="manager_name" value="<?= e((string)($manager['name'] ?? '')) ?>"></label>
    <label class="field">Role<input type="text" name="manager_role" value="<?= e((string)($manager['role'] ?? '')) ?>"></label>
    <label class="field">Phone<input type="text" name="manager_phone" value="<?= e((string)($manager['phone'] ?? '')) ?>"></label>
    <label class="field">Email<input type="email" name="manager_email" value="<?= e((string)($manager['email'] ?? '')) ?>"></label>
    <label class="field">Hours<input type="text" name="manager_hours" value="<?= e((string)($manager['hours'] ?? '')) ?>"></label>
    <button class="btn btn--sun" type="submit">Save settings</button>
  </div>
</form>
