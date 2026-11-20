/* Product page — size/colour grid, live totals, MOQ gate, add to quote */
(() => {
  if (document.body.dataset.page !== 'product') return;
  const { $, $$, fmt, params, addToQuote, toast, packLabel, stockLabel } = NK;

  const pathSlug = (location.pathname.replace(/\/$/, '').split('/').pop() || '');
  const p = (window.NK_BOOT && window.NK_BOOT.product) || bySlug(params.get('slug') || pathSlug) || byId(params.get('id'));
  if (!p) return;
  document.title = `${p.name} — Nano Kids Wholesale`;

  let color = p.colors[0];
  const qty = {};
  p.sizes.forEach(s => { qty[s] = 0; });

  function totalQty() { return Object.values(qty).reduce((n, v) => n + v, 0); }

  function live() {
    const n = totalQty();
    const tier = tierForQty(p.tiers, n || p.moq);
    const unit = unitPrice(p.base_price, n ? tier.discount : 0);
    const ok = n >= p.moq;
    const warn = $('#moqWarn');
    if (warn) {
      warn.hidden = n === 0 || ok;
      warn.textContent = n === 0 ? '' : `Add ${p.moq - n} more piece${p.moq - n === 1 ? '' : 's'} to meet the MOQ of ${p.moq}.`;
    }
    $('#liveQty').textContent = n;
    $('#liveUnit').textContent = fmt(unit) + ' / pc';
    $('#liveTier').textContent = n ? (tier.discount ? tier.discount + '% volume discount' : 'Base wholesale price') : 'Select quantities';
    $('#liveTotal').textContent = fmt(unit * n);
    const btn = $('#addQuoteBtn');
    btn.disabled = n === 0;
    btn.classList.toggle('is-blocked', n > 0 && !ok);
    $$('.tier-table tbody tr').forEach(tr => {
      tr.classList.toggle('is-on', n >= Number(tr.dataset.min) && (tr.dataset.max === '' || n <= Number(tr.dataset.max)));
    });
  }

  function render() {
    $('#crumbs').innerHTML = `<span><a href="${nkUrl('')}">Home</a></span><span><a href="${nkUrl('catalog')}">Catalogue</a></span><span><a href="${nkUrl('catalog', { cat: p.category })}">${(CATS.find(c=>c.key===p.category)||{}).label||p.category}</a></span><span>${p.name}</span>`;
    $('#pName').textContent = p.name;
    $('#pSku').textContent = p.sku;
    $('#pLead').textContent = `${p.lead_time_days} working days`;
    $('#pStock').textContent = stockLabel(p.stock_status);
    $('#pMoq').textContent = p.moq + ' pcs';
    $('#pFabric').textContent = p.fabric;
    $('#pGsm').textContent = p.gsm + ' gsm';
    $('#pPack').textContent = packLabel(p) || 'Open size assortment';
    $('#pDesc').textContent = p.description;
    $('#pAge').textContent = (AGES.find(a => a.key === p.age_group) || {}).label || p.age_group;

    const main = $('#mainImg');
    main.src = p.images[0];
    main.alt = p.name;
    $('#thumbs').innerHTML = p.images.map((src, i) =>
      `<button type="button" class="thumb ${i===0?'is-on':''}" data-src="${src}"><img src="${src}" alt=""></button>`
    ).join('');

    $('#colorRow').innerHTML = p.colors.map(c => {
      const meta = COLORS[c] || { n: c, h: '#ccc' };
      return `<button type="button" class="swatch ${c===color?'is-on':''}" data-color="${c}" style="--c:${meta.h}" title="${meta.n}" aria-label="${meta.n}"></button>`;
    }).join('');
    $('#colorName').textContent = (COLORS[color] || { n: color }).n;

    $('#sizeGrid').innerHTML = p.sizes.map(s => `<label class="size-cell">
      <span>${s}</span>
      <input type="number" min="0" step="1" value="0" data-size="${s}" inputmode="numeric" aria-label="Quantity ${s}">
    </label>`).join('');

    $('#tierTable tbody').innerHTML = p.tiers.map(t => {
      const range = t.max == null ? `${t.min}+ pcs` : `${t.min}–${t.max} pcs`;
      const unit = unitPrice(p.base_price, t.discount);
      return `<tr data-min="${t.min}" data-max="${t.max ?? ''}">
        <td>${range}</td><td>${t.discount ? t.discount + '% off' : 'Base price'}</td>
        <td>${fmt(unit)} / pc</td></tr>`;
    }).join('');

    const related = PRODUCTS.filter(x => x.id !== p.id && (x.category === p.category || x.age_group === p.age_group)).slice(0, 4);
    $('#relatedGrid').innerHTML = related.map(NK.productCard).join('');
    if (NK.initReveals) NK.initReveals();
    live();
  }

  document.addEventListener('click', e => {
    const th = e.target.closest('.thumb');
    if (th) {
      $('#mainImg').src = th.dataset.src;
      $$('.thumb').forEach(t => t.classList.toggle('is-on', t === th));
    }
    const sw = e.target.closest('.swatch');
    if (sw) {
      color = sw.dataset.color;
      $$('.swatch').forEach(s => s.classList.toggle('is-on', s === sw));
      $('#colorName').textContent = (COLORS[color] || { n: color }).n;
    }
  });
  document.addEventListener('input', e => {
    const inp = e.target.closest('[data-size]');
    if (!inp) return;
    qty[inp.dataset.size] = Math.max(0, Math.floor(Number(inp.value) || 0));
    live();
  });
  $('#addQuoteBtn').addEventListener('click', () => {
    const res = addToQuote({ product_id: p.id, color, sizes: { ...qty } });
    if (!res.ok) { toast(res.error, 'warn'); return; }
    p.sizes.forEach(s => { qty[s] = 0; });
    $$('[data-size]').forEach(i => { i.value = 0; });
    live();
  });

  if (!$('#pName') || !$('#pName').textContent.trim()) render();
  else if (NK.initReveals) NK.initReveals();
  live();
})();
