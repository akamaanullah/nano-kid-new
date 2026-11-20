<section class="section">
  <div class="wrap auth-layout auth-layout--narrow">
    <div>
      <span class="eyebrow">Trade login</span>
      <h1>Welcome <em>back.<svg class="squiggle" viewBox="0 0 140 14" fill="none" aria-hidden="true"><path d="M2 9c18-8 28 6 48 0s28-8 48 1 22 7 40-2" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/></svg></em></h1>
      <p class="lead">Sign in to follow quotes, orders and reorders. Approved dealers use the email and password issued by the trade desk.</p>
    </div>
    <form class="auth-card" id="loginForm" method="post" action="<?= e(url('login')) ?>">
      <?= csrf_field() ?>
      <label class="field">Work email<input type="email" name="email" id="loginEmail" required autocomplete="username" value="<?= e(old('email')) ?>"></label>
      <label class="field">Password<input type="password" name="password" id="loginPass" required minlength="4" autocomplete="current-password"></label>
      <button class="btn btn--pill btn--block" type="submit">Sign in</button>
      <p class="news-note">No account yet? <a href="<?= e(url('apply')) ?>">Apply to become a dealer</a>.</p>
    </form>
  </div>
</section>
