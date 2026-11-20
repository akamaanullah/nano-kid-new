/* ==========================================================================
   Nano Kids Wholesale — shared UI (vanilla JS)
   Layout partials (header/footer) are injected so a future PHP MVC can
   replace mountLayout() with includes / views without rewriting pages.
   ========================================================================== */
(() => {
'use strict';

const $  = (s, r = document) => r.querySelector(s);
const $$ = (s, r = document) => [...r.querySelectorAll(s)];
const fmt = n => 'Rs. ' + Math.round(n).toLocaleString('en-US');
const page = document.body.dataset.page || '';
const params = new URLSearchParams(location.search);
const labelOf = (list, key) => (list.find(x => x.key === key) || {}).label || key;

const QUOTE_KEY = 'nkw_quote';
const AUTH_KEY  = 'nkw_auth';
const APPS_KEY  = 'nkw_applications';
const RFQ_KEY   = 'nkw_submitted_quotes';

window.NK = {
  $, $$, fmt, page, params, labelOf,
  QUOTE_KEY, AUTH_KEY,
  quoteGet, quoteSet, quoteCount, quoteClear,
  linePieces, lineTotals, quoteGrand,
  authGet, authSet, authClear, requireAuth,
  toast, ico, openModal, closePanels, openPanel,
  initReveals
};

/* ----- icons ----- */
const ICONS = {
  search:'<circle cx="11" cy="11" r="6.5"/><path d="M16 16l4.5 4.5"/>',
  heart:'<path d="M12 20s-7.5-4.6-7.5-10.2A4.3 4.3 0 0 1 12 7.4a4.3 4.3 0 0 1 7.5 2.4C19.5 15.4 12 20 12 20z"/>',
  bag:'<path d="M5 8h14l-1 12H6L5 8z"/><path d="M9 8V6.5a3 3 0 0 1 6 0V8"/>',
  menu:'<path d="M4 8h16M4 16h16"/>',
  close:'<path d="M5 5l14 14M19 5L5 19"/>',
  plus:'<path d="M12 5v14M5 12h14"/>',
  minus:'<path d="M5 12h14"/>',
  arrow:'<path d="M4 12h15M13 6l6 6-6 6"/>',
  down:'<path d="M6 9l6 6 6-6"/>',
  check:'<path d="M5 12.5l4.5 4.5L19 7.5"/>',
  truck:'<path d="M3 6.5h11v9H3zM14 10h4l3 3v2.5h-7"/><circle cx="7" cy="17.5" r="1.8"/><circle cx="17" cy="17.5" r="1.8"/>',
  leaf:'<path d="M5 19c0-8 5-13 14-14 0 9-5 14-13 14"/><path d="M5 19c3-4 6-6 9-8"/>',
  swap:'<path d="M4 9h13l-3-3M20 15H7l3 3"/>',
  gift:'<rect x="4" y="9" width="16" height="11"/><path d="M3 9h18v-3H3zM12 6v14M12 6c-1-3-5-3-4-.5M12 6c1-3 5-3 4-.5"/>',
  shield:'<path d="M12 3l7 3v5.5c0 4.3-3 8-7 9.5-4-1.5-7-5.2-7-9.5V6z"/><path d="M9 12l2.2 2.2L15.5 10"/>',
  phone:'<path d="M6 3.5h3l1.5 4-2 1.5a11 11 0 0 0 6.5 6.5l1.5-2 4 1.5v3a2 2 0 0 1-2 2A16 16 0 0 1 4 5.5a2 2 0 0 1 2-2z"/>',
  mail:'<rect x="3" y="5.5" width="18" height="13"/><path d="M3.5 6.5L12 13l8.5-6.5"/>',
  pin:'<path d="M12 21s-6.5-5.7-6.5-11a6.5 6.5 0 0 1 13 0c0 5.3-6.5 11-6.5 11z"/><circle cx="12" cy="10" r="2.3"/>',
  clock:'<circle cx="12" cy="12" r="8.5"/><path d="M12 7v5l3 2"/>',
  insta:'<rect x="4" y="4" width="16" height="16" rx="4.5"/><circle cx="12" cy="12" r="3.7"/><circle cx="16.8" cy="7.2" r=".6" fill="currentColor"/>',
  fb:'<path d="M14 21v-8h2.7l.5-3.2H14V8c0-1 .5-1.8 1.8-1.8h1.5V3.4A18 18 0 0 0 15 3.2c-2.5 0-4.2 1.5-4.2 4.2v2.4H8V13h2.8v8"/>',
  chat:'<path d="M4 19l1.2-3.8A8 8 0 1 1 8.8 18.8z"/>',
  grid:'<rect x="4" y="4" width="6.5" height="6.5"/><rect x="13.5" y="4" width="6.5" height="6.5"/><rect x="4" y="13.5" width="6.5" height="6.5"/><rect x="13.5" y="13.5" width="6.5" height="6.5"/>',
  list:'<path d="M4 6h16M4 12h16M4 18h16"/>',
  upload:'<path d="M12 16V5M7.5 9.5L12 5l4.5 4.5M5 19h14"/>',
  filter:'<path d="M4 7h10M18 7h2M4 17h2M10 17h10"/><circle cx="16" cy="7" r="2"/><circle cx="8" cy="17" r="2"/>',
  user:'<circle cx="12" cy="8" r="3.2"/><path d="M5 19c1.5-3.5 4-5 7-5s5.5 1.5 7 5"/>',
  file:'<path d="M7 3h7l5 5v13H7z"/><path d="M14 3v5h5"/>',
  warn:'<path d="M12 4l9 16H3z"/><path d="M12 10v4M12 17h.01"/>'
};
function ico(name, cls = 'ico') {
  return `<svg class="${cls}" viewBox="0 0 24 24" aria-hidden="true">${ICONS[name] || ''}</svg>`;
}

/* ----- quote store ----- */
function quoteGet() {
  try { return JSON.parse(localStorage.getItem(QUOTE_KEY) || '[]'); }
  catch (e) { return []; }
}
function quoteSet(items) {
  localStorage.setItem(QUOTE_KEY, JSON.stringify(items));
  updateBadges();
}
function quoteClear() { quoteSet([]); }
function quoteCount() {
  return quoteGet().reduce((n, line) => n + linePieces(line), 0);
}
function linePieces(line) {
  if (line.custom) return Number(line.qty) || 0;
  return Object.values(line.sizes || {}).reduce((n, v) => n + (Number(v) || 0), 0);
}
function productQtyInQuote(productId, items) {
  return (items || quoteGet())
    .filter(l => Number(l.product_id) === Number(productId))
    .reduce((n, l) => n + linePieces(l), 0);
}
function lineTotals(line, product) {
  const p = product || byId(line.product_id);
  const qty = linePieces(line);
  if (!p) {
    const unit = line.unit_price || 0;
    return { qty, unit, discount: 0, ext: unit * qty, moq: line.moq || 0, tier: null, ok: qty >= (line.moq || 0) };
  }
  /* tier is based on this product's total pieces in the quote (all colourways) */
  const allQty = productQtyInQuote(p.id);
  const tier = tierForQty(p.tiers, Math.max(allQty, qty));
  const unit = unitPrice(p.base_price, tier.discount);
  return { qty, unit, discount: tier.discount, ext: unit * qty, moq: p.moq, tier, ok: qty >= p.moq, product: p };
}
function quoteGrand(items) {
  const list = items || quoteGet();
  let pieces = 0, sub = 0, ok = true;
  list.forEach(line => {
    const t = lineTotals(line);
    pieces += t.qty; sub += t.ext; if (!t.ok) ok = false;
  });
  return { pieces, sub, ok, count: list.length };
}

function addToQuote(payload) {
  /* payload: { product_id, color, sizes: {S:2,...} } or custom line */
  if (payload.custom) {
    const items = quoteGet();
    payload.id = payload.id || ('c' + Date.now());
    items.push(payload);
    quoteSet(items);
    toast('Custom programme added to your quote');
    return { ok: true };
  }
  const p = byId(payload.product_id);
  if (!p) return { ok: false, error: 'Product not found' };
  const qty = Object.values(payload.sizes || {}).reduce((n, v) => n + (Number(v) || 0), 0);
  if (qty < p.moq) {
    return { ok: false, error: `This style has a minimum order of ${p.moq} pieces. You have ${qty}.` };
  }
  const items = quoteGet();
  const existing = items.find(l => Number(l.product_id) === p.id && l.color === payload.color && !l.custom);
  if (existing) {
    Object.keys(payload.sizes).forEach(s => {
      existing.sizes[s] = (Number(existing.sizes[s]) || 0) + (Number(payload.sizes[s]) || 0);
    });
  } else {
    items.push({
      id: 'l' + Date.now() + Math.random().toString(16).slice(2, 6),
      product_id: p.id,
      sku: p.sku,
      name: p.name,
      slug: p.slug,
      image: p.img,
      color: payload.color,
      sizes: { ...payload.sizes },
      moq: p.moq,
      base_price: p.base_price
    });
  }
  quoteSet(items);
  toast(`${p.name} added to your quote`);
  return { ok: true };
}
window.NK.addToQuote = addToQuote;
window.NK.lineTotals = lineTotals;
window.NK.productQtyInQuote = productQtyInQuote;

/* ----- auth ----- */
function authGet() {
  try { return JSON.parse(localStorage.getItem(AUTH_KEY) || 'null'); }
  catch (e) { return null; }
}
function authSet(user) { localStorage.setItem(AUTH_KEY, JSON.stringify(user)); }
function authClear() { localStorage.removeItem(AUTH_KEY); }
function requireAuth() {
  if (authGet()) return true;
  location.href = 'login.html?next=' + encodeURIComponent(location.pathname.split('/').pop());
  return false;
}

/* ----- layout ----- */
const MSGS = [
  'Trade pricing · MOQ is set per style, from 12 pieces',
  'Dispatch in 7–10 working days on in-stock programmes',
  'Private label and custom programmes <a href="private-label.html">Enquire</a>'
];
const logoHTML = `<a class="logo" href="index.html" aria-label="Nano Kids Wholesale home"><span class="logo-word">Nano<i>Kids</i></span><span class="logo-sub">Wholesale Trade</span></a>`;

function megaHTML() {
  const col = (title, list, param) => `<div><h4>${title}</h4><ul>${list.map(x =>
    `<li><a href="catalog.html?${param}=${x.key}">${x.label}${x.range ? `<small>${x.range}</small>` : ''}</a></li>`).join('')}</ul></div>`;
  const mid = Math.ceil(CATS.length / 2);
  return `<div class="mega"><div class="mega-inner">
    ${col('By age', AGES, 'age')}${col('By season', SEASONS, 'season')}${col('Category', CATS.slice(0, mid), 'cat')}${col('More categories', CATS.slice(mid), 'cat')}
  </div>
  <div class="mega-foot"><a class="mega-cta" href="private-label.html">Private label programmes <span aria-hidden="true">→</span></a></div>
  </div>`;
}
function mobileMenuHTML() {
  const sub = (t, list, p) => `<h5>${t}</h5>` + list.map(x => `<a href="catalog.html?${p}=${x.key}">${x.label}</a>`).join('');
  const user = authGet();
  return `<aside class="mobile-menu" id="mobileMenu" aria-label="Menu">
    <div class="mm-top">${logoHTML}<button class="icon-btn" data-close-menu aria-label="Close menu">${ico('close')}</button></div>
    <ul class="mm-list">
      <li><a href="catalog.html">Catalogue</a></li>
      <li><button class="mm-acc-btn" aria-expanded="false">Shop by ${ico('plus')}</button>
        <div class="mm-sub"><div class="mm-sub-inner">${sub('By age', AGES, 'age')}${sub('By season', SEASONS, 'season')}${sub('By category', CATS, 'cat')}</div></div></li>
      <li><a href="private-label.html" style="color:var(--accent-text)">Private label</a></li>
      <li><a href="apply.html">Become a dealer</a></li>
      <li><a href="about.html">About</a></li>
      <li><a href="faq.html">FAQ</a></li>
      <li><a href="contact.html">Contact</a></li>
      <li><a href="${user ? 'dashboard.html' : 'login.html'}">${user ? 'Dashboard' : 'Trade login'}</a></li>
    </ul>
    <div class="mm-foot">${TRADE_CONTACT.email}<br>${TRADE_CONTACT.phone}</div>
  </aside>`;
}
function headerHTML() {
  const act = h => location.pathname.endsWith(h) ? 'is-active' : '';
  const user = authGet();
  return `
  <div class="site-chrome">
  <div class="announce" role="region" aria-label="Announcements">${MSGS.map((m, i) => `<div class="announce-msg ${i ? '' : 'is-on'}">${m}</div>`).join('')}</div>
  <header class="site-header" id="siteHeader">
    <div class="wrap header-inner">
      <button class="icon-btn menu-toggle" data-open-menu aria-label="Open menu">${ico('menu')}</button>
      <ul class="main-nav">
        <li class="has-mega"><a href="catalog.html" class="${act('catalog.html')}">Catalogue</a>${megaHTML()}</li>
          <li><a class="nav-accent ${act('private-label.html')}" href="private-label.html">Private Label</a></li>
        <li><a href="apply.html" class="${act('apply.html')}">Become a dealer</a></li>
        <li><a href="about.html" class="${act('about.html')}">About</a></li>
      </ul>
      ${logoHTML}
      <div class="header-actions">
        <button class="icon-btn" data-open-search aria-label="Search">${ico('search')}</button>
        <a class="icon-btn hide-sm" href="${user ? 'dashboard.html' : 'login.html'}" aria-label="${user ? 'Dashboard' : 'Trade login'}">${ico('user')}</a>
        <a class="icon-btn" href="quote.html" aria-label="Quote list">${ico('file')}<span class="badge" data-quote-count>0</span></a>
      </div>
    </div>
  </header>
  </div>
  ${mobileMenuHTML()}
  <div class="scrim" id="scrim"></div>
  <div class="search-overlay" id="searchOverlay" role="dialog" aria-label="Search">
    <div class="search-top"><input type="search" id="searchInput" placeholder="Search styles, SKUs, fabrics…" autocomplete="off">
      <button class="icon-btn" data-close-search aria-label="Close search">${ico('close')}</button></div>
    <div class="search-body"><div class="search-results" id="searchResults"></div></div>
  </div>`;
}
function footerHTML() {
  const li = (list, p) => list.map(x => `<li><a href="catalog.html?${p}=${x.key}">${x.label}</a></li>`).join('');
  return `<footer class="site-footer"><div class="footer-accent" aria-hidden="true"></div><div class="wrap">
    <div class="footer-grid">
      <div class="footer-brand">${logoHTML}<p>Wholesale clothing for retailers, shops and distributors — baby and kids programmes from newborn to 14, with per-style MOQs, volume tiers and private label.</p>
        <div class="socials"><a href="#" aria-label="Instagram">${ico('insta')}</a><a href="#" aria-label="Facebook">${ico('fb')}</a><a href="#" aria-label="WhatsApp">${ico('chat')}</a></div></div>
      <div><h5>Catalogue</h5><ul><li><a href="catalog.html">All styles</a></li>${li(AGES, 'age')}<li><a href="private-label.html">Private label</a></li></ul></div>
      <div><h5>Programmes</h5><ul>${li(SEASONS, 'season')}</ul></div>
      <div><h5>Trade</h5><ul><li><a href="apply.html">Become a dealer</a></li><li><a href="login.html">Trade login</a></li><li><a href="faq.html">MOQ &amp; terms</a></li><li><a href="quote.html">Request a quote</a></li><li><a href="contact.html">Contact trade</a></li></ul></div>
      <div class="footer-contact"><h5>Trade desk</h5><ul>
        <li>${ico('pin')}<span>${TRADE_CONTACT.warehouse}</span></li>
        <li>${ico('phone')}<span>${TRADE_CONTACT.phone}</span></li>
        <li>${ico('mail')}<span>${TRADE_CONTACT.email}</span></li>
        <li>${ico('clock')}<span>Mon – Sat, 10 am – 6 pm</span></li></ul></div>
    </div>
    <div class="footer-bottom"><span>© 2026 Nano Kids Wholesale. All rights reserved.</span>
      <div class="pay-chips"><span>Bank transfer</span><span>50% advance</span><span>Trade credit*</span><span>NTN invoiced</span></div>
      <nav><a href="faq.html">Terms</a><a href="faq.html">Shipping</a><a href="faq.html">Returns</a></nav></div>
  </div></footer>
  <div class="toast" id="toast" role="status" aria-live="polite"></div>`;
}

function mountLayout() {
  document.body.insertAdjacentHTML('afterbegin', headerHTML());
  document.body.insertAdjacentHTML('beforeend', footerHTML());
  const msgs = $$('.announce-msg'); let mi = 0;
  if (msgs.length > 1) setInterval(() => { msgs[mi].classList.remove('is-on'); mi = (mi + 1) % msgs.length; msgs[mi].classList.add('is-on'); }, 4200);
  const hdr = $('#siteHeader');
  const chrome = $('.site-chrome');
  const onScroll = () => {
    const on = scrollY > 10;
    hdr && hdr.classList.toggle('is-scrolled', on);
    chrome && chrome.classList.toggle('is-scrolled', on);
  };
  onScroll(); addEventListener('scroll', onScroll, { passive: true });
  updateBadges();
  bindChrome();
  initRipple();
  initCounters();
  initReveals();
}

function updateBadges() {
  const n = quoteGet().length;
  $$('[data-quote-count]').forEach(el => { el.textContent = n; el.hidden = n === 0; });
}

const scrim = () => $('#scrim');
function openPanel(el) { el.classList.add('open'); scrim().classList.add('show'); document.body.classList.add('no-scroll'); }
function closePanels() {
  $$('.mobile-menu.open, .filters.open').forEach(e => e.classList.remove('open'));
  $$('.modal.open').forEach(e => { e.classList.remove('open'); e.setAttribute('aria-hidden', 'true'); });
  const so = $('#searchOverlay'); if (so) so.classList.remove('open');
  const sc = scrim(); if (sc) sc.classList.remove('show');
  document.body.classList.remove('no-scroll');
  $$('.filter-footer').forEach(f => f.classList.remove('open'));
}
function openModal(el) { el.classList.add('open'); el.setAttribute('aria-hidden', 'false'); document.body.classList.add('no-scroll'); }
function toast(msg, kind) {
  const t = $('#toast'); if (!t) return;
  t.innerHTML = ico(kind === 'warn' ? 'warn' : 'check') + `<span>${msg}</span>`;
  t.classList.toggle('toast--warn', kind === 'warn');
  t.classList.add('show');
  clearTimeout(toast._t); toast._t = setTimeout(() => t.classList.remove('show'), 3200);
}

function bindChrome() {
  document.addEventListener('click', e => {
    if (e.target.closest('[data-open-menu]')) openPanel($('#mobileMenu'));
    if (e.target.closest('[data-close-menu], [data-close-search], #scrim')) closePanels();
    if (e.target.closest('[data-open-search]')) { $('#searchOverlay').classList.add('open'); document.body.classList.add('no-scroll'); setTimeout(() => $('#searchInput')?.focus(), 50); }
    const acc = e.target.closest('.mm-acc-btn');
    if (acc) {
      const open = acc.getAttribute('aria-expanded') === 'true';
      acc.setAttribute('aria-expanded', String(!open));
      acc.parentElement.classList.toggle('open', !open);
    }
  });
  const input = $('#searchInput');
  if (input) input.addEventListener('input', () => renderSearch(input.value.trim()));
}

function renderSearch(q) {
  const box = $('#searchResults'); if (!box) return;
  if (q.length < 2) { box.innerHTML = '<p class="muted">Type a style, SKU or fabric.</p>'; return; }
  const low = q.toLowerCase();
  const hits = PRODUCTS.filter(p =>
    (p.name + p.sku + p.fabric + p.category + p.age_group).toLowerCase().includes(low)
  ).slice(0, 8);
  box.innerHTML = hits.length ? hits.map(p => `<a class="search-hit" href="product.html?slug=${p.slug}">
    <img src="${p.img}" alt=""><span><b>${p.name}</b><small>${p.sku} · MOQ ${p.moq} · ${fmt(p.base_price)} / pc</small></span></a>`).join('')
    : '<p class="muted">No styles matched that search.</p>';
}

/* ----- cards ----- */
function packLabel(p) {
  if (!p.pack_units) return '';
  return p.pack_ratio ? `Pack of ${p.pack_units}: ${p.pack_ratio}` : `Pack of ${p.pack_units}`;
}
function stockLabel(s) {
  return { in_stock: 'In stock', limited: 'Limited', made_to_order: 'Made to order' }[s] || s;
}
function productCard(p) {
  const first = unitPrice(p.base_price, p.tiers[0].discount);
  const pack = packLabel(p);
  return `<article class="pcard" data-reveal>
    <a class="pcard-media" href="product.html?slug=${p.slug}">
      ${p.tag ? `<span class="pcard-tag">${p.tag}</span>` : ''}
      <img src="${p.img}" alt="${p.name}" loading="lazy">
    </a>
    <div class="pcard-info">
      <p class="pcard-meta">${labelOf(CATS, p.category)} · ${labelOf(AGES, p.age_group)}</p>
      <a class="pcard-name" href="product.html?slug=${p.slug}">${p.name}</a>
      <p class="pcard-sku">${p.sku}</p>
      <div class="pcard-trade">
        <span class="pcard-price">${fmt(first)} <small>/ pc</small></span>
        <span class="moq-badge">MOQ ${p.moq}</span>
      </div>
      ${pack ? `<p class="pcard-pack">${pack}</p>` : ''}
      <a class="btn btn--pill btn--ghost btn--sm" href="product.html?slug=${p.slug}">View &amp; add to quote</a>
    </div>
  </article>`;
}
window.NK.productCard = productCard;
window.NK.packLabel = packLabel;
window.NK.stockLabel = stockLabel;

/* ----- motion ----- */
function initRipple() {
  document.addEventListener('click', e => {
    const btn = e.target.closest('.btn');
    if (!btn || matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    const r = document.createElement('span');
    r.className = 'ripple';
    const box = btn.getBoundingClientRect();
    const size = Math.max(box.width, box.height);
    r.style.width = r.style.height = size + 'px';
    r.style.left = (e.clientX - box.left - size / 2) + 'px';
    r.style.top = (e.clientY - box.top - size / 2) + 'px';
    btn.appendChild(r);
    setTimeout(() => r.remove(), 650);
  });
}
function initCounters() {
  const els = $$('[data-count]');
  if (!els.length) return;
  const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
  const run = el => {
    const end = Number(el.dataset.count);
    const prefix = el.dataset.prefix || '';
    const suffix = el.dataset.suffix || '';
    if (reduce) { el.textContent = prefix + end + suffix; return; }
    const t0 = performance.now(), dur = 900;
    const tick = now => {
      const k = Math.min(1, (now - t0) / dur);
      const eased = 1 - Math.pow(1 - k, 3);
      el.textContent = prefix + Math.round(end * eased) + suffix;
      if (k < 1) requestAnimationFrame(tick);
    };
    requestAnimationFrame(tick);
  };
  const io = new IntersectionObserver(ents => ents.forEach(e => { if (e.isIntersecting) { run(e.target); io.unobserve(e.target); } }), { threshold: .4 });
  els.forEach(el => io.observe(el));
}
function initReveals() {
  const els = $$('[data-reveal]').filter(e => !e.classList.contains('in') && e.dataset.revealBound !== '1');
  if (!els.length) return;
  if (matchMedia('(prefers-reduced-motion: reduce)').matches) { els.forEach(e => e.classList.add('in')); return; }
  const io = new IntersectionObserver(ents => ents.forEach(e => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } }), { threshold: .12 });
  els.forEach(el => { el.dataset.revealBound = '1'; io.observe(el); });
}

function initHeroSlider() {
  const root = $('#heroSlider');
  if (!root) return;
  const slides = $$('.hero-slide', root);
  const dots = $$('[data-hero-dot]', root);
  const status = $('#heroStatus');
  const countEl = $('#heroCount');
  const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
  const DURATION = 6000;
  let i = 0, timer = null, paused = false, startedAt = 0, remaining = DURATION;

  const setPaused = on => {
    if (on) {
      if (!paused && startedAt) remaining = Math.max(200, DURATION - (Date.now() - startedAt));
      paused = true; root.classList.add('is-paused'); stop();
    } else {
      paused = false; root.classList.remove('is-paused');
      if (!reduce) resume();
    }
  };
  function go(n) {
    slides[i].classList.remove('is-on');
    slides[i].setAttribute('aria-hidden', 'true');
    if (dots[i]) { dots[i].classList.remove('is-on'); dots[i].removeAttribute('aria-current'); }
    i = (n + slides.length) % slides.length;
    if (dots[i]) {
      const bar = dots[i].querySelector('i');
      if (bar) { bar.style.animation = 'none'; void bar.offsetWidth; bar.style.animation = ''; }
      dots[i].classList.add('is-on');
      dots[i].setAttribute('aria-current', 'true');
    }
    slides[i].classList.add('is-on');
    slides[i].setAttribute('aria-hidden', 'false');
    if (status) status.textContent = `Slide ${i + 1} of ${slides.length}`;
    if (countEl) countEl.textContent = String(i + 1).padStart(2, '0');
    remaining = DURATION;
    if (!reduce && !paused) start();
  }
  function start() { stop(); if (reduce || paused) return; remaining = DURATION; startedAt = Date.now(); timer = setTimeout(() => go(i + 1), DURATION); }
  function resume() { stop(); if (reduce || paused) return; startedAt = Date.now() - (DURATION - remaining); timer = setTimeout(() => go(i + 1), remaining); }
  function stop() { if (timer) { clearTimeout(timer); timer = null; } }

  $('[data-hero-prev]', root)?.addEventListener('click', () => go(i - 1));
  $('[data-hero-next]', root)?.addEventListener('click', () => go(i + 1));
  dots.forEach(d => d.addEventListener('click', () => go(Number(d.dataset.heroDot))));
  root.addEventListener('mouseenter', () => setPaused(true));
  root.addEventListener('mouseleave', () => setPaused(false));
  let tx = 0, ty = 0, tActive = false;
  root.addEventListener('touchstart', e => { if (!e.changedTouches[0]) return; tActive = true; tx = e.changedTouches[0].clientX; ty = e.changedTouches[0].clientY; setPaused(true); }, { passive: true });
  root.addEventListener('touchend', e => {
    if (!tActive || !e.changedTouches[0]) return;
    tActive = false;
    const dx = e.changedTouches[0].clientX - tx, dy = e.changedTouches[0].clientY - ty;
    if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy)) go(i + (dx < 0 ? 1 : -1));
    setPaused(false);
  }, { passive: true });
  if (!reduce) start();
}

