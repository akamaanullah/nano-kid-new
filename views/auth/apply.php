<?php $sent = !empty($_GET['sent']); ?>
<section class="section">
  <div class="wrap auth-layout">
    <div>
      <span class="eyebrow">Dealership</span>
      <h1>Apply to trade with <em>Nano Kids.<svg class="squiggle" viewBox="0 0 140 14" fill="none" aria-hidden="true"><path d="M2 9c18-8 28 6 48 0s28-8 48 1 22 7 40-2" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/></svg></em></h1>
      <p class="lead">Registered retailers, shops and distributors can apply for wholesale pricing, invoices against your NTN, and 14-day credit once the account is approved.</p>
      <ul class="plain-list">
        <li>Per-style MOQs, set in the admin — never a surprise site-wide number</li>
        <li>Volume tiers up to 22% off</li>
        <li>A named account manager after approval</li>
      </ul>
    </div>
    <?php if ($sent): ?>
      <div id="applySuccess" class="success-panel">
        <span class="hero-pill">Received</span>
        <h2>Thank you — your application is <em>in review.</em></h2>
        <p class="lead">The trade desk will be in touch, usually within two working days. You can sign in after your application is approved and a login has been created.</p>
        <a class="btn btn--pill" href="<?= e(url('catalog')) ?>">Browse the catalogue</a>
        <a class="btn btn--pill btn--ghost" href="<?= e(url('login')) ?>">Trade login</a>
      </div>
    <?php else: ?>
      <form class="auth-card" id="applyForm" method="post" action="<?= e(url('apply')) ?>">
        <?= csrf_field() ?>
        <label class="field">Business name<input type="text" name="business" required value="<?= e(old('business')) ?>"></label>
        <label class="field">Contact person<input type="text" name="contact" required value="<?= e(old('contact')) ?>"></label>
        <label class="field">Work email<input type="email" name="email" required value="<?= e(old('email')) ?>"></label>
        <label class="field">Phone<input type="tel" name="phone" required value="<?= e(old('phone')) ?>"></label>
        <label class="field">NTN / registration number<input type="text" name="ntn" required value="<?= e(old('ntn')) ?>"></label>
        <label class="field">City<input type="text" name="city" required value="<?= e(old('city')) ?>"></label>
        <label class="field">Type of shop
          <select name="type" required>
            <option value="">Select</option>
            <?php foreach (['Independent boutique','Multi-store retailer','Department store','Distributor / wholesaler','Online-only retailer'] as $opt): ?>
              <option<?= old('type') === $opt ? ' selected' : '' ?>><?= e($opt) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label class="field">Expected monthly volume
          <select name="volume" required>
            <option value="">Select</option>
            <?php foreach (['Under 200 pieces','200–500 pieces','500–1,500 pieces','1,500+ pieces'] as $opt): ?>
              <option<?= old('volume') === $opt ? ' selected' : '' ?>><?= e($opt) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <button class="btn btn--pill btn--block" type="submit">Submit application</button>
        <p class="news-note">Applications are reviewed by the trade desk. You will receive a login once approved.</p>
      </form>
    <?php endif; ?>
  </div>
</section>
