<?php
$user = $user ?? current_user();
$submitted = (string)($_GET['submitted'] ?? '');
?>
<section class="section section--tight">
  <div class="wrap">
    <div class="section-head"><div><span class="eyebrow">Request for quotation</span><h1>Your <em>quote</em></h1></div></div>
    <div id="quoteEmpty" class="empty-note" hidden>
      <p>Your quote list is empty. Browse the catalogue and add styles that meet their MOQ.</p>
      <a class="btn btn--pill" href="<?= e(url('catalog')) ?>">Browse the catalogue</a>
    </div>
    <div id="quoteSheet">
      <div class="table-wrap">
        <table class="quote-table">
          <thead><tr><th>Style</th><th>Colour / sizes</th><th>Qty</th><th>Unit</th><th>Total</th><th></th></tr></thead>
          <tbody id="quoteBody"></tbody>
        </table>
      </div>
      <div class="quote-layout">
        <form class="quote-form" id="quoteForm" method="post" action="<?= e(url('quote')) ?>">
          <?= csrf_field() ?>
          <h3>Business details</h3>
          <div class="field-row">
            <label class="field">Business name<input type="text" name="business" id="bizName" required value="<?= e((string)($user['business_name'] ?? old('business'))) ?>"></label>
            <label class="field">Contact person<input type="text" name="contact" required value="<?= e((string)($user['name'] ?? old('contact'))) ?>"></label>
          </div>
          <div class="field-row">
            <label class="field">Work email<input type="email" name="email" id="bizEmail" required value="<?= e((string)($user['email'] ?? old('email'))) ?>"></label>
            <label class="field">Phone<input type="tel" name="phone" required value="<?= e((string)($user['phone'] ?? old('phone'))) ?>"></label>
          </div>
          <div class="field-row">
            <label class="field">City<input type="text" name="city" required value="<?= e((string)($user['city'] ?? old('city'))) ?>"></label>
            <label class="field">NTN / registration<input type="text" name="ntn" required value="<?= e((string)($user['ntn'] ?? old('ntn'))) ?>"></label>
          </div>
          <label class="field">Notes for the trade desk<textarea name="notes" rows="4" placeholder="Delivery window, labelling, or pack ratios…"><?= e(old('notes')) ?></textarea></label>
          <button class="btn btn--pill" type="submit" id="submitQuote">Submit quotation request</button>
        </form>
        <aside class="summary-card">
          <h3>Estimate</h3>
          <p class="sum-line"><span>Pieces</span><b id="sumPieces">0</b></p>
          <p class="sum-line"><span>Estimated total</span><b id="sumSub">Rs. 0</b></p>
          <p class="muted" id="sumNote"></p>
          <p class="news-note">This is an estimate. The trade desk will confirm stock, dispatch and the final invoice. Unit prices are recalculated on the server against each style’s MOQ and tiers.</p>
        </aside>
      </div>
    </div>
    <div id="quoteSuccess" class="success-panel"<?= $submitted === '' ? ' hidden' : '' ?>>
      <span class="hero-pill">Received</span>
      <h2>Quote <em id="rfqRef"><?= e($submitted) ?></em> is with the desk</h2>
      <p class="lead">We will confirm stock and lead time and send a written quotation to the email you provided, usually within one working day.</p>
      <a class="btn btn--pill" href="<?= e(url('catalog')) ?>">Continue browsing</a>
      <a class="btn btn--pill btn--ghost" href="<?= e(url($user ? 'dashboard' : 'login')) ?>"><?= $user ? 'Open your dashboard' : 'Trade login' ?></a>
    </div>
  </div>
</section>