function initHome() {
  initHeroSlider();
  const feat = $('#featuredGrid');
  if (feat) feat.innerHTML = PRODUCTS.filter(p => p.tag === 'Bestseller' || p.tag === 'Core' || p.tag === 'New').slice(0, 8).map(productCard).join('');
  const ageEl = $('#ageGrid');
  if (ageEl) {
    const thumbs = { newborn: CAMPAIGN.baby, baby: CAMPAIGN.pyjama, kids: CAMPAIGN.hoodie, junior: CAMPAIGN.jeans };
    ageEl.innerHTML = AGES.map((a, i) => `<a class="age-card" href="catalog.html?age=${a.key}" data-reveal style="--d:${i * .08}s">
      <img src="${pex(thumbs[a.key] || CAMPAIGN.knit, 800)}" alt="${a.label} clothing" loading="lazy">
      <div class="meta"><div><span class="num">${a.range}</span><h3>${a.label}</h3></div><span class="arrow">${ico('arrow')}</span></div></a>`).join('');
  }
  const catEl = $('#catRow');
  if (catEl) {
    const thumbs = { dresses: CAMPAIGN.festive, tops: CAMPAIGN.cube, trousers: CAMPAIGN.jeans, sets: CAMPAIGN.everyday, outerwear: CAMPAIGN.knit, nightwear: CAMPAIGN.pyjama, ethnic: CAMPAIGN.formal, accessories: CAMPAIGN.baby };
    catEl.innerHTML = CATS.map((c, i) => `<a class="cat-item" href="catalog.html?cat=${c.key}" data-reveal style="--d:${i * .04}s">
      <span class="ring"><img src="${pex(thumbs[c.key] || CAMPAIGN.knit, 400)}" alt=""></span><span>${c.label}</span></a>`).join('');
  }
  initReveals();
}

function bootDemos() {
  $$('form[data-demo]').forEach(f => f.addEventListener('submit', e => {
    e.preventDefault();
    if (!f.checkValidity()) { f.reportValidity(); return; }
    const box = f.querySelector('[data-success]') || f;
    toast('Sent — we will be in touch shortly');
    const next = f.dataset.successPage;
    if (next) setTimeout(() => { location.href = next; }, 600);
    else {
      const msg = f.querySelector('.form-success');
      if (msg) msg.hidden = false;
      f.reset();
    }
  }));
  $$('[data-accordion] .faq-q').forEach(btn => btn.addEventListener('click', () => {
    const item = btn.parentElement;
    const open = item.classList.contains('is-open');
    $$('[data-accordion] .faq-item').forEach(i => i.classList.remove('is-open'));
    if (!open) item.classList.add('is-open');
  }));
}

document.addEventListener('DOMContentLoaded', () => {
  mountLayout();
  if (page === 'home') initHome();
  bootDemos();
});
})();
