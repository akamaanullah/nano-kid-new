/* Bulk custom / private-label studio */
(() => {
  if (document.body.dataset.page !== 'private-label') return;
  const { $, $$, fmt, addToQuote, toast } = NK;

  const MOQ = 50;
  const base = 1290;
  const colors = [
    { id: '#F2B33D', name: 'Mustard' },
    { id: '#FF8B73', name: 'Coral' },
    { id: '#7ED9B8', name: 'Mint' },
    { id: '#7EC9F0', name: 'Sky' },
    { id: '#1B2A41', name: 'Navy' },
    { id: '#F7F3EE', name: 'Ivory' }
  ];
  const state = { color: colors[0], garment: 'tee', label: 'woven', pack: 'mailer', mark: 'print', name: 'Your shop', qty: MOQ };

  function tiers() { return tiersFor(MOQ); }
  function live() {
    const t = tierForQty(tiers(), state.qty);
    const unit = unitPrice(base, state.qty >= MOQ ? t.discount : 0);
    $('#plQty').value = state.qty;
    $('#plMoqNote').textContent = state.qty < MOQ
      ? `Custom programmes have an MOQ of ${MOQ} pieces. Add ${MOQ - state.qty} more.`
      : `${t.discount ? t.discount + '% volume discount applied. ' : ''}${fmt(unit)} / pc · ${fmt(unit * state.qty)} estimated.`;
    $('#plMoqNote').classList.toggle('is-short', state.qty < MOQ);
    $('#plAdd').disabled = state.qty < MOQ;
    $('#diyTeeFill').setAttribute('fill', state.color.id);
    const tx = $('#diyName');
    if (tx) { tx.textContent = state.name || 'Your shop'; tx.setAttribute('fill', state.color.id === '#F7F3EE' ? '#1B2A41' : '#F7F3EE'); }
  }

  $('#plMixer').innerHTML = colors.map((c, i) =>
    `<button type="button" class="diy-chip ${i===0?'is-on':''}" data-color="${c.id}" data-name="${c.name}"><i style="--c:${c.id}"></i>${c.name}</button>`
  ).join('');

  document.addEventListener('click', e => {
    const chip = e.target.closest('#plMixer [data-color]');
    if (chip) {
      state.color = { id: chip.dataset.color, name: chip.dataset.name };
      $$('#plMixer .diy-chip').forEach(c => c.classList.toggle('is-on', c === chip));
      live();
    }
  });
  ['garment','label','pack','mark'].forEach(k => {
    $$(`[name="${k}"]`).forEach(el => el.addEventListener('change', () => { state[k] = el.value; }));
  });
  $('#plName')?.addEventListener('input', e => { state.name = e.target.value; live(); });
  $('#plQty')?.addEventListener('input', e => { state.qty = Math.max(0, Math.floor(Number(e.target.value) || 0)); live(); });

  $('#plAdd')?.addEventListener('click', () => {
    if (state.qty < MOQ) { toast(`Custom programmes require ${MOQ} pieces.`, 'warn'); return; }
    const t = tierForQty(tiers(), state.qty);
    addToQuote({
      custom: true,
      name: `Private label ${state.garment} — ${state.name || 'unbranded'}`,
      qty: state.qty,
      moq: MOQ,
      unit_price: unitPrice(base, t.discount),
      color: state.color.name,
      meta: { garment: state.garment, label: state.label, pack: state.pack, mark: state.mark }
    });
  });

  live();
})();
