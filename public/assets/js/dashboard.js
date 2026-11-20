/* Dealer dashboard — tab switching only (records come from the server). */
(() => {
  if (document.body.dataset.page !== 'dashboard') return;
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];
  document.addEventListener('click', e => {
    const tab = e.target.closest('[data-tab]');
    if (!tab) return;
    $$('[data-tab]').forEach(t => t.classList.toggle('is-on', t === tab));
    $$('[data-panel]').forEach(p => { p.hidden = p.dataset.panel !== tab.dataset.tab; });
  });
})();
