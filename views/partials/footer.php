<?php
$boot = $boot ?? store_boot();
$navAges = $boot['ages'] ?? [];
$navSeasons = $boot['seasons'] ?? [];
$contact = $boot['contact'] ?? [];
$logo = '<a class="logo" href="' . e(url('/')) . '" aria-label="Nano Kids Wholesale home"><span class="logo-word">Nano<i>Kids</i></span><span class="logo-sub">Wholesale Trade</span></a>';
?>
<footer class="site-footer">
  <div class="footer-accent" aria-hidden="true"></div>
  <div class="wrap">
    <div class="footer-grid">
      <div class="footer-brand">
        <?= $logo ?>
        <p>Wholesale clothing for retailers, shops and distributors — baby and kids programmes from newborn to 14, with per-style MOQs, volume tiers and private label.</p>
        <div class="socials">
          <a href="#" aria-label="Instagram"><?= nkw_icon('insta') ?></a>
          <a href="#" aria-label="Facebook"><?= nkw_icon('fb') ?></a>
          <a href="#" aria-label="WhatsApp"><?= nkw_icon('chat') ?></a>
        </div>
      </div>
      <div>
        <h5>Catalogue</h5>
        <ul>
          <li><a href="<?= e(url('catalog')) ?>">All styles</a></li>
          <?php foreach ($navAges as $a): ?>
            <li><a href="<?= e(catalog_url(['age' => $a['key']])) ?>"><?= e($a['label']) ?></a></li>
          <?php endforeach; ?>
          <li><a href="<?= e(url('private-label')) ?>">Private label</a></li>
        </ul>
      </div>
      <div>
        <h5>Programmes</h5>
        <ul>
          <?php foreach ($navSeasons as $s): $sk = is_array($s) ? ($s['key'] ?? '') : $s; $sl = is_array($s) ? ($s['label'] ?? $sk) : $s; ?>
            <li><a href="<?= e(catalog_url(['season' => $sk])) ?>"><?= e($sl) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div>
        <h5>Trade</h5>
        <ul>
          <li><a href="<?= e(url('apply')) ?>">Become a dealer</a></li>
          <li><a href="<?= e(url('login')) ?>">Trade login</a></li>
          <li><a href="<?= e(url('faq')) ?>">MOQ &amp; terms</a></li>
          <li><a href="<?= e(url('quote')) ?>">Request a quote</a></li>
          <li><a href="<?= e(url('contact')) ?>">Contact trade</a></li>
        </ul>
      </div>
      <div class="footer-contact">
        <h5>Trade desk</h5>
        <ul>
          <li><?= nkw_icon('pin') ?><span><?= e((string)($contact['warehouse'] ?? '')) ?></span></li>
          <li><?= nkw_icon('phone') ?><span><?= e((string)($contact['phone'] ?? '')) ?></span></li>
          <li><?= nkw_icon('mail') ?><span><?= e((string)($contact['email'] ?? '')) ?></span></li>
          <li><?= nkw_icon('clock') ?><span>Mon – Sat, 10 am – 6 pm</span></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© 2026 Nano Kids Wholesale. All rights reserved.</span>
      <div class="pay-chips"><span>Bank transfer</span><span>50% advance</span><span>Trade credit*</span><span>NTN invoiced</span></div>
      <nav>
        <a href="<?= e(url('faq')) ?>">Terms</a>
        <a href="<?= e(url('faq')) ?>">Shipping</a>
        <a href="<?= e(url('faq')) ?>">Returns</a>
      </nav>
    </div>
  </div>
</footer>
<div class="toast" id="toast" role="status" aria-live="polite"></div>
