/* Catalogue boot — filled from #nk-boot (server) with helper functions. */
(function () {
  'use strict';
  var el = document.getElementById('nk-boot');
  var boot = {};
  if (el) {
    try { boot = JSON.parse(el.textContent || '{}') || {}; }
    catch (e) { boot = {}; }
  }
  window.NK_BOOT = boot;

  window.nkUrl = function (path, query) {
    var base = (boot.base || '').replace(/\/$/, '');
    path = String(path || '').replace(/^\//, '');
    var url = (base ? base + '/' : '/') + path;
    if (query && typeof query === 'object') {
      var qs = Object.keys(query).filter(function (k) { return query[k] !== '' && query[k] != null; })
        .map(function (k) { return encodeURIComponent(k) + '=' + encodeURIComponent(query[k]); }).join('&');
      if (qs) url += (url.indexOf('?') >= 0 ? '&' : '?') + qs;
    }
    return url || '/';
  };
  window.nkCsrf = function () {
    return boot.csrf || (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
  };

  function pex(id, w) {
    w = w || 1200;
    return 'https://images.pexels.com/photos/' + id + '/pexels-photo-' + id + '.jpeg?auto=compress&cs=tinysrgb&w=' + w;
  }
  window.pex = pex;

  window.AGES = boot.ages || [];
  window.SEASONS = boot.seasons || [];
  window.CATS = boot.cats || [];
  window.COLORS = boot.colors || {};
  window.CAMPAIGN = boot.campaign || {};
  window.ACCOUNT_MANAGER = boot.manager || {};
  window.TRADE_CONTACT = boot.contact || {};

  var products = (boot.products || []).map(function (p) {
    var copy = Object.assign({}, p);
    copy.id = Number(copy.id);
    copy.moq = Number(copy.moq);
    copy.base_price = Number(copy.base_price);
    copy.images = copy.images || [];
    copy.img = copy.img || copy.image || copy.images[0] || '';
    copy.tiers = copy.tiers || [];
    if (!copy.tiers.length) copy.tiers = tiersFor(copy.moq || 12);
    return copy;
  });
  window.PRODUCTS = products;

  var TIER_BREAKS = [
    { min: 12, max: 49, discount: 0 },
    { min: 50, max: 199, discount: 8 },
    { min: 200, max: 499, discount: 15 },
    { min: 500, max: null, discount: 22 }
  ];
  function tiersFor(moq) {
    var tiers = [];
    var started = false;
    TIER_BREAKS.forEach(function (t) {
      var top = t.max == null ? Infinity : t.max;
      if (top < moq) return;
      if (!started) { tiers.push({ min: moq, max: t.max, discount: t.discount }); started = true; }
      else tiers.push({ min: t.min, max: t.max, discount: t.discount });
    });
    return tiers;
  }
  function tierForQty(tiers, qty) {
    return (tiers || []).find(function (t) { return qty >= t.min && (t.max == null || qty <= t.max); }) || tiers[0];
  }
  function unitPrice(base, discount) {
    return Math.round(base * (1 - (discount || 0) / 100));
  }
  window.tiersFor = tiersFor;
  window.tierForQty = tierForQty;
  window.unitPrice = unitPrice;

  window.FABRICS = Array.from(new Set(products.map(function (p) { return p.fabric; }).filter(Boolean))).sort();
  window.MOQ_OPTIONS = Array.from(new Set(products.map(function (p) { return p.moq; }))).sort(function (a, b) { return a - b; });
  window.bySlug = function (slug) { return products.find(function (p) { return p.slug === slug; }); };
  window.byId = function (id) { return products.find(function (p) { return Number(p.id) === Number(id); }); };
})();
