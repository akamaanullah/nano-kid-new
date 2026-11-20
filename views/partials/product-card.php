<?php
/** @var array $p */
$href = url('product/' . $p['slug']);
$img = product_image($p);
$pack = pack_label($p);
$unit = first_unit_price($p);
$catLabel = $p['category_label'] ?? $p['category'] ?? '';
$ageLabel = $p['age_label'] ?? $p['age_group'] ?? '';
?>
<article class="pcard" data-reveal>
  <a class="pcard-media" href="<?= e($href) ?>">
    <?php if (!empty($p['tag'])): ?><span class="pcard-tag"><?= e((string)$p['tag']) ?></span><?php endif; ?>
    <img src="<?= e($img) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
  </a>
  <div class="pcard-info">
    <p class="pcard-meta"><?= e($catLabel) ?> · <?= e($ageLabel) ?></p>
    <a class="pcard-name" href="<?= e($href) ?>"><?= e($p['name']) ?></a>
    <p class="pcard-sku"><?= e($p['sku']) ?></p>
    <div class="pcard-trade">
      <span class="pcard-price"><?= e(rs($unit)) ?> <small>/ pc</small></span>
      <span class="moq-badge">MOQ <?= (int)$p['moq'] ?></span>
    </div>
    <?php if ($pack): ?><p class="pcard-pack"><?= e($pack) ?></p><?php endif; ?>
    <a class="btn btn--pill btn--ghost btn--sm" href="<?= e($href) ?>">View &amp; add to quote</a>
  </div>
</article>
