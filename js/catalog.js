/* Catalogue filters + grid. Query params: age, cat, season, fabric, moq, sort, q */
(() => {
  if (document.body.dataset.page !== 'catalog') return;
  const { $, $$, fmt, params, productCard, labelOf } = NK;

  const state = {
    age: params.get('age') || '',
    cat: params.get('cat') || '',
    season: params.get('season') || '',
    fabric: params.get('fabric') || '',
    moq: params.get('moq') || '',
    stock: params.get('stock') || '',
    min: Number(params.get('min') || 0),
    max: Number(params.get('max') || 0),
    sort: params.get('sort') || 'featured',
    q: params.get('q') || ''
  };
  const MAXP = Math.max(...PRODUCTS.map(p => p.base_price));
  if (!state.max) state.max = MAXP;

  function filtered() {
    return PRODUCTS.filter(p =>
      (!state.age || p.age_group === state.age) &&
      (!state.cat || p.category === state.cat) &&
      (!state.season || p.season === state.season) &&
      (!state.fabric || p.fabric === state.fabric) &&
      (!state.moq || String(p.moq) === String(state.moq)) &&
      (!state.stock || p.stock_status === state.stock) &&
      p.base_price >= state.min && p.base_price <= state.max &&
      (!state.q || (p.name + p.sku + p.fabric).toLowerCase().includes(state.q.toLowerCase()))
    ).sort((a, b) => {
      if (state.sort === 'price-asc') return a.base_price - b.base_price;
      if (state.sort === 'price-desc') return b.base_price - a.base_price;
      if (state.sort === 'moq') return a.moq - b.moq;
      if (state.sort === 'name') return a.name.localeCompare(b.name);
      return a.id - b.id;
    });
  }

  function options(list, key, labelFn) {
    return `<option value="">All</option>` + list.map(x => {
      const v = typeof x === 'object' ? x.key : x;
      const l = typeof x === 'object' ? x.label : labelFn ? labelFn(x) : x;
      return `<option value="${v}" ${String(state[key]) === String(v) ? 'selected' : ''}>${l}</option>`;
    }).join('');
  }

  function renderFilters() {
    const el = $('#filters');
    el.innerHTML = `
      <div class="filters-head"><h3>Filters</h3><button class="link" type="button" id="clearAll">Clear all</button></div>
      <label class="field"><span>Age group</span><select data-f="age">${options(AGES, 'age')}</select></label>
      <label class="field"><span>Category</span><select data-f="cat">${options(CATS, 'cat')}</select></label>
      <label class="field"><span>Season</span><select data-f="season">${options(SEASONS, 'season')}</select></label>
      <label class="field"><span>Fabric</span><select data-f="fabric">${options(FABRICS, 'fabric')}</select></label>
      <label class="field"><span>MOQ</span><select data-f="moq">${options(MOQ_OPTIONS, 'moq', v => v + ' pcs')}</select></label>
      <label class="field"><span>Availability</span><select data-f="stock">
        <option value="">All</option>
        <option value="in_stock" ${state.stock==='in_stock'?'selected':''}>In stock</option>
        <option value="limited" ${state.stock==='limited'?'selected':''}>Limited</option>
        <option value="made_to_order" ${state.stock==='made_to_order'?'selected':''}>Made to order</option>
      </select></label>
      <div class="field"><span>Wholesale price / pc</span>
        <div class="price-row"><input type="number" data-f="min" min="0" value="${state.min}" aria-label="Min price">
        <input type="number" data-f="max" min="0" value="${state.max}" aria-label="Max price"></div>
      </div>`;
    el.addEventListener('change', onChange);
    $('#clearAll').addEventListener('click', () => {
      Object.assign(state, { age:'', cat:'', season:'', fabric:'', moq:'', stock:'', min:0, max:MAXP, q:'', sort:'featured' });
      render();
    });
  }

  function onChange(e) {
    const t = e.target.closest('[data-f]'); if (!t) return;
    const k = t.dataset.f;
    state[k] = (k === 'min' || k === 'max') ? Number(t.value || 0) : t.value;
    renderGrid();
    pushUrl();
  }

  function titleFor() {
    if (state.age) return `${labelOf(AGES, state.age)} <em>programme</em>`;
    if (state.cat) return `${labelOf(CATS, state.cat)}`;
    if (state.season) return `${labelOf(SEASONS, state.season)} <em>edit</em>`;
    return 'Trade <em>catalogue</em>';
  }

  function renderGrid() {
    const list = filtered();
    $('#productGrid').innerHTML = list.length ? list.map(productCard).join('') : '<p class="empty-note">No styles match those filters. Clear one or two and try again.</p>';
    $('#resultCount').textContent = list.length + (list.length === 1 ? ' style' : ' styles');
    const t = titleFor();
    $('#shopTitle').innerHTML = t;
    $('#crumbCur').textContent = $('#shopTitle').textContent;
    if (NK.initReveals) NK.initReveals();
  }

  function pushUrl() {
    const p = new URLSearchParams();
    ['age','cat','season','fabric','moq','stock','sort','q'].forEach(k => state[k] && p.set(k, state[k]));
    if (state.min) p.set('min', state.min);
    if (state.max && state.max !== MAXP) p.set('max', state.max);
    history.replaceState(null, '', 'catalog.html' + (p.toString() ? '?' + p : ''));
  }

  function render() {
    renderFilters();
    renderGrid();
    $('#sortSel').value = state.sort;
    pushUrl();
  }

  $('#sortSel').addEventListener('change', e => { state.sort = e.target.value; renderGrid(); pushUrl(); });
  $('#filterOpen')?.addEventListener('click', () => {
    $('#filters').classList.add('open');
    $('#scrim').classList.add('show');
    document.body.classList.add('no-scroll');
    $('.filter-footer')?.classList.add('open');
  });
  $('#filterApply')?.addEventListener('click', () => NK.$.bind ? null : document.dispatchEvent(new Event('click')));
  document.addEventListener('click', e => {
    if (e.target.closest('#filterApply') || e.target.closest('#filterReset')) {
      if (e.target.closest('#filterReset')) {
        Object.assign(state, { age:'', cat:'', season:'', fabric:'', moq:'', stock:'', min:0, max:MAXP });
        render();
      }
      document.getElementById('scrim')?.click();
    }
  });

  render();
})();
