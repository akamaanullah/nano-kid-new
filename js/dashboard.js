/* Trade dashboard — demo records after login */
(() => {
  if (document.body.dataset.page !== 'dashboard') return;
  if (!NK.requireAuth()) return;
  const { $, $$, fmt, authGet, authClear, toast } = NK;
  const user = authGet();

  $('#dashName').textContent = user.business || user.name || 'Your account';
  $('#dashEmail').textContent = user.email;
  $('#mgrName').textContent = ACCOUNT_MANAGER.name;
  $('#mgrRole').textContent = ACCOUNT_MANAGER.role;
  $('#mgrPhone').textContent = ACCOUNT_MANAGER.phone;
  $('#mgrEmail').textContent = ACCOUNT_MANAGER.email;
  $('#mgrHours').textContent = ACCOUNT_MANAGER.hours;

  const submitted = JSON.parse(localStorage.getItem('nkw_submitted_quotes') || '[]');
  const demoOrders = [
    { id: 'SO-10428', date: '12 Nov 2026', status: 'Dispatched', pieces: 240, total: 268800, invoice: 'INV-3911' },
    { id: 'SO-10391', date: '28 Oct 2026', status: 'In packing', pieces: 96, total: 112320, invoice: 'INV-3874' },
    { id: 'SO-10340', date: '9 Oct 2026', status: 'Delivered', pieces: 180, total: 198540, invoice: 'INV-3810' }
  ];
  const demoInvoices = [
    { id: 'INV-3911', date: '12 Nov 2026', amount: 268800, status: 'Due 26 Nov' },
    { id: 'INV-3874', date: '28 Oct 2026', amount: 112320, status: 'Paid' },
    { id: 'INV-3810', date: '9 Oct 2026', amount: 198540, status: 'Paid' }
  ];

  $('#orderTable tbody').innerHTML = demoOrders.map(o => `<tr>
    <td>${o.id}</td><td>${o.date}</td><td><span class="status-pill status-${o.status.replace(/\s+/g,'-').toLowerCase()}">${o.status}</span></td>
    <td>${o.pieces} pcs</td><td>${fmt(o.total)}</td>
    <td><button class="link" type="button" data-reorder="${o.id}">Reorder</button></td>
  </tr>`).join('');

  const quoteRows = submitted.length
    ? submitted.slice(0, 6).map(q => `<tr><td>${q.id}</td><td>${new Date(q.at).toLocaleDateString('en-GB', { day:'numeric', month:'short', year:'numeric' })}</td><td><span class="status-pill">${q.status}</span></td><td>${q.totals.pieces} pcs</td><td>${fmt(q.totals.sub)}</td></tr>`)
    : `<tr><td>RFQ-18420</td><td>4 Nov 2026</td><td><span class="status-pill">Quoted</span></td><td>120 pcs</td><td>${fmt(142800)}</td></tr>
       <tr><td>RFQ-18311</td><td>19 Oct 2026</td><td><span class="status-pill">Accepted</span></td><td>200 pcs</td><td>${fmt(210000)}</td></tr>`;
  $('#quoteTable tbody').innerHTML = quoteRows;

  $('#invoiceTable tbody').innerHTML = demoInvoices.map(i => `<tr>
    <td>${i.id}</td><td>${i.date}</td><td>${fmt(i.amount)}</td><td>${i.status}</td>
  </tr>`).join('');

  document.addEventListener('click', e => {
    if (e.target.closest('#logoutBtn')) { authClear(); location.href = 'login.html'; }
    if (e.target.closest('[data-reorder]')) {
      toast('Those lines have been added as a starting point on a new quote — review quantities and submit.');
      location.href = 'catalog.html';
    }
    const tab = e.target.closest('[data-tab]');
    if (tab) {
      $$('[data-tab]').forEach(t => t.classList.toggle('is-on', t === tab));
      $$('[data-panel]').forEach(p => p.hidden = p.dataset.panel !== tab.dataset.tab);
    }
  });
})();
