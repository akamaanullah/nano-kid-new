<?php
/** @var array $product */
$p = $product;
$img0 = product_image($p);
$colors = setting_json('colors', []);
?>
<div class="wrap">
  <div class="crumbs" id="crumbs" style="padding-top:24px">
    <span><a href="<?= e(url('/')) ?>">Home</a></span>
    <span><a href="<?= e(url('catalog')) ?>">Catalogue</a></span>
    <span><a href="<?= e(catalog_url(['cat' => $p['category']])) ?>"><?= e($p['category_label'] ?? $p['category']) ?></a></span>
    <span><?= e($p['name']) ?></span>
  </div>
</div>
<div class="wrap pdp pdp-wholesale">
  <div class="pdp-gallery">
    <div class="thumbs" id="thumbs">
      <?php foreach ($p['images'] as $i => $src): ?>
        <button type="button" class="thumb<?= $i === 0 ? ' is-on' : '' ?>" data-src="<?= e($src) ?>"><img src="<?= e($src) ?>" alt=""></button>
      <?php endforeach; ?>
    </div>
    <div class="pdp-main"><img id="mainImg" src="<?= e($img0) ?>" alt="<?= e($p['name']) ?>"></div>
  </div>
  <div class="pdp-info">
    <p class="eyebrow" id="pAge"><?= e($p['age_label'] ?? $p['age_group']) ?></p>
    <h1 id="pName"><?= e($p['name']) ?></h1>
    <p class="p-sku" id="pSku"><?= e($p['sku']) ?></p>
    <ul class="spec-row">
      <li><span>MOQ</span><b id="pMoq"><?= (int)$p['moq'] ?> pcs</b></li>
      <li><span>Lead time</span><b id="pLead"><?= (int)$p['lead_time_days'] ?> working days</b></li>
      <li><span>Availability</span><b id="pStock"><?= e(stock_label($p['stock_status'])) ?></b></li>
      <li><span>Fabric / GSM</span><b><span id="pFabric"><?= e((string)$p['fabric']) ?></span> · <span id="pGsm"><?= e((string)$p['gsm']) ?></span></b></li>
      <li><span>Pack</span><b id="pPack"><?= e(pack_label($p) ?: 'Open size assortment') ?></b></li>
    </ul>
    <p class="lead" id="pDesc"><?= e((string)$p['description']) ?></p>

    <h3 class="subhead">Volume tiers</h3>
    <table class="tier-table" id="tierTable">
      <thead><tr><th>Quantity</th><th>Discount</th><th>Unit price</th></tr></thead>
      <tbody>
        <?php foreach ($p['tiers'] as $t):
          $range = $t['max'] === null ? ($t['min'] . '+ pcs') : ($t['min'] . '–' . $t['max'] . ' pcs');
          $unit = round((float)$p['base_price'] * (1 - (float)$t['discount'] / 100), 2);
        ?>
          <tr data-min="<?= (int)$t['min'] ?>" data-max="<?= $t['max'] === null ? '' : (int)$t['max'] ?>">
            <td><?= e($range) ?></td>
            <td><?= $t['discount'] ? e((string)$t['discount']) . '% off' : 'Base price' ?></td>
            <td><?= e(rs($unit)) ?> / pc</td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="opt-block">
      <div class="opt-lab">Colour <b id="colorName"><?php
        $first = $p['colors'][0] ?? '';
        $meta = $colors[$first] ?? [];
        echo e((string)($meta['n'] ?? $first));
      ?></b></div>
      <div class="swatch-row" id="colorRow">
        <?php foreach ($p['colors'] as $i => $ck):
          $meta = $colors[$ck] ?? ['n' => $ck, 'h' => '#ccc'];
        ?>
          <button type="button" class="swatch<?= $i === 0 ? ' is-on' : '' ?>" data-color="<?= e($ck) ?>" style="--c:<?= e($meta['h'] ?? '#ccc') ?>" title="<?= e($meta['n'] ?? $ck) ?>" aria-label="<?= e($meta['n'] ?? $ck) ?>"></button>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="opt-block">
      <div class="opt-lab">Assortment — quantities per size</div>
      <div class="size-grid" id="sizeGrid">
        <?php foreach ($p['sizes'] as $s): ?>
          <label class="size-cell">
            <span><?= e($s) ?></span>
            <input type="number" min="0" step="1" value="0" data-size="<?= e($s) ?>" inputmode="numeric" aria-label="Quantity <?= e($s) ?>">
          </label>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="live-bar">
      <div><span class="muted">Total pieces</span><b id="liveQty">0</b></div>
      <div><span class="muted">Unit price</span><b id="liveUnit">—</b></div>
      <div><span class="muted">Tier</span><b id="liveTier">Select quantities</b></div>
      <div><span class="muted">Line total</span><b id="liveTotal">Rs. 0</b></div>
    </div>
    <p class="moq-warn" id="moqWarn" hidden></p>
    <button class="btn btn--pill btn--block" type="button" id="addQuoteBtn">Add to quote</button>
  </div>
</div>
<section class="section">
  <div class="wrap">
    <div class="section-head"><h2>Related <em>styles</em></h2></div>
    <div class="products" id="relatedGrid">
      <?php foreach ($related as $p) { include project_root() . '/views/partials/product-card.php'; } ?>
    </div>
  </div>
</section>
