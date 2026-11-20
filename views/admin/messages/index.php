<?php $messages = $messages ?? []; $fmtDate = static fn($d) => $d ? date('j M Y', strtotime((string)$d)) : '—'; ?>
<div class="admin-head">
  <div>
    <div class="kicker">Inbox</div>
    <h1>Messages</h1>
    <p>Enquiries from the contact form.</p>
  </div>
</div>
<div class="panel">
  <div class="table-tools">
    <input class="grow" type="search" data-table-search placeholder="Search messages…" aria-label="Filter messages">
  </div>
  <?php if (!$messages): ?>
    <div class="empty"><h3>Inbox is clear</h3><p>Contact form messages will land here.</p></div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="adm-table" data-table data-page-size="12">
        <thead><tr><th>Business</th><th>Email</th><th>Message</th><th>Date</th></tr></thead>
        <tbody>
          <?php foreach ($messages as $m): ?>
            <tr>
              <td class="cell-stack">
                <a class="name" href="<?= e(url('admin/messages/' . $m['id'])) ?>"><?= e($m['business_name']) ?></a>
                <?php if (empty($m['is_read'])): ?><small>Unread</small><?php endif; ?>
              </td>
              <td><?= e($m['email']) ?></td>
              <td><?= e(mb_strimwidth((string)$m['message'], 0, 80, '…')) ?></td>
              <td><?= e($fmtDate($m['created_at'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="pager" data-pager></div>
  <?php endif; ?>
</div>
