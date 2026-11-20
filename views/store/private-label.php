<section class="section">
    <div class="wrap">
      <div class="diy-panel">
        <div class="diy">
          <div class="diy-visual" data-reveal="fade">
            <div class="diy-mixer" id="plMixer"></div>
            <svg viewBox="0 0 400 440" aria-label="Custom tee preview" id="diyTee">
              <path id="diyTeeFill" d="M130 40 L88 56 L32 112 L68 152 L100 130 L100 384 Q100 394 110 394 L290 394 Q300 394 300 384 L300 130 L332 152 L368 112 L312 56 L270 40 Q200 100 130 40 Z" fill="#F2B33D"/>
              <path d="M130 40 Q200 100 270 40" fill="none" stroke="rgba(0,0,0,.18)" stroke-width="9" stroke-linecap="round"/>
              <text id="diyName" x="200" y="215" text-anchor="middle" font-family="Cormorant Garamond, serif" font-style="italic" font-size="42" fill="#F7F3EE">Your shop</text>
            </svg>
          </div>
          <div class="diy-copy" data-reveal>
            <span class="eyebrow">Private label</span>
            <h1>Your name, our <em>making.<svg class="squiggle" viewBox="0 0 140 14" fill="none" aria-hidden="true"><path d="M2 9c18-8 28 6 48 0s28-8 48 1 22 7 40-2" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/></svg></em></h1>
            <p class="lead">Brief a bulk custom programme — garment, marking, labels and packaging — then add it to your quote. Custom work has a 50 piece MOQ.</p>

            <label class="field">Shop or brand name<input type="text" id="plName" value="Your shop" maxlength="18"></label>

            <div class="opt-grid">
              <fieldset><legend>Garment</legend>
                <label class="check radio"><input type="radio" name="garment" value="tee" checked><span>Tee</span></label>
                <label class="check radio"><input type="radio" name="garment" value="sweatshirt"><span>Sweatshirt</span></label>
                <label class="check radio"><input type="radio" name="garment" value="polo"><span>Polo</span></label>
                <label class="check radio"><input type="radio" name="garment" value="set"><span>Kids set</span></label>
              </fieldset>
              <fieldset><legend>Marking</legend>
                <label class="check radio"><input type="radio" name="mark" value="print" checked><span>Print</span></label>
                <label class="check radio"><input type="radio" name="mark" value="embroidery"><span>Embroidery</span></label>
              </fieldset>
              <fieldset><legend>Labels</legend>
                <label class="check radio"><input type="radio" name="label" value="woven" checked><span>Woven label</span></label>
                <label class="check radio"><input type="radio" name="label" value="printed"><span>Printed label</span></label>
                <label class="check radio"><input type="radio" name="label" value="hangtag"><span>Hangtag only</span></label>
              </fieldset>
              <fieldset><legend>Packaging</legend>
                <label class="check radio"><input type="radio" name="pack" value="mailer" checked><span>Plain mailer</span></label>
                <label class="check radio"><input type="radio" name="pack" value="branded"><span>Branded bag</span></label>
                <label class="check radio"><input type="radio" name="pack" value="box"><span>Gift box</span></label>
              </fieldset>
            </div>

            <label class="field">Quantity (pieces)<input type="number" id="plQty" min="50" value="50"></label>
            <p class="moq-warn" id="plMoqNote"></p>
            <button class="btn btn--pill btn--light" type="button" id="plAdd">Add programme to quote</button>
          </div>
        </div>
      </div>
    </div>
  </section>
