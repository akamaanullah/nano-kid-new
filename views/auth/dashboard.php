<?php
$user = $user ?? current_user();
$quotes = $quotes ?? [];
$orders = $orders ?? [];
$manager = setting_json('account_manager', []);
$fmtDate = static fn($d) => $d ? date('j M Y', strtotime((string)$d)) : '—';
?>
<section class="section section--tight">
  <div class="wrap dash">
    <aside class="dash-side">
      <p class="eyebrow">Trade account</p>
      <h2 id="dashName"><?= e((string)($user['business_name'] ?: $user['name'])) ?></h2>
      <p class="muted" id="dashEmail"><?= e((string)$user['email']) ?></p>
      <nav class="dash-nav">
        <button type="button" class="is-on" data-tab="orders">Orders</button>
        <button type="button" data-tab="quotes">Quotes</button>
        <button type="button" data-tab="manager">Account manager</button>
      </nav>
      <form method="post" action="<?= e(url('logout')) ?>">
        <?= csrf_field() ?>
        <button class="btn btn--pill btn--ghost btn--sm" type="submit" id="logoutBtn">Sign out</button>
      </form>
    </aside>
    <div class="dash-main">
      <div data-panel="orders">
        <div class="section-head"><h3>Recent orders</h3><a class="link" href="<?= e(url('catalog')) ?>">Place a reorder from the catalogue</a></div>
        <div class="table-wrap">
          <table class="quote-table" id="orderTable">
            <thead><tr><th>Order</th><th>Date</th><th>Status</th><th>Pieces</th><th>Total</th></tr></thead>
            <tbody>
              <?php if (!$orders): ?>
                <tr><td colspan="5">No orders yet. Submit a quote and the desk will convert it once confirmed.</td></tr>
              <?php else: foreach ($orders as $o): ?>
                <tr>
                  <td><?= e($o['reference']) ?></td>
                  <td><?= e($fmtDate($o['created_at'])) ?></td>
                  <td><span class="status-pill"><?= e(ucfirst(str_replace('_', ' ', (string)$o['status']))) ?></span></td>
                  <td><?= (int)$o['pieces'] ?> pcs</td>
                  <td><?= e(rs((float)$o['total'])) ?></td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
      <div data-panel="quotes" hidden>
        <h3>Quote status</h3>
        <div class="table-wrap">
          <table class="quote-table" id="quoteTable">
            <thead><tr><th>Reference</th><th>Date</th><th>Status</th><th>Pieces</th><th>Estimate</th></tr></thead>
            <tbody>
              <?php if (!$quotes): ?>
                <tr><td colspan="5">No quotes submitted yet. Add styles to your quote list and send them to the desk.</td></tr>
              <?php else: foreach ($quotes as $q): ?>
                <tr>
                  <td><?= e($q['reference']) ?></td>
                  <td><?= e($fmtDate($q['created_at'])) ?></td>
                  <td><span class="status-pill"><?= e(ucfirst((string)$q['status'])) ?></span></td>
                  <td><?= (int)$q['pieces'] ?> pcs</td>
                  <td><?= e(rs((float)$q['subtotal'])) ?></td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
      <div data-panel="manager" hidden>
        <div class="manager-card">
          <span class="hero-pill">Your desk</span>
          <h3 id="mgrName"><?= e((string)($manager['name'] ?? 'Trade desk')) ?></h3>
          <p class="muted" id="mgrRole"><?= e((string)($manager['role'] ?? '')) ?></p>
          <p id="mgrPhone"><?= e((string)($manager['phone'] ?? '')) ?></p>
          <p id="mgrEmail"><?= e((string)($manager['email'] ?? '')) ?></p>
          <p class="muted" id="mgrHours"><?= e((string)($manager['hours'] ?? '')) ?></p>
          <a class="btn btn--pill" href="<?= e(url('contact')) ?>">Message the trade desk</a>
        </div>
      </div>
    </div>
  </div>
</section>
