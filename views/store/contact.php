<section class="section">
    <div class="wrap contact-grid">
      <div>
        <span class="eyebrow">Trade desk</span>
        <h1>Talk to us about a <em>programme</em></h1>
        <p class="lead">For quotations, stock checks and dealership applications, write or call the trade desk in Lahore.</p>
        <ul class="footer-contact" style="margin-top:28px">
          <li><span>Warehouse<br>14-A, Quaid-e-Azam Industrial Estate, Kot Lakhpat, Lahore</span></li>
          <li><span>Showroom<br>12-B, Main Boulevard, Gulberg III, Lahore</span></li>
          <li><span>trade@nanokids.pk<br>+92 42 3577 6400 · WhatsApp +92 300 855 2140</span></li>
          <li><span>Mon – Sat, 10 am – 6 pm</span></li>
        </ul>
      </div>
      <form method="post" action="<?= e(url('contact')) ?>" class="auth-card">
        <?= csrf_field() ?>
        <label class="field">Business name<input type="text" name="business" required></label>
        <label class="field">Email<input type="email" name="email" required></label>
        <label class="field">Phone<input type="tel" name="phone" required></label>
        <label class="field">Message<textarea name="message" rows="5" required></textarea></label>
        <button class="btn btn--pill" type="submit">Send message</button>
        <p class="form-success" hidden>Thank you — the trade desk will reply shortly.</p>
      </form>
    </div>
  </section>
