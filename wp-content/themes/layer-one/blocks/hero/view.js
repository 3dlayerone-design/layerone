/* Hero spotlight (front end). Ported from the design's landing2.js; slides come from the block's data-hero attribute. */
(function () {
  var root = document.querySelector('[data-hero]');
  if (!root) return;
  var cfg;
  try { cfg = JSON.parse(root.dataset.hero); } catch (e) { return; }
  var SL = cfg.slides || [];
  if (!SL.length) return;

  var $ = function (s) { return root.querySelector(s); };
  var box = $('#viewer'), img = $('#heroImg'), pickEl = $('#pick'), copy = $('#spCopy'), main = $('.main');
  var phone = matchMedia('(max-width:860px)');
  var reduce = matchMedia('(prefers-reduced-motion:reduce)').matches;
  var DUR = cfg.ms || 6000;
  var cur = 0, auto = !!cfg.auto && SL.length > 1, hover = false, seen = true, t0 = performance.now();

  function esc(s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
  function big(f) { return (phone.matches && f.imgS) ? f.imgS : f.img; }

  function setFeat(k) {
    if (k === cur && img.dataset.ready) return;
    cur = k; img.dataset.ready = '1';
    var f = SL[k];
    img.classList.remove('in'); void img.offsetWidth; img.src = big(f); img.alt = f.name; img.classList.add('in');
    main.classList.toggle('has-bg', !!f.bg);
    if (f.bg) { main.style.setProperty('--hero-bg', f.bg); } else { main.style.removeProperty('--hero-bg'); }
    main.style.setProperty('--t1', f.lt[0]); main.style.setProperty('--t2', f.lt[1]); main.style.setProperty('--t3', f.lt[2]);
    box.style.setProperty('--glow', f.glow);
    $('#hn').textContent = f.name; $('#hc').textContent = f.cat; $('#hp').textContent = f.price;
    $('#heroTh').style.backgroundImage = f.thumb ? 'url(' + f.thumb + ')' : '';
    $('#fc').innerHTML = f.chips.map(function (x) { return '<b>' + esc(x) + '</b>'; }).join(' · ');
    var q = !!f.quote;
    $('#hb').hidden = q; $('#hb').dataset.id = f.id;
    $('#hq').hidden = !q; $('#spBuy').hidden = q; $('#spBuy').dataset.id = f.id; $('#spQuote').hidden = !q;
    if (q) { $('#hq').href = $('#spQuote').href = f.quote; }
    copy.classList.remove('sp-in'); void copy.offsetWidth; copy.classList.add('sp-in');
    $('#spK').textContent = f.kicker; $('#spH').textContent = f.name; $('#spP').textContent = f.desc;
    $('#spPr').innerHTML = '<b>' + esc(f.price) + '</b>' + (f.unit ? '<span>' + esc(f.unit) + '</span>' : '') + (f.old ? '<s>' + esc(f.old) + '</s>' : '');
    $('#spMore').textContent = f.moreL; $('#spMore').href = f.moreU;
    pickEl.querySelectorAll('button').forEach(function (b, j) { b.classList.toggle('on', j === k); });
    t0 = performance.now();
  }

  pickEl.addEventListener('click', function (e) {
    var b = e.target.closest('button');
    if (b) { auto = false; setFeat(+b.dataset.k); $('#spBarI').style.width = '0'; }
  });

  /* add to cart through WooCommerce ajax */
  function add(btn) {
    var id = btn.dataset.id, f = SL[cur];
    var url = window.LAYERONE && LAYERONE.wcAdd;
    if (!url || !id) return;
    btn.disabled = true;
    fetch(url, { method: 'POST', credentials: 'same-origin', body: new URLSearchParams({ product_id: id, quantity: 1 }) })
      .then(function (r) { return r.json(); })
      .then(function (j) {
        if (j && j.error) { if (window.LO) LO.toast('Could not add this product'); return; }
        var done = function () { if (window.LO) { LO.bump(); LO.toast('✓ ' + f.name + ' added to cart'); } };
        window.LO ? LO.refreshCart().then(done) : 0;
      })
      .catch(function () { if (window.LO) LO.toast('Could not add this product'); })
      .then(function () { btn.disabled = false; });
  }
  ['#spBuy', '#hb'].forEach(function (s) { $(s).addEventListener('click', function () { add(this); }); });

  /* auto-advance with progress bar */
  new IntersectionObserver(function (es) { seen = es[0].isIntersecting; }).observe(box);
  (function loop(now) {
    var bar = $('#spBarI');
    if (auto && !hover && seen && !document.hidden) {
      var d = now - t0;
      bar.style.width = Math.min(100, d / DUR * 100) + '%';
      if (d >= DUR) setFeat((cur + 1) % SL.length);
    } else {
      t0 = now - (parseFloat(bar.style.width) || 0) / 100 * DUR;
    }
    requestAnimationFrame(loop);
  })(performance.now());

  /* sparkles */
  var SP = [[8,18,20,'#A8D5BA',-30,-24,0],[18,62,14,'#6B7A4F',-34,10,.6],[86,20,18,'#6B7A4F',30,-26,1.1],[92,58,22,'#A8D5BA',34,14,.3],[70,8,12,'#A8D5BA',20,-30,1.6],[30,8,14,'#6B7A4F',-18,-30,.9],[6,42,12,'#A8D5BA',-34,0,1.3],[78,74,14,'#6B7A4F',26,22,.5]];
  $('#sparks').innerHTML = SP.map(function (s) {
    return '<span class="sp" style="left:' + s[0] + '%;top:' + s[1] + '%;--s:' + s[2] + 'px;--c:' + s[3] + ';--dx:' + s[4] + ';--dy:' + s[5] + ';animation-delay:' + s[6] + 's">✦</span>';
  }).join('');

  /* hover: smooth tilt + parallax + pop */
  var tx = 0, ty = 0, cx = 0, cy = 0, sc = 1, raf = 0;
  function tick() {
    cx += (tx - cx) * .09; cy += (ty - cy) * .09; sc += ((hover ? 1.04 : 1) - sc) * .1;
    img.style.transform = 'translate3d(' + (cx * -18) + 'px,' + (cy * -12) + 'px,0) rotateY(' + (cx * 10) + 'deg) rotateX(' + (cy * -8) + 'deg) scale(' + sc + ')';
    box.style.setProperty('--mx', cx.toFixed(3)); box.style.setProperty('--my', cy.toFixed(3));
    raf = !hover && Math.abs(cx) + Math.abs(cy) + Math.abs(sc - 1) < .002 ? 0 : requestAnimationFrame(tick);
  }
  function wake() { if (!reduce && !raf) raf = requestAnimationFrame(tick); }
  box.addEventListener('pointermove', function (e) {
    if (e.pointerType !== 'mouse') return;
    var b = box.getBoundingClientRect(); tx = (e.clientX - b.left) / b.width - .5; ty = (e.clientY - b.top) / b.height - .5; wake();
  });
  box.addEventListener('pointerenter', function (e) { if (e.pointerType !== 'mouse') return; hover = true; box.classList.add('hv'); wake(); });
  box.addEventListener('pointerleave', function (e) { if (e.pointerType !== 'mouse') return; hover = false; tx = ty = 0; box.classList.remove('hv'); wake(); });
})();
