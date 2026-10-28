/* Layer One listing page: filters, sort, load more. Ported from the design's listing.js.
   The products and their finished cards come from the page (JSON in #lo-shop-data). */
(function () {
  var dataEl = document.getElementById('lo-shop-data');
  if (!dataEl) return;
  var D;
  try { D = JSON.parse(dataEl.textContent); } catch (e) { return; }

  var $ = function (s) { return document.querySelector(s); };
  var esc = function (s) { return String(s).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;'); };
  var PAGE = function () { return matchMedia('(max-width:860px)').matches ? 8 : 9; };
  var inr = function (n) { return '₹' + Math.round(n).toLocaleString('en-IN'); };

  var COLORS = Object.keys(D.colours).map(function (id) { return { id: id, label: id.charAt(0).toUpperCase() + id.slice(1), hex: D.colours[id] }; });
  var PRICE = [
    { id: 'any', label: 'Any price' },
    { id: 'u800', label: 'Under ₹800', test: function (p) { return p < 800; } },
    { id: 'm', label: '₹800 – ₹1,500', test: function (p) { return p >= 800 && p <= 1500; } },
    { id: 'o', label: 'Above ₹1,500', test: function (p) { return p > 1500; } }
  ];
  var SORTS = ['featured', 'new', 'low', 'high', 'az'];
  var ITEMS = D.items;

  /* state, read from the URL so links like ?color=red&sale=1 work */
  var q = new URLSearchParams(location.search);
  var csv = function (k) { return (q.get(k) || '').split(',').filter(Boolean); };
  var state = {
    cats: new Set(csv('cat')), colors: new Set(csv('color')),
    price: 'any', min: null, max: null,
    sale: q.get('sale') === '1', isNew: q.get('new') === '1',
    sort: SORTS.indexOf(q.get('sort')) > -1 ? q.get('sort') : 'featured',
    shown: PAGE()
  };
  var rendered = 0;

  /* filtering */
  function priceOk(p) {
    if (state.min != null && p.price < state.min) return false;
    if (state.max != null && p.price > state.max) return false;
    var r = PRICE.filter(function (x) { return x.id === state.price; })[0];
    return !r || !r.test || r.test(p.price);
  }
  var offersOk = function (p) { return (!state.sale || p.sale) && (!state.isNew || p.isNew); };
  var catOk = function (p) { return !state.cats.size || state.cats.has(p.cat); };
  var colorOk = function (p) { return !state.colors.size || (p.colors || []).some(function (c) { return state.colors.has(c); }); };
  function results() {
    var l = ITEMS.filter(function (p) { return catOk(p) && colorOk(p) && priceOk(p) && offersOk(p); });
    var by = {
      featured: function (a, b) { return (b.best | 0) - (a.best | 0) || a.idx - b.idx; },
      new: function (a, b) { return (b.isNew | 0) - (a.isNew | 0) || a.idx - b.idx; },
      low: function (a, b) { return a.price - b.price; },
      high: function (a, b) { return b.price - a.price; },
      az: function (a, b) { return a.name.localeCompare(b.name); }
    };
    return l.sort(by[state.sort]);
  }

  /* filter panel */
  function renderFilters() {
    var base = ITEMS.filter(function (p) { return colorOk(p) && priceOk(p) && offersOk(p); });
    var counts = {};
    ITEMS.forEach(function (p) { if (p.cat) counts[p.cat] = counts[p.cat] || 0; });
    base.forEach(function (p) { if (p.cat) counts[p.cat]++; });
    Array.from(state.cats).forEach(function (c) { if (!(c in counts)) state.cats.delete(c); });
    $('#catPills').innerHTML = '<button type="button" class="cp' + (state.cats.size ? '' : ' on') + '" data-cat="" aria-pressed="' + !state.cats.size + '">All <small>' + base.length + '</small></button>' +
      Object.keys(counts).sort().map(function (c) {
        return '<button type="button" class="cp' + (state.cats.has(c) ? ' on' : '') + '" data-cat="' + esc(c) + '" aria-pressed="' + state.cats.has(c) + '">' + esc(c) + ' <small>' + counts[c] + '</small></button>';
      }).join('');
    $('#factN').textContent = ITEMS.length;
    $('#fPrice').innerHTML = PRICE.map(function (r) {
      return '<label class="ck r"><input type="radio" name="price" value="' + r.id + '" ' + (state.price === r.id && state.min == null && state.max == null ? 'checked' : '') + '><i></i><span>' + r.label + '</span></label>';
    }).join('');
    var cbase = ITEMS.filter(function (p) { return catOk(p) && priceOk(p) && offersOk(p); });
    $('#fColors').innerHTML = COLORS.map(function (c) {
      var n = cbase.filter(function (p) { return (p.colors || []).indexOf(c.id) > -1; }).length, on = state.colors.has(c.id);
      return '<button type="button" class="sw' + (on ? ' on' : '') + '" data-color="' + c.id + '" style="--c:' + c.hex + '" aria-pressed="' + on + '" aria-label="' + c.label + ' (' + n + ')" title="' + c.label + ' · ' + n + '" ' + (n || on ? '' : 'disabled') + '><i></i><span>' + c.label + '</span></button>';
    }).join('');
    var pool = ITEMS.filter(function (p) { return catOk(p) && colorOk(p) && priceOk(p); });
    $('#nSale').textContent = pool.filter(function (p) { return p.sale; }).length;
    $('#nNew').textContent = pool.filter(function (p) { return p.isNew; }).length;
    $('#fSale').checked = state.sale; $('#fNew').checked = state.isNew;
    $('#pMin').value = state.min == null ? '' : state.min; $('#pMax').value = state.max == null ? '' : state.max;
  }

  /* chips */
  function chips() {
    var out = [];
    state.colors.forEach(function (c) { out.push({ label: 'Colour: ' + c.charAt(0).toUpperCase() + c.slice(1), clear: function () { state.colors.delete(c); } }); });
    if (state.min != null || state.max != null) out.push({ label: '₹' + (state.min == null ? 0 : state.min) + ' – ' + (state.max != null ? '₹' + state.max : 'any'), clear: function () { state.min = state.max = null; } });
    else if (state.price !== 'any') out.push({ label: PRICE.filter(function (r) { return r.id === state.price; })[0].label, clear: function () { state.price = 'any'; } });
    if (state.sale) out.push({ label: 'On sale', clear: function () { state.sale = false; } });
    if (state.isNew) out.push({ label: 'New arrivals', clear: function () { state.isNew = false; } });
    return out;
  }
  function renderChips(list) {
    var box = $('#chips'); box.innerHTML = '';
    list.forEach(function (c) {
      var b = document.createElement('button');
      b.type = 'button'; b.innerHTML = esc(c.label) + '<i aria-hidden="true">×</i>'; b.setAttribute('aria-label', 'Remove filter ' + c.label);
      b.onclick = function () { c.clear(); reset(); };
      box.appendChild(b);
    });
    if (list.length > 1) { var b = document.createElement('button'); b.type = 'button'; b.className = 'all'; b.textContent = 'Clear all'; b.onclick = clearAll; box.appendChild(b); }
  }

  /* render */
  function render(append) {
    var l = results(), shown = Math.min(state.shown, l.length), list = chips();
    $('#sort').value = state.sort;
    if (!append) { renderFilters(); renderChips(list); }
    var from = append ? rendered : 0;
    /* the first row is on screen at once: load it straight away instead of waiting for scroll */
    var html = l.slice(from, shown).map(function (p, i) { return (!append && i < 2) ? p.html.replace('loading="lazy"', 'loading="eager" fetchpriority="high"') : p.html; }).join('');
    if (append) $('#lpGrid').insertAdjacentHTML('beforeend', html); else $('#lpGrid').innerHTML = html;
    rendered = shown;

    $('#count').innerHTML = '<b>' + l.length + '</b> product' + (l.length === 1 ? '' : 's');
    $('#shown').textContent = 'Showing ' + shown + ' of ' + l.length;
    $('#progBar').style.width = (l.length ? shown / l.length * 100 : 0) + '%';
    $('#moreWrap').hidden = !l.length;
    $('#loadMore').hidden = shown >= l.length;
    $('#empty').hidden = l.length > 0;
    $('#applyCount').textContent = l.length;
    var n = list.length;
    $('#fCount').textContent = n; $('#fCount').hidden = !n;
    $('#clearAll').hidden = !n;

    /* keep the URL shareable without reloading */
    var p = new URLSearchParams(location.search);
    ['sort', 'cat', 'color', 'sale', 'new'].forEach(function (k) { p.delete(k); });
    if (state.sort !== 'featured') p.set('sort', state.sort);
    if (state.cats.size) p.set('cat', Array.from(state.cats).join(','));
    if (state.colors.size) p.set('color', Array.from(state.colors).join(','));
    if (state.sale) p.set('sale', '1');
    if (state.isNew) p.set('new', '1');
    try { history.replaceState(null, '', location.pathname + (p.toString() ? '?' + p : '')); } catch (e) {}
  }
  function reset() { state.shown = PAGE(); render(); }
  function clearAll() { state.cats.clear(); state.colors.clear(); state.price = 'any'; state.min = state.max = null; state.sale = state.isNew = false; reset(); }

  /* events */
  $('#fColors').addEventListener('click', function (e) {
    var b = e.target.closest('[data-color]'); if (!b) return;
    var c = b.dataset.color; state.colors.has(c) ? state.colors.delete(c) : state.colors.add(c); reset();
  });
  $('#catPills').addEventListener('click', function (e) {
    var b = e.target.closest('[data-cat]'); if (!b) return;
    var c = b.dataset.cat; if (!c) state.cats.clear(); else state.cats.has(c) ? state.cats.delete(c) : state.cats.add(c);
    reset();
  });
  $('#fPrice').addEventListener('change', function (e) { state.price = e.target.value; state.min = state.max = null; reset(); });
  var num = function (v) { return (v === '' || isNaN(+v)) ? null : Math.max(0, +v); };
  var tmr;
  ['#pMin', '#pMax'].forEach(function (s) {
    $(s).addEventListener('input', function () {
      clearTimeout(tmr);
      tmr = setTimeout(function () {
        state.min = num($('#pMin').value); state.max = num($('#pMax').value);
        if (state.min != null || state.max != null) state.price = 'any';
        state.shown = PAGE();
        var a = document.activeElement;
        render();
        if (a && a.id) { var el = document.getElementById(a.id); if (el) el.focus(); }
      }, 350);
    });
  });
  $('#fSale').onchange = function (e) { state.sale = e.target.checked; reset(); };
  $('#fNew').onchange = function (e) { state.isNew = e.target.checked; reset(); };
  $('#sort').onchange = function (e) { state.sort = e.target.value; reset(); };
  $('#clearAll').onclick = $('#emptyClear').onclick = clearAll;
  $('#loadMore').onclick = function () { state.shown += PAGE(); render(true); };

  /* filters sheet on phones */
  var openFilters = function (o) { if (window.LO) LO.sheet($('#filters'), o); };
  if (window.LO && LO.onCloseAll) LO.onCloseAll(function () { openFilters(false); });
  $('#openFilters').onclick = function () { openFilters(true); };
  $('#closeFilters').onclick = $('#applyFilters').onclick = function () { openFilters(false); };
  var mq = matchMedia('(min-width:1101px)');
  mq.addEventListener('change', function () { if (mq.matches) openFilters(false); });

  render();
})();
