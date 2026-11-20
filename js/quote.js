/* RFQ / quote list page */
(() => {
  if (document.body.dataset.page !== 'quote') return;
  const { $, $$, fmt, quoteGet, quoteSet, linePieces, lineTotals, quoteGrand, toast, authGet } = NK;

  function render() {
    const items = quoteGet();
    const body = $('#quoteBody');
    const empty = $('#quoteEmpty');
    const sheet = $('#quoteSheet');
    if (!items.length) {
      empty.hidden = false; sheet.hidden = true;
      return;
    }
    empty.hidden = true; sheet.hidden = false;
    body.innerHTML = items.map(line => {
      const t = lineTotals(line);
      const p = t.product;
      if (line.custom) {
        return `<tr data-id="${line.id}">
          <td class="q-prod"><div><b>${line.name}</b><small>Custom programme · MOQ ${line.moq} pcs</small></div></td>
          <td>—</td>
          <td><input type="number" min="${line.moq}" value="${line.qty}" data-custom-qty="${line.id}" class="qty-input"></td>
          <td>${fmt(line.unit_price)}</td>
          <td>${fmt(line.unit_price * line.qty)}</td>
          <td><button class="icon-btn" data-remove="${line.id}" aria-label="Remove">${NK.ico ? '' : ''}×</button></td>
        </tr>`;
      }
      const sizes = Object.entries(line.sizes || {}).filter(([, n]) => n > 0).map(([s, n]) => `${s}: ${n}`).join(' · ') || '—';
      const warn = t.ok ? '' : `<p class="moq-inline">Below MOQ (${t.moq}). Add ${t.moq - t.qty} more.</p>`;
      const colorName = (COLORS[line.color] || { n: line.color }).n;
      return `<tr data-id="${line.id}" class="${t.ok ? '' : 'is-short'}">
        <td class="q-prod"><a href="product.html?slug=${line.slug}"><img src="${line.image}" alt=""></a>
          <div><b><a href="product.html?slug=${line.slug}">${line.name}</a></b><small>${line.sku} · MOQ ${t.moq} pcs</small>${warn}</div></td>
        <td>${colorName}<div class="q-sizes">${sizes}</div></td>
        <td>${t.qty}</td>
        <td>${fmt(t.unit)}${t.discount ? `<small class="tier-chip">–${t.discount}%</small>` : ''}</td>
        <td>${fmt(t.ext)}</td>
        <td><button class="icon-btn" data-remove="${line.id}" aria-label="Remove">×</button></td>
      </tr>`;
    }).join('');

    const g = quoteGrand(items);
    $('#sumPieces').textContent = g.pieces;
    $('#sumSub').textContent = fmt(g.sub);
    $('#sumNote').textContent = g.ok
      ? 'All lines meet their style MOQ. Submit and the trade desk will confirm stock and lead time.'
      : 'One or more styles are below their MOQ. Adjust quantities before submitting.';
    $('#submitQuote').disabled = !g.ok;
  }

  document.addEventListener('click', e => {
    const btn = e.target.closest('[data-remove]');
    if (!btn) return;
    quoteSet(quoteGet().filter(l => l.id !== btn.dataset.remove));
    render();
  });
  document.addEventListener('input', e => {
    const inp = e.target.closest('[data-custom-qty]');
    if (!inp) return;
    const items = quoteGet();
    const line = items.find(l => l.id === inp.dataset.customQty);
    if (line) { line.qty = Math.max(0, Math.floor(Number(inp.value) || 0)); quoteSet(items); render(); }
  });

  $('#quoteForm').addEventListener('submit', e => {
    e.preventDefault();
    const g = quoteGrand();
    if (!g.ok) { toast('Please bring every style up to its MOQ before submitting.', 'warn'); return; }
    if (!$('#quoteForm').checkValidity()) { $('#quoteForm').reportValidity(); return; }
    const fd = new FormData($('#quoteForm'));
    const rec = {
      id: 'RFQ-' + Date.now().toString().slice(-6),
      at: new Date().toISOString(),
      status: 'Pending review',
      business: Object.fromEntries(fd.entries()),
      items: quoteGet(),
      totals: g
    };
    const prev = JSON.parse(localStorage.getItem('nkw_submitted_quotes') || '[]');
    prev.unshift(rec);
    localStorage.setItem('nkw_submitted_quotes', JSON.stringify(prev));
    quoteSet([]);
    $('#quoteSheet').hidden = true;
    $('#quoteSuccess').hidden = false;
    $('#rfqRef').textContent = rec.id;
  });

  const user = authGet();
  if (user) {
    if ($('#bizName') && user.business) $('#bizName').value = user.business;
    if ($('#bizEmail') && user.email) $('#bizEmail').value = user.email;
  }
  render();
})();
