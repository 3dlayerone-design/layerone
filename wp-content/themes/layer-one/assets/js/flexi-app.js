/* Flexi Crew collectibles page (front end), shared by the section blocks. Ported from the design's flexi2.js.
   Each section (hero, most loved, bundles, all figures, box builder) is its own block; this script starts whichever
   sections are on the page. Data comes from the catalogue JSON embedded once in the page; the cart is WooCommerce. */
(function () {
  var dataEl = document.getElementById('lo-flexi-data');
  if (!dataEl) return;
  var D;
  try { D = JSON.parse(dataEl.textContent); } catch (e) { return; }

  var $ = function (s) { return document.querySelector(s); };
  var $$ = function (s) { return Array.prototype.slice.call(document.querySelectorAll(s)); };

  /* ---------- data ---------- */
  var figs = D.figures || [];
  var byId = {}; figs.forEach(function (f) { byId[f.id] = f; });
  var seriesById = {}; (D.series || []).forEach(function (s) { seriesById[s.id] = s; });
  var groups = D.groups || [];
  var groupById = {}, groupBySlug = {}; groups.forEach(function (g) { groupById[g.id] = g; groupBySlug[g.slug] = g; });
  var bundleByTerm = {}; (D.bundles || []).forEach(function (b) { bundleByTerm[b.term] = b; });
  var sizes = (D.box && D.box.sizes) || [];
  var bonusSize = (D.box && D.box.bonusSize) || 0, bonusCount = (D.box && D.box.bonusCount) || 0;
  if (hasBox && !sizes.length) return;

  document.body.classList.add('lo-has-flexi');
  var hasBox = !!document.getElementById('crate');
  var reduce = matchMedia('(prefers-reduced-motion:reduce)').matches;
  var fine = matchMedia('(hover:hover) and (pointer:fine)').matches;

  var inr = function (n) { return '₹' + Math.round(n).toLocaleString('en-IN'); };
  var pad = function (n) { return String(n).padStart(2, '0'); };
  var esc = function (s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); };
  var poster = function (f) { return f.thumb || f.image || ''; };
  var inSeries = function (sid) { return figs.filter(function (f) { return f.series === sid; }); };
  var inGroup = function (g) { return figs.filter(function (f) { return f.bundle === g.id; }); };
  var hasTag = function (f, t) { return (f.tags || []).indexOf(t) > -1; };
  var shuffle = function (a) { return a.slice().sort(function () { return Math.random() - .5; }); };
  var toast = function (m) { if (window.LO && LO.toast) LO.toast(m); };
  var priceHTML = function (f) { return (f.onSale && f.regular ? '<s>' + inr(f.regular) + '</s> ' : '') + inr(f.price); };
  var sources = function (f) {
    return (f.webm ? '<source src="' + esc(f.webm) + '" type="video/webm">' : '') + (f.mp4 ? '<source src="' + esc(f.mp4) + '" type="video/mp4">' : '');
  };


  /* ---------- cart helpers ---------- */
  function post(action, data) {
    var body = new URLSearchParams(Object.assign({ action: action, nonce: window.LAYERONE ? LAYERONE.nonce : '' }, data));
    return fetch(window.LAYERONE.ajax, { method: 'POST', credentials: 'same-origin', body: body }).then(function (r) { return r.json(); });
  }
  function afterAdd(message) {
    var done = function () { if (window.LO) { LO.bump(); } toast('✓ ' + message); };
    window.LO && LO.refreshCart ? LO.refreshCart().then(done) : done();
  }
  /* bundle or single figure: straight into the cart */
  function addProduct(id, btn) {
    if (btn) btn.disabled = true;
    post('layerone_flexi_add_product', { product_id: id })
      .then(function (j) { if (j && j.success) afterAdd(j.data.message); else toast((j && j.data && j.data.message) || 'It could not be added to your cart.'); })
      .catch(function () { toast('Could not reach the shop. Please try again.'); })
      .then(function () { if (btn) btn.disabled = false; });
  }

  /* ---------- figure card (used everywhere) ---------- */
  function card(f) {
    var v = !!f.mp4, p = poster(f);
    return '<article class="v2-card' + (f.inStock ? '' : ' soldout') + '" data-id="' + f.id + '" style="--tint:' + esc(f.tint) + '">' +
      '<button type="button" class="v2-media" data-qv="' + f.id + '" aria-label="Look closer at ' + esc(f.name) + ' the ' + esc(f.animal.toLowerCase()) + '">' +
      (p ? '<img src="' + esc(p) + '" alt="" loading="lazy" decoding="async">' : '<span class="v2-ph">#' + pad(f.no) + '</span>') +
      (v ? '<video muted playsinline preload="none" data-id="' + f.id + '" aria-hidden="true"></video>' : '') +
      '</button>' +
      (f.inStock && hasBox ? '<button type="button" class="v2-add" data-inc="' + f.id + '" aria-label="Add ' + esc(f.name) + ' to your box"><svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg><span class="v2-n"></span></button>' : '') +
      '<div class="v2-cap"><b>' + esc(f.name) + '</b><span>' + esc(f.animal) + '</span></div></article>';
  }

  /* Figures for a section: picked by hand (data-ids), else with the product tag (data-tag), else the ones that have a video. */
  function pickFigures(sec, defaultTag, videoFallback) {
    var ids = [];
    try { ids = JSON.parse((sec && sec.dataset.ids) || '[]'); } catch (e) {}
    if (ids.length) return ids.map(function (id) { return byId[id]; }).filter(Boolean);
    var tag = (sec && sec.dataset.tag !== undefined) ? sec.dataset.tag : defaultTag;
    var tagged = tag ? figs.filter(function (f) { return hasTag(f, tag); }) : [];
    if (tagged.length) return tagged;
    return videoFallback ? figs.filter(function (f) { return f.mp4; }) : figs;
  }

  /* ---------- hero wiggle wall ---------- */
  (function hero() {
    var show = $('#heroShow'); if (!show) return;
    var list = shuffle(pickFigures(show, 'flexi-wall', true));
    if (!list.length) { show.hidden = true; return; }
    var half = Math.ceil(list.length / 2);
    var tile = function (f) {
      return '<button type="button" class="fx-tile" data-qv="' + f.id + '" aria-label="Look closer at ' + esc(f.name) + '" style="--tint:' + esc(f.tint) + '">' +
        '<img src="' + esc(poster(f)) + '" alt="" loading="lazy" decoding="async">' +
        '<video muted loop playsinline preload="none" data-wall="' + f.id + '" aria-hidden="true"></video>' +
        '<span class="fx-tname">' + esc(f.name) + '</span></button>';
    };
    var row = function (l, d) { return '<div class="fx-row ' + d + '" style="--n:' + l.length + '"><div class="fx-belt">' + l.map(tile).join('') + l.map(tile).join('') + '</div></div>'; };
    show.innerHTML = '<div class="fx-wall">' + row(list.slice(0, half), 'l') + row(list.slice(half), 'r') + '</div>';
    var wall = show.querySelector('.fx-wall');
    function wake(v) {
      var f = byId[v.dataset.wall];
      if (!v.dataset.src) {
        v.dataset.src = 1; v.preload = 'auto'; v.innerHTML = sources(f);
        v.addEventListener('loadeddata', function () { v.parentNode.classList.add('ready'); }, { once: true });
        v.load();
      }
      if (!reduce && !qvOpen()) v.play().catch(function () {});
    }
    if ('IntersectionObserver' in window) {
      var io = new IntersectionObserver(function (es) {
        es.forEach(function (en) {
          var v = en.target.querySelector('video');
          if (en.isIntersecting) wake(v); else if (v.dataset.src) v.pause();
        });
      }, { root: wall, rootMargin: '0px 260px' });
      wall.querySelectorAll('.fx-tile').forEach(function (t) { io.observe(t); });
    }
  })();

  /* ---------- most loved shelf ---------- */
  var loved = pickFigures($('#loved'), 'most-loved', true);
  if ($('#lovedShelf')) {
    if (!loved.length) { var ls = $('#loved'); if (ls) ls.hidden = true; }
    $('#lovedShelf').innerHTML = loved.map(card).join('');
  }
  $$('[data-shelf]').forEach(function (b) {
    b.onclick = function () { var sh = $('#lovedShelf'); sh.scrollBy({ left: +b.dataset.shelf * sh.clientWidth * .8, behavior: 'smooth' }); };
  });

  /* ---------- bundles ---------- */
  var heads = function (g) {
    return inGroup(g).filter(function (f) { return poster(f); }).filter(function (f, i) { return i % 2 === 0; }).slice(0, 6)
      .map(function (f) { return '<img src="' + esc(poster(f)) + '" alt="' + esc(f.name) + '" title="' + esc(f.name) + '" loading="lazy" style="--tint:' + esc(f.tint) + '">'; }).join('');
  };
  function renderBento() {
    var ids = [];
    try { ids = JSON.parse(($('#bundles') && $('#bundles').dataset.ids) || '[]'); } catch (e) {}
    var bundles = (D.bundles || []).filter(function (b) { return groupById[b.term] && (!ids.length || ids.indexOf(b.id) > -1); });
    if (ids.length) bundles.sort(function (a, b) { return ids.indexOf(a.id) - ids.indexOf(b.id); });
    var featured = bundles.filter(function (b) { return b.badge; })[0] || bundles[0];
    var order = featured ? [featured].concat(bundles.filter(function (b) { return b !== featured; })) : [];
    $('#bento').innerHTML = order.map(function (b, k) {
      var g = groupById[b.term], sids = g.series;
      var first = inGroup(g)[0];
      var banner = b.banner || (first ? poster(first) : '');
      var t1 = seriesById[sids[0]] ? seriesById[sids[0]].tint : '#dfe4e4', t2 = seriesById[sids[1]] ? seriesById[sids[1]].tint : t1;
      var names = sids.map(function (s) { return seriesById[s] ? seriesById[s].name : ''; }).join(' and ');
      var more = Math.max(0, inGroup(g).length - 6);
      return '<article class="v2-bundle' + (k === 0 ? ' feat' : '') + '" style="--t1:' + esc(t1) + ';--t2:' + esc(t2) + '">' +
        '<button type="button" class="v2-bimg" data-open="' + esc(g.slug) + '" aria-label="Open the ' + esc(g.name) + ' bundle"><img src="' + esc(banner) + '" alt="" loading="lazy">' + (b.badge ? '<span class="v2-tag">' + esc(b.badge) + '</span>' : '') + '</button>' +
        '<div class="v2-binfo"><h3>' + esc(g.name) + '</h3><p class="v2-series">' + esc(names) + '</p>' +
        (k === 0 && b.line ? '<p class="v2-line">' + esc(b.line) + '</p>' : '') +
        '<div class="v2-heads">' + heads(g) + (more ? '<span>+' + more + '</span>' : '') + '</div>' +
        '<div class="v2-bfoot"><span class="v2-bprice"><b>' + (b.price != null ? priceHTML(b) : '') + '</b> for all ' + inGroup(g).length + '</span>' +
        '<span class="v2-bbtn"><button type="button" class="btn btn-dark btn-sm" data-open="' + esc(g.slug) + '">Open bundle</button>' +
        (b.canBuy ? '<button type="button" class="v2-ghost" data-bundle="' + b.id + '">Add to cart</button>' : '<button type="button" class="v2-ghost" disabled>' + (b.inStock ? 'Unavailable' : 'Sold out') + '</button>') +
        '</span></div></div></article>';
    }).join('');
  }
  if ($('#bento')) renderBento();

  /* ---------- all figures + series chips ---------- */
  var filter = 'all';
  var allNote = $('#allNote');
  var hasAll = !!$('#allGrid');
  if (hasAll) (function chips() {
    var list = [['all', 'All ' + figs.length, '#ffffff']].concat((D.series || []).map(function (s) { return [String(s.id), s.name, s.tint]; }));
    $('#chips').innerHTML = list.map(function (c) {
      return '<button type="button" role="tab" data-filter="' + c[0] + '" aria-selected="' + (c[0] === 'all') + '" style="--tint:' + esc(c[2]) + '">' + (c[0] === 'all' ? '' : '<i></i>') + esc(c[1]) + '</button>';
    }).join('');
  })();
  function renderAll() {
    var list = filter === 'all' ? figs : inSeries(+filter);
    $('#allGrid').innerHTML = list.map(card).join('');
    if (filter === 'all') { allNote.textContent = allNote.dataset.default; }
    else {
      var s = seriesById[+filter], g = s && groupById[s.bundle];
      allNote.textContent = s.name + ': ' + list.length + ' figures' + (g ? ', part of the ' + g.name + ' bundle.' : '.');
    }
    $$('#chips button').forEach(function (b) { b.setAttribute('aria-selected', b.dataset.filter === filter); });
    refresh(); watch();
  }
  if (hasAll) $('#chips').addEventListener('click', function (e) { var b = e.target.closest('[data-filter]'); if (b) { filter = b.dataset.filter; renderAll(); } });

  /* ---------- box ---------- */
  var sizeIx = Math.max(0, sizes.map(function (z) { return z.n; }).indexOf(12));
  var picked = new Map();
  var count = function () { var n = 0; picked.forEach(function (q) { n += q; }); return n; };
  var extraFor = function (ix) { return (bonusSize && sizes[ix].n === bonusSize) ? bonusCount : 0; };
  var capacity = function () { return sizes[sizeIx].n + extraFor(sizeIx); };
  var maxN = sizes[sizes.length - 1].n + extraFor(sizes.length - 1);
  var flatPicks = function () { var a = []; picked.forEach(function (q, id) { for (var i = 0; i < q; i++) a.push(id); }); return a; };

  if (hasBox) $('#sizes').innerHTML = sizes.map(function (z, k) {
    return '<button type="button" role="radio" data-size="' + k + '"><b>' + z.n + '</b><span>figures' + (extraFor(k) ? ' +' + extraFor(k) + ' free' : '') + '</span><em>' + inr(z.price / z.n) + ' each</em>' + (z.tag ? '<i>' + esc(z.tag) + '</i>' : '') + '</button>';
  }).join('');
  if (hasBox) $('#sizes').addEventListener('click', function (e) {
    var b = e.target.closest('[data-size]'); if (!b) return;
    var k = +b.dataset.size;
    if (count() > sizes[k].n + extraFor(k)) return toast('You have ' + count() + ' figures. Remove some first, or pick a bigger box.');
    sizeIx = k; refresh();
  });

  function add(ids) {
    var n = 0;
    for (var i = 0; i < ids.length; i++) {
      var f = byId[ids[i]]; if (!f) continue;
      if (count() >= maxN) { toast('A box holds up to ' + maxN + ' figures.'); break; }
      if (!f.inStock) { toast(f.name + ' is sold out'); continue; }
      if ((picked.get(f.id) || 0) >= f.max) { toast('Only ' + f.max + ' of ' + f.name + ' available'); continue; }
      if (count() >= capacity() && sizeIx < sizes.length - 1) { sizeIx++; toast('Box upgraded to ' + sizes[sizeIx].n + ' figures'); }
      picked.set(f.id, (picked.get(f.id) || 0) + 1); n++;
    }
    refresh(); return n;
  }
  function remove(id) { var q = (picked.get(id) || 0) - 1; if (q > 0) picked.set(id, q); else picked.delete(id); refresh(); }

  function refresh() {
    var z = sizes[sizeIx], n = count(), left = z ? z.n - n : 0, ex = z ? extraFor(sizeIx) : 0;
    var full = z ? (n === z.n || (ex && n === z.n + ex)) : false;
    var flat = flatPicks();

    if (hasBox) {
      $$('#sizes button').forEach(function (b, k) { b.classList.toggle('on', k === sizeIx); b.setAttribute('aria-checked', k === sizeIx); });
      $('#boxPrice').textContent = inr(z.price);
      var line;
      if (left > 0) line = n + ' of ' + z.n + ' picked, ' + left + ' to go';
      else if (ex && n < z.n + ex) line = 'Box of ' + z.n + ' is full. Add ' + (z.n + ex - n) + ' more as your free bonus';
      else if (ex) line = 'Box of ' + z.n + ' + ' + ex + ' bonus is full';
      else line = 'Box of ' + z.n + ' is full';
      $('#boxLine').textContent = line;
      $('#addBoxBtn').disabled = !full;

      var slots = z.n + ex, html = '';
      $('#crate').style.setProperty('--cols', z.n <= 6 ? 3 : z.n <= 12 ? 4 : z.n <= 24 ? 6 : 8);
      for (var i = 0; i < slots; i++) {
        var f = byId[flat[i]], bonus = i >= z.n ? ' bonus' : '';
        html += f
          ? '<button type="button" class="v2-slot full' + bonus + '" data-dec="' + f.id + '" style="--tint:' + esc(f.tint) + '" aria-label="Remove ' + esc(f.name) + '"><img src="' + esc(poster(f)) + '" alt=""></button>'
          : '<span class="v2-slot' + bonus + '"' + (bonus ? ' title="Free bonus figure"' : '') + '></span>';
      }
      $('#crate').innerHTML = html;
    }

    $$('.v2-card').forEach(function (c) {
      var q = picked.get(+c.dataset.id) || 0;
      c.classList.toggle('in', q > 0);
      var nEl = c.querySelector('.v2-n'); if (nEl) nEl.textContent = q > 1 ? q : '';
    });

    var thumbs = flat.slice(-3).map(function (id) { return '<img src="' + esc(poster(byId[id])) + '" alt="">'; }).join('');
    var tb = $('#tabBox');
    if (tb && z) { tb.hidden = n === 0; $('#tabCount').textContent = n + ' / ' + z.n; $('#tabThumbs').innerHTML = thumbs; }
    if ($('#boxBar') && z) {
      $('#bbN').textContent = n + ' / ' + z.n;
      $('#bbThumbs').innerHTML = thumbs; $('#bbThumbs').hidden = !n;
      $('#bbS').textContent = left > 0 ? inr(z.price) + ' · box of ' + z.n : inr(z.price) + ' · box is full';
      var bb = $('#bbAdd'); bb.classList.toggle('wait', !full);
      bb.textContent = !full ? 'Pick ' + Math.max(1, left) + ' more' : 'Add box to cart';
    }
    if ($('#qv') && !$('#qv').hidden) qvAct();
  }

  if (hasBox) $('#mysteryBtn').onclick = function () {
    var left = sizes[sizeIx].n - count(); if (left <= 0) return toast('Your box is already full');
    var pool = shuffle(figs.filter(function (f) { return f.inStock && !picked.has(f.id); }));
    add(pool.slice(0, left).map(function (f) { return f.id; })); toast(left + ' mystery figures added');
  };
  if ($('#bbAdd')) $('#bbAdd').onclick = function () {
    var z = sizes[sizeIx], n = count(), ex = extraFor(sizeIx);
    if (!(n === z.n || (ex && n === z.n + ex))) { var t = document.getElementById('box'); if (t) t.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' }); return; }
    $('#addBoxBtn').click();
  };
  if (hasBox) $('#clearBtn').onclick = function () { picked.clear(); refresh(); };
  if (hasBox) $('#addBoxBtn').onclick = function () {
    var z = sizes[sizeIx], btn = this, map = {};
    picked.forEach(function (q, id) { map[id] = q; });
    btn.disabled = true;
    post('layerone_flexi_add_box', { size: z.n, figures: JSON.stringify(map) })
      .then(function (j) {
        if (j && j.success) { picked.clear(); refresh(); afterAdd(j.data.message.replace(/ added to your cart$/, '') + ' added to your cart'); }
        else { toast((j && j.data && j.data.message) || 'The box could not be added.'); }
      })
      .catch(function () { toast('Could not reach the shop. Please try again.'); })
      .then(function () { refresh(); });
  };

  /* ---------- bundle panel ---------- */
  var current = null;
  var hasSheet = !!$('#sheet'), hasQv = !!$('#qv');
  if (hasSheet) $('#sheetTabs').innerHTML = groups.map(function (g) { return '<button type="button" data-open="' + esc(g.slug) + '">' + esc(g.name) + '</button>'; }).join('');
  function openBundle(slug) {
    var g = groupBySlug[slug]; if (!g) return;
    current = g;
    var b = bundleByTerm[g.id], sh = $('#sheet');
    var t1 = seriesById[g.series[0]] ? seriesById[g.series[0]].tint : '#dfe4e4', t2 = seriesById[g.series[1]] ? seriesById[g.series[1]].tint : t1;
    sh.style.setProperty('--t1', t1); sh.style.setProperty('--t2', t2);
    $$('#sheetTabs button').forEach(function (x) { x.classList.toggle('on', x.dataset.open === slug); });
    var list = inGroup(g);
    var names = g.series.map(function (s) { return seriesById[s] ? seriesById[s].name : ''; }).join(' and ');
    var banner = b && b.banner ? '<img class="v2-sban" src="' + esc(b.banner) + '" alt="">' : '';
    var buy = b ? (b.canBuy ? '<button type="button" class="btn btn-dark" data-bundle="' + b.id + '">Add bundle to cart · ' + priceHTML(b) + '</button>'
      : '<button type="button" class="btn btn-dark" disabled>' + (b.inStock ? 'Unavailable' : 'Sold out') + '</button>') : '';
    $('#sheetBody').innerHTML = '<div class="wrap"><header class="v2-shead">' + banner +
      '<div><h2 id="sheetName">' + esc(g.name) + '</h2><p>' + esc((b && b.line) || '') + ' ' + list.length + ' figures from ' + esc(names) + '.</p>' +
      '<div class="v2-shbtns">' + buy + (hasBox ? '<button type="button" class="v2-ghost" data-addall="' + esc(g.slug) + '">Add all ' + list.length + ' to my box</button>' : '') + '</div></div></header>' +
      g.series.map(function (sid) {
        var s = seriesById[sid]; if (!s) return '';
        var l = inSeries(sid);
        return '<section class="v2-sgroup" style="--tint:' + esc(s.tint) + '"><div class="v2-sghead"><h3>' + esc(s.name) + '</h3>' + (hasBox ? '<button type="button" class="v2-ghost" data-addseries="' + sid + '">Add all ' + l.length + '</button>' : '') + '</div>' +
          '<div class="v2-grid six">' + l.map(card).join('') + '</div></section>';
      }).join('') + '</div>';
    sh.hidden = false; sh.scrollTop = 0; document.body.classList.add('lock');
    requestAnimationFrame(function () { sh.classList.add('on'); });
    try { history.replaceState(null, '', '#' + slug); } catch (e) {}
    refresh(); watch();
  }
  function closeBundle() {
    var sh = $('#sheet'); sh.classList.remove('on'); current = null;
    setTimeout(function () { sh.hidden = true; }, 250);
    if (!hasQv || $('#qv').hidden) document.body.classList.remove('lock');
    try { history.replaceState(null, '', location.pathname + location.search); } catch (e) {}
  }
  if (hasSheet) $('#sheetClose').onclick = closeBundle;

  /* ---------- quick view ---------- */
  var qvList = [], qvIx = 0, bgPaused = [];
  function qvOpen() { var q = document.getElementById('qv'); return !!q && !q.hidden; }
  function openQV(id, from) {
    var scope = from && from.closest('#sheet') && current ? inGroup(current)
      : from && from.closest('#allGrid') ? (filter === 'all' ? figs : inSeries(+filter))
      : from && from.closest('#lovedShelf, #heroShow') ? loved : figs;
    qvList = scope.map(function (f) { return f.id; });
    qvIx = Math.max(0, qvList.indexOf(+id));
    showQV(); $('#qv').hidden = false; document.body.classList.add('lock');
    bgPaused = $$('video').filter(function (v) { return !v.closest('#qv') && !v.paused; });
    bgPaused.forEach(function (v) { v.pause(); });
    requestAnimationFrame(function () { $('#qv').classList.add('on'); });
  }
  function showQV() {
    var f = byId[qvList[qvIx]], s = seriesById[f.series], g = groupById[f.bundle];
    $('#qv').style.setProperty('--tint', f.tint);
    $('#qvMedia').innerHTML = f.mp4
      ? '<video muted loop playsinline autoplay poster="' + esc(poster(f)) + '">' + sources(f) + '</video>'
      : '<img src="' + esc(f.image || poster(f)) + '" alt="' + esc(f.name) + ' the ' + esc(f.animal.toLowerCase()) + '"><span class="v2-soon">Video coming soon</span>';
    $('#qvSeries').textContent = (s ? s.name : '') + ', figure #' + pad(f.no);
    $('#qvName').textContent = f.name;
    $('#qvLine').textContent = 'A poseable ' + f.animal.toLowerCase() + ' printed in matte PLA. Every segment bends at its own hinge, so it wiggles, curls and holds a pose on your desk.';
    $('#qvStoryBox').hidden = !f.story;
    if (f.story) { $('#qvStory').textContent = f.story; $('#qvLesson').hidden = !f.lesson; $('#qvLesson').textContent = f.lesson || ''; }
    var qb = $('#qvBundle');
    qb.hidden = !g;
    if (g) { qb.textContent = 'Part of the ' + g.name + ' bundle. Open it'; qb.dataset.open = g.slug; }
    qvAct();
  }
  function qvAct() {
    var f = byId[qvList[qvIx]], q = picked.get(f.id) || 0;
    var box = !hasBox ? '' : q
      ? '<div class="v2-qty"><button type="button" data-dec="' + f.id + '" aria-label="Remove one">−</button><span>' + q + ' in your box</span><button type="button" data-inc="' + f.id + '" aria-label="Add one">+</button></div>'
      : (f.inStock ? '<button type="button" class="btn btn-dark" data-inc="' + f.id + '">Add to box</button>' : '<span class="v2-soon">Sold out</span>');
    var buy = f.canBuy ? '<button type="button" class="btn btn-line" data-buy="' + f.id + '">Buy this figure · ' + priceHTML(f) + '</button>' : '';
    $('#qvAct').innerHTML = box + buy;
  }
  function closeQV() {
    $('#qv').classList.remove('on');
    setTimeout(function () { $('#qv').hidden = true; $('#qvMedia').innerHTML = ''; }, 200);
    bgPaused.forEach(function (v) { v.play().catch(function () {}); }); bgPaused = [];
    if (!hasSheet || $('#sheet').hidden) document.body.classList.remove('lock');
  }
  if (hasQv) {
    $('#qvClose').onclick = closeQV;
    $('#qv').addEventListener('click', function (e) { if (e.target.id === 'qv') closeQV(); });
    $('#qvPrev').onclick = function () { qvIx = (qvIx - 1 + qvList.length) % qvList.length; showQV(); };
    $('#qvNext').onclick = function () { qvIx = (qvIx + 1) % qvList.length; showQV(); };
    $('#qvBundle').addEventListener('click', function (e) { var slug = e.currentTarget.dataset.open; closeQV(); if (hasSheet) openBundle(slug); e.stopPropagation(); });
  }
  document.addEventListener('keydown', function (e) {
    if (hasQv && !$('#qv').hidden && e.key === 'ArrowRight') $('#qvNext').click();
    if (hasQv && !$('#qv').hidden && e.key === 'ArrowLeft') $('#qvPrev').click();
    if (e.key === 'Escape') { if (hasQv && !$('#qv').hidden) closeQV(); else if (hasSheet && !$('#sheet').hidden) closeBundle(); }
  });

  /* ---------- one click handler ---------- */
  document.addEventListener('click', function (e) {
    var t = e.target.closest('[data-qv],[data-open],[data-inc],[data-dec],[data-addall],[data-addseries],[data-bundle],[data-buy]');
    if (!t || t.id === 'qvBundle' || !t.closest('.lo-flexi')) return;
    var d = t.dataset;
    if (d.qv) return openQV(d.qv, t);
    if (d.open) return openBundle(d.open);
    if (d.bundle) return addProduct(d.bundle, t);
    if (d.buy) return addProduct(d.buy, t);
    if (d.inc) { var id = +d.inc; if (add([id]) && $('#qv').hidden) toast(byId[id].name + ' added to your box'); return; }
    if (d.dec) return remove(+d.dec);
    if (d.addall) { var g = groupBySlug[d.addall]; var n = add(inGroup(g).map(function (f) { return f.id; })); return toast(n + ' figures from ' + g.name + ' added to your box'); }
    if (d.addseries) { var sid = +d.addseries, s = seriesById[sid]; var m = add(inSeries(sid).map(function (f) { return f.id; })); return toast(m + ' ' + s.name + ' added to your box'); }
  });

  /* ---------- card video: buffer near view, instant hover, finish the loop, one ambient wiggle at a time ---------- */
  var vidOf = function (m) { return m.querySelector('video[data-id]'); };
  function arm(m) {
    var v = vidOf(m); if (!v || v.dataset.ready) return;
    v.dataset.ready = 1; v.preload = 'auto'; v.innerHTML = sources(byId[v.dataset.id]);
    v.addEventListener('loadeddata', function () { m.classList.add('ready'); if (m._want) start(m); }, { once: true });
    v.addEventListener('ended', function () { if (m._hover) { v.currentTime = 0; v.play().catch(function () {}); } else { m.classList.remove('playing'); v.currentTime = 0; } });
    v.load();
  }
  function start(m) {
    var v = vidOf(m); if (!v || reduce || (qvOpen() && !m.closest('#qv'))) return;
    if (!m.classList.contains('ready')) { m._want = true; return arm(m); }
    m._want = false; if (!v.paused) return;
    m.classList.add('playing'); v.play().catch(function () { m.classList.remove('playing'); });
  }
  var vis = new Set();
  var near = 'IntersectionObserver' in window ? new IntersectionObserver(function (es) { es.forEach(function (en) { if (en.isIntersecting) arm(en.target); }); }, { rootMargin: '500px' }) : null;
  var onScreen = 'IntersectionObserver' in window ? new IntersectionObserver(function (es) { es.forEach(function (en) { if (en.isIntersecting) vis.add(en.target); else vis.delete(en.target); }); }, { threshold: .6 }) : null;
  function watch() {
    $$('.v2-media:not([data-w])').forEach(function (m) {
      m.dataset.w = 1;
      if (vidOf(m) && near) { near.observe(m); onScreen.observe(m); }
    });
  }
  document.addEventListener('pointerover', function (e) { if (!fine) return; var m = e.target.closest && e.target.closest('.v2-media'); if (m && !m.contains(e.relatedTarget)) { m._hover = true; start(m); } });
  document.addEventListener('pointerout', function (e) { if (!fine) return; var m = e.target.closest && e.target.closest('.v2-media'); if (m && !m.contains(e.relatedTarget)) m._hover = false; });
  if (!reduce) setInterval(function () {
    if (document.hidden) return;
    var live = Array.from(vis).filter(function (m) { return m.isConnected; });
    if (live.some(function (m) { return m.classList.contains('playing'); })) return;
    var idle = live.filter(function (m) { return m.classList.contains('ready') && !m._hover && Date.now() - (m._last || 0) > 8000; });
    if (!idle.length) return;
    var m = idle[Math.floor(Math.random() * idle.length)]; m._last = Date.now(); start(m);
  }, 1800);

  /* ---------- tab bar: highlight the section in view ---------- */
  if ('IntersectionObserver' in window) {
    var spy = new IntersectionObserver(function (es) {
      es.forEach(function (en) { if (en.isIntersecting) $$('.v2-tablinks a').forEach(function (a) { a.classList.toggle('on', a.getAttribute('href') === '#' + en.target.id); }); });
    }, { rootMargin: '-45% 0px -50% 0px' });
    ['loved', 'bundles', 'all', 'box'].forEach(function (id) { var s = document.getElementById(id); if (s) spy.observe(s); });
  }

  /* ---------- start ---------- */
  if (hasAll) renderAll();
  refresh(); watch();
  function fromHash() {
    var h = decodeURIComponent(location.hash.slice(1));
    if (!h) return;
    if (groupBySlug[h] && hasSheet) { openBundle(h); return; }
    if (h.indexOf('fig-') === 0 && hasQv) {
      var code = h.slice(4), f = figs.filter(function (x) { return x.code === code; })[0];
      if (f) { qvList = figs.map(function (x) { return x.id; }); qvIx = qvList.indexOf(f.id); showQV(); $('#qv').hidden = false; document.body.classList.add('lock'); requestAnimationFrame(function () { $('#qv').classList.add('on'); }); }
    }
  }
  fromHash();
  addEventListener('hashchange', fromHash);
})();
