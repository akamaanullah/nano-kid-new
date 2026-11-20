/* Nano Kids Wholesale — admin UI */
(() => {
  'use strict';

  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];

  /* ----- sidebar ----- */
  const openBtn = $('[data-open-sidebar]');
  const scrim = $('#adminScrim');
  const closeSidebar = () => document.body.classList.remove('sidebar-open');
  if (openBtn) openBtn.addEventListener('click', () => document.body.classList.add('sidebar-open'));
  if (scrim) scrim.addEventListener('click', closeSidebar);
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeSidebar(); });

  /* ----- toast ----- */
  const toastEl = $('#adminToast');
  function toast(msg, kind) {
    if (!toastEl || !msg) return;
    toastEl.textContent = msg;
    toastEl.classList.remove('is-ok', 'is-error');
    if (kind) toastEl.classList.add(kind === 'error' ? 'is-error' : 'is-ok');
    toastEl.classList.add('is-on');
    clearTimeout(toastEl._t);
    toastEl._t = setTimeout(() => toastEl.classList.remove('is-on'), 3800);
  }
  window.adminToast = toast;
  const flashOk = $('.flash--ok');
  const flashErr = $('.flash--error');
  if (flashOk) toast(flashOk.textContent.trim(), 'ok');
  if (flashErr) toast(flashErr.textContent.trim(), 'error');

  /* ----- confirm dialog ----- */
  const box = $('#confirmBox');
  const boxScrim = $('#confirmScrim');
  let pendingForm = null;
  function openConfirm(message, form) {
    if (!box) return form && form.submit();
    pendingForm = form;
    $('#confirmBody').textContent = message || 'Please confirm this action.';
    box.classList.add('is-on');
    if (boxScrim) boxScrim.classList.add('is-on');
  }
  function closeConfirm() {
    pendingForm = null;
    if (box) box.classList.remove('is-on');
    if (boxScrim) boxScrim.classList.remove('is-on');
  }
  document.addEventListener('click', e => {
    const btn = e.target.closest('[data-confirm]');
    if (!btn) return;
    const form = btn.form || btn.closest('form');
    if (!form) return;
    e.preventDefault();
    openConfirm(btn.getAttribute('data-confirm'), form);
  });
  $('[data-confirm-cancel]')?.addEventListener('click', closeConfirm);
  $('[data-confirm-ok]')?.addEventListener('click', () => {
    const form = pendingForm;
    closeConfirm();
    if (form) form.submit();
  });
  boxScrim?.addEventListener('click', closeConfirm);

  /* ----- table search + pagination ----- */
  function bindTable(table) {
    const size = Number(table.dataset.pageSize || 12);
    const search = table.closest('.panel')?.querySelector('[data-table-search]');
    const pager = table.closest('.panel')?.querySelector('[data-pager]');
    const rows = [...table.tBodies[0]?.rows || []];
    let page = 1;
    let q = '';

    function visible() {
      const needle = q.trim().toLowerCase();
      return rows.filter(r => !needle || r.textContent.toLowerCase().includes(needle));
    }
    function render() {
      const list = visible();
      const pages = Math.max(1, Math.ceil(list.length / size));
      if (page > pages) page = pages;
      rows.forEach(r => { r.hidden = true; });
      list.forEach((r, i) => {
        const start = (page - 1) * size;
        r.hidden = i < start || i >= start + size;
      });
      if (pager) {
        if (pages <= 1) { pager.innerHTML = ''; return; }
        pager.innerHTML = Array.from({ length: pages }, (_, i) =>
          `<button type="button" data-page="${i + 1}" class="${i + 1 === page ? 'is-on' : ''}">${i + 1}</button>`
        ).join('');
      }
    }
    search?.addEventListener('input', () => { q = search.value; page = 1; render(); });
    pager?.addEventListener('click', e => {
      const b = e.target.closest('[data-page]');
      if (!b) return;
      page = Number(b.dataset.page);
      render();
    });
    render();
  }
  $$('table[data-table]').forEach(bindTable);
  $$('select[data-auto-submit]').forEach(el => el.addEventListener('change', () => el.form && el.form.submit()));

  /* ----- colour chip toggle ----- */
  $('#colorPick')?.addEventListener('change', e => {
    const lab = e.target.closest('label');
    if (lab) lab.classList.toggle('is-on', e.target.checked);
  });

  /* ----- size chips ----- */
  const sizesInput = $('#sizesInput');
  const sizeChips = $('#sizeChips');
  const sizeAdd = $('#sizeAdd');
  function sizes() {
    return (sizesInput?.value || '').split(/[\n,]+/).map(s => s.trim()).filter(Boolean);
  }
  function setSizes(list) {
    if (!sizesInput) return;
    sizesInput.value = list.join(', ');
    if (!sizeChips) return;
    sizeChips.innerHTML = list.map(s =>
      `<span class="chip">${s}<button type="button" data-remove-size="${s}" aria-label="Remove ${s}">×</button></span>`
    ).join('');
  }
  if (sizesInput) setSizes(sizes());
  sizeChips?.addEventListener('click', e => {
    const btn = e.target.closest('[data-remove-size]');
    if (!btn) return;
    setSizes(sizes().filter(s => s !== btn.dataset.removeSize));
  });
  sizeAdd?.addEventListener('keydown', e => {
    if (e.key !== 'Enter' && e.key !== ',') return;
    e.preventDefault();
    const v = sizeAdd.value.trim().replace(/,$/, '');
    if (!v) return;
    const list = sizes();
    if (!list.includes(v)) list.push(v);
    setSizes(list);
    sizeAdd.value = '';
  });

  /* ----- images ----- */
  const preview = $('#imagePreview');
  function addFig(src, hiddenValue) {
    if (!preview || !src) return;
    const fig = document.createElement('figure');
    fig.innerHTML = `<img src="${src}" alt=""><input type="hidden" name="image_urls[]" value="${hiddenValue || src}"><button type="button" data-remove-img aria-label="Remove">×</button>`;
    preview.appendChild(fig);
  }
  preview?.addEventListener('click', e => {
    const btn = e.target.closest('[data-remove-img]');
    if (btn) btn.closest('figure')?.remove();
  });
  $('#imageInput')?.addEventListener('change', e => {
    const file = e.target.files && e.target.files[0];
    if (!file) return;
    const url = URL.createObjectURL(file);
    addFig(url, '');
    // keep the file input; the empty hidden is not used as URL
    const last = preview?.querySelector('figure:last-child input');
    if (last) last.remove();
  });
  $('#addImageUrl')?.addEventListener('click', () => {
    const inp = $('#imageUrlInput');
    const val = (inp?.value || '').trim();
    if (!val) return;
    addFig(val, val);
    inp.value = '';
  });

  /* ----- tier / MOQ validation ----- */
  const moqInput = $('#moqInput');
  const editor = $('#tierEditor');
  const msg = $('#tierMsg');

  function validateTiers() {
    if (!editor) return true;
    const moq = Number(moqInput?.value || 0);
    const rows = [...editor.querySelectorAll('tbody tr')];
    let ok = true;
    let firstMin = null;
    const notes = [];
    rows.forEach((row, i) => {
      const minEl = row.querySelector('[name="tier_min[]"]');
      const maxEl = row.querySelector('[name="tier_max[]"]');
      const min = Number(minEl.value || 0);
      const max = maxEl.value === '' ? null : Number(maxEl.value);
      let bad = false;
      if (!min || min < 1) { bad = true; notes.push(`Tier ${i + 1}: enter a minimum quantity.`); }
      else if (moq && min < moq) { bad = true; notes.push(`Tier ${i + 1}: minimum cannot be below the MOQ (${moq}).`); }
      if (max !== null && max < min) { bad = true; notes.push(`Tier ${i + 1}: maximum must be at or above the minimum.`); }
      row.classList.toggle('is-bad', bad);
      if (firstMin === null && min) firstMin = min;
      if (bad) ok = false;
    });
    if (ok && moq && firstMin !== moq) {
      ok = false;
      notes.push(`The first price tier must start at the product MOQ (${moq}).`);
      rows[0]?.classList.add('is-bad');
    }
    if (msg) {
      msg.textContent = notes[0] || (ok ? 'Tiers start at the MOQ and are ready to save.' : '');
      msg.className = 'tier-msg ' + (ok ? 'is-ok' : 'is-bad');
    }
    return ok;
  }
  editor?.addEventListener('input', validateTiers);
  moqInput?.addEventListener('input', () => {
    const first = editor?.querySelector('[name="tier_min[]"]');
    const moq = Number(moqInput.value || 0);
    if (first && moq && (!first.value || Number(first.value) < moq)) first.value = moq;
    validateTiers();
  });
  $('#addTier')?.addEventListener('click', () => {
    const body = editor.querySelector('tbody');
    const tr = document.createElement('tr');
    tr.innerHTML = `<td><input type="number" name="tier_min[]" min="1" required></td>
      <td><input type="number" name="tier_max[]" min="1" placeholder="Open"></td>
      <td><input type="number" name="tier_discount[]" min="0" max="90" step="0.01" value="0"></td>
      <td><button type="button" class="btn btn--ghost btn--sm" data-remove-tier>Remove</button></td>`;
    body.appendChild(tr);
    validateTiers();
  });
  editor?.addEventListener('click', e => {
    const btn = e.target.closest('[data-remove-tier]');
    if (!btn) return;
    const rows = editor.querySelectorAll('tbody tr');
    if (rows.length <= 1) return;
    btn.closest('tr')?.remove();
    validateTiers();
  });
  $('#productForm')?.addEventListener('submit', e => {
    if (!validateTiers()) {
      e.preventDefault();
      toast(msg?.textContent || 'Please fix the price tiers before saving.', 'error');
    }
  });
  if (editor) validateTiers();
})();
