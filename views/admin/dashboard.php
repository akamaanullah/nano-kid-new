<?php
$stats = $stats ?? [];
$trends = $trends ?? [];
$chart = $chart ?? ['labels' => [], 'quotes' => [], 'orders' => []];
$quotes = $quotes ?? [];
$applications = $applications ?? [];
$messages = $messages ?? [];

$labels = $chart['labels'] ?? [];
$qSeries = $chart['quotes'] ?? [];
$oSeries = $chart['orders'] ?? [];
$w = 640; $h = 200; $padL = 28; $padR = 8; $padT = 12; $padB = 28;
$n = max(count($labels), 1);
$max = max(array_merge([1], $qSeries, $oSeries));
$pts = static function (array $series) use ($n, $max, $w, $h, $padL, $padR, $padT, $padB): string {
    $innerW = $w - $padL - $padR;
    $innerH = $h - $padT - $padB;
    $out = [];
    $count = max(count($series), 1);
    foreach ($series as $i => $v) {
        $x = $padL + ($count === 1 ? $innerW / 2 : ($i / ($count - 1)) * $innerW);
        $y = $padT + $innerH - ((float)$v / $max) * $innerH;
        $out[] = round($x, 1) . ',' . round($y, 1);
    }
    return implode(' ', $out);
};
$fmtDate = static fn($d) => $d ? date('j M Y', strtotime((string)$d)) : '—';
?>
<div class="admin-head">
  <div>
    <div class="kicker">Overview</div>
    <h1>Trade desk</h1>
    <p>Quotes, orders and applications at a glance.</p>
  </div>
  <a class="btn btn--sun" href="<?= e(url('admin/products/create')) ?>">New product</a>
</div>

<div class="stat-grid">
  <article class="stat-card">
    <div class="label">Open quotes</div>
    <div class="value"><?= (int)$stats['open_quotes'] ?></div>
    <?= trend_chip($trends['quotes'] ?? ['dir' => 'flat', 'label' => 'No change this week']) ?>
  </article>
  <article class="stat-card">
    <div class="label">Open orders</div>
    <div class="value"><?= (int)$stats['open_orders'] ?></div>
    <?= trend_chip($trends['orders'] ?? ['dir' => 'flat', 'label' => 'No change this week']) ?>
  </article>
  <article class="stat-card">
    <div class="label">Pending applications</div>
    <div class="value"><?= (int)$stats['pending_apps'] ?></div>
    <span class="trend trend-flat"><?= (int)$stats['dealers'] ?> approved dealers</span>
  </article>
  <article class="stat-card">
    <div class="label">Active styles</div>
    <div class="value"><?= (int)$stats['products'] ?></div>
    <span class="trend trend-flat"><?= (int)$stats['unread'] ?> unread messages · <?= (int)$stats['new_pl'] ?> new private label</span>
  </article>
</div>

<div class="dash-grid">
  <section class="panel">
    <div class="panel-head">
      <h2>Quotes &amp; orders</h2>
      <span class="muted">Last 8 weeks</span>
    </div>
    <div class="chart-legend">
      <span><i class="q"></i>Quotes</span>
      <span><i class="o"></i>Orders</span>
    </div>
    <svg class="chart" viewBox="0 0 <?= $w ?> <?= $h ?>" role="img" aria-label="Quotes and orders over the last eight weeks">
      <?php
        $innerH = $h - $padT - $padB;
        $innerW = $w - $padL - $padR;
        $tickCount = $max <= 1 ? 1 : ($max <= 4 ? (int)$max : 4);
        for ($g = 0; $g <= $tickCount; $g++):
          $y = $padT + ($innerH / $tickCount) * $g;
          $val = (int)round($max * (1 - $g / $tickCount));
      ?>
        <line x1="<?= $padL ?>" y1="<?= $y ?>" x2="<?= $w - $padR ?>" y2="<?= $y ?>" stroke="#E4DDD0" stroke-width="1"/>
        <text x="4" y="<?= $y + 4 ?>" font-size="10" fill="#5A6475"><?= $val ?></text>
      <?php endfor; ?>
      <?php foreach ($labels as $i => $lab):
          $x = $padL + (count($labels) <= 1 ? $innerW / 2 : ($i / (count($labels) - 1)) * $innerW);
      ?>
        <text x="<?= $x ?>" y="<?= $h - 8 ?>" font-size="10" text-anchor="middle" fill="#5A6475"><?= e($lab) ?></text>
      <?php endforeach; ?>
      <polyline fill="none" stroke="#1B2A41" stroke-width="2.2" stroke-linejoin="round" stroke-linecap="round" points="<?= e($pts($qSeries)) ?>"/>
      <polyline fill="none" stroke="#F2B33D" stroke-width="2.2" stroke-linejoin="round" stroke-linecap="round" points="<?= e($pts($oSeries)) ?>"/>
      <?php foreach ($qSeries as $i => $v):
          $x = $padL + (count($qSeries) <= 1 ? $innerW / 2 : ($i / (max(count($qSeries)-1,1))) * $innerW);
          $y = $padT + $innerH - ((float)$v / $max) * $innerH;
      ?>
        <circle cx="<?= $x ?>" cy="<?= $y ?>" r="3" fill="#1B2A41"/>
      <?php endforeach; ?>
    </svg>
  </section>

  <section class="panel">
    <div class="panel-head">
      <h2>Pending applications</h2>
      <a href="<?= e(url('admin/applications')) ?>">View all</a>
    </div>
    <?php if (!$applications): ?>
      <div class="empty"><h3>All caught up</h3><p>No dealership applications waiting for review.</p></div>
    <?php else: ?>
      <div class="table-wrap">
        <table class="adm-table">
          <thead><tr><th>Business</th><th>City</th><th>Received</th></tr></thead>
          <tbody>
            <?php foreach ($applications as $a): ?>
              <tr>
                <td class="cell-stack">
                  <a class="name" href="<?= e(url('admin/applications/' . $a['id'])) ?>"><?= e($a['business_name']) ?></a>
                  <small><?= e($a['contact_name']) ?></small>
                </td>
                <td><?= e($a['city']) ?></td>
                <td><?= e($fmtDate($a['created_at'])) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>
</div>

<section class="panel">
  <div class="panel-head">
    <h2>Recent quotes</h2>
    <a href="<?= e(url('admin/quotes')) ?>">View all</a>
  </div>
  <?php if (!$quotes): ?>
    <div class="empty"><h3>No quotes yet</h3><p>Submitted RFQs will land here.</p></div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="adm-table" data-table>
        <thead><tr><th>Reference</th><th>Business</th><th>Status</th><th class="num">Pieces</th><th class="num">Estimate</th><th>Received</th></tr></thead>
        <tbody>
          <?php foreach ($quotes as $q): ?>
            <tr>
              <td><a class="name" href="<?= e(url('admin/quotes/' . $q['id'])) ?>"><?= e($q['reference']) ?></a></td>
              <td class="cell-stack"><b><?= e($q['business_name']) ?></b><small><?= e($q['email']) ?></small></td>
              <td><?= status_pill($q['status']) ?></td>
              <td class="num"><?= (int)$q['pieces'] ?></td>
              <td class="num"><?= e(rs((float)$q['subtotal'])) ?></td>
              <td><?= e($fmtDate($q['created_at'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>
