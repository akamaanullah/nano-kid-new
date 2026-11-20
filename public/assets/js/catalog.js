/* Catalogue — server-rendered filters; drawer + sort submit. Optional API refresh. */
(() => {
  if (document.body.dataset.page !== 'catalog') return;
  const $ = (s, r = document) => r.querySelector(s);
  const form = $('#filterForm');

  $('#filterOpen')?.addEventListener('click', () => {
    $('#filters')?.classList.add('open');
    document.getElementById('scrim')?.classList.add('show');
    document.body.classList.add('no-scroll');
    document.querySelector('.filter-footer')?.classList.add('open');
  });

  $('#sortSel')?.addEventListener('change', () => {
    const hidden = form?.querySelector('input[name="sort"]');
    if (hidden) hidden.value = $('#sortSel').value;
    if (form) form.submit();
  });

  // Auto-apply when a select changes (desktop). Keep the apply button for the drawer.
  form?.querySelectorAll('select').forEach(sel => {
    sel.addEventListener('change', () => {
      if (window.matchMedia('(min-width: 861px)').matches) form.submit();
    });
  });
})();
