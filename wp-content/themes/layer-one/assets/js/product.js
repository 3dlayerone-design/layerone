/* Layer One product page: colour, quantity, sticky add-to-cart bar, share, photo viewer with zoom.
   Ported from the design's product.js. The page itself is drawn by woocommerce/single-product.php. */
(function () {
  var $ = function (s) { return document.querySelector(s); };
  var $$ = function (s) { return Array.prototype.slice.call(document.querySelectorAll(s)); };
  if (!$('.pd')) return;
  var toast = function (m) { if (window.LO && LO.toast) LO.toast(m); };

  /* ---------- colour (both the main picker and the sticky bar) ---------- */
  var addBtns = [$('#pdAdd'), $('#sbAdd')].filter(Boolean);
  var colour = '';
  function setColour(c) {
    colour = c;
    $$('#pdColors [data-c], #sbColors [data-c]').forEach(function (b) { var on = b.dataset.c === c; b.classList.toggle('on', on); b.setAttribute('aria-pressed', on); });
    var n = $('#pdColorName'); if (n) n.textContent = c.charAt(0).toUpperCase() + c.slice(1);
    addBtns.forEach(function (b) { b.dataset.col = c; });
  }
  ['#pdColors', '#sbColors'].forEach(function (s) {
    var box = $(s); if (!box) return;
    box.addEventListener('click', function (e) { var b = e.target.closest('[data-c]'); if (b) setColour(b.dataset.c); });
  });
  var first = $('#pdColors [data-c]'); if (first) setColour(first.dataset.c);

  /* ---------- quantity ---------- */
  var qty = 1;
  function setQty(n) {
    qty = Math.min(20, Math.max(1, n));
    ['#pdQty', '#sbQty'].forEach(function (s) { var o = $(s); if (o) o.textContent = qty; });
  }
  [['#pdMinus', -1], ['#pdPlus', 1], ['#sbMinus', -1], ['#sbPlus', 1]].forEach(function (x) { var b = $(x[0]); if (b) b.onclick = function () { setQty(qty + x[1]); }; });

  /* ---------- sticky add-to-cart bar: shown while the normal button is off screen, hidden again at the footer ---------- */
  var sbar = $('#sbar'), addBtn = $('#pdAdd'), foot = document.querySelector('footer');
  function checkBar() {
    if (!sbar || !addBtn) return;
    var r = addBtn.getBoundingClientRect(), buyVisible = r.bottom > 0 && r.top < innerHeight;
    var footVisible = foot && foot.getBoundingClientRect().top < innerHeight - 8;
    var on = !buyVisible && !footVisible;
    sbar.classList.toggle('on', on); document.body.classList.toggle('has-sbar', on);
  }
  addEventListener('scroll', checkBar, { passive: true });
  addEventListener('resize', checkBar);
  checkBar();

  /* ---------- share ---------- */
  function share() {
    var data = { title: document.title, text: ($('#pdTitle') || {}).textContent + ' – LayerOne', url: location.href };
    var done = navigator.share ? navigator.share(data) : navigator.clipboard.writeText(location.href).then(function () { toast('Link copied'); });
    Promise.resolve(done).catch(function (e) { if (!e || e.name !== 'AbortError') toast('Copy the link from the address bar'); });
  }
  ['#pdShare', '#pdShare2'].forEach(function (s) { var b = $(s); if (b) b.onclick = share; });

  /* ---------- photo viewer with zoom ---------- */
  var lb = $('#lb');
  if (lb) {
    var photos = $$('#pdGallery .pd-zoom').map(function (b) { return b.dataset.full; });
    var lbImg = $('#lbImg'), lbStage = $('#lbStage'), title = ($('#pdTitle') || {}).textContent || '';
    var cur = 0, zoomed = false, lastFocus = null;
    var setZoom = function (on, e) {
      zoomed = on; lb.classList.toggle('zoomed', on); $('#lbZoom b').textContent = on ? 'Zoom out' : 'Zoom';
      if (on) moveZoom(e); else lbImg.style.transformOrigin = '50% 50%';
    };
    var moveZoom = function (e) {
      var r = lbImg.getBoundingClientRect(); if (!e || !r.width) return;
      var x = Math.min(100, Math.max(0, (e.clientX - r.left) / r.width * 100)), y = Math.min(100, Math.max(0, (e.clientY - r.top) / r.height * 100));
      lbImg.style.transformOrigin = x + '% ' + y + '%';
    };
    var lbShow = function (i) {
      cur = (i + photos.length) % photos.length; setZoom(false);
      lbImg.src = photos[cur]; lbImg.alt = title + ' – photo ' + (cur + 1);
      $('#lbN').textContent = (cur + 1) + ' / ' + photos.length;
      lb.classList.toggle('single', photos.length < 2);
    };
    var lbOpen = function (i) { lastFocus = document.activeElement; lb.hidden = false; document.body.style.overflow = 'hidden'; lbShow(i); $('#lbX').focus(); };
    var lbClose = function () { lb.hidden = true; document.body.style.overflow = ''; if (lastFocus) lastFocus.focus(); };
    $('#pdGallery').addEventListener('click', function (e) {
      var b = e.target.closest('.pd-zoom'), f = e.target.closest('figure');
      if (b) lbOpen(+b.dataset.i);
      else if (f && f.querySelector('img')) lbOpen($$('#pdGallery figure').indexOf(f));
    });
    $('#lbX').onclick = lbClose;
    $('#lbPrev').onclick = function () { lbShow(cur - 1); };
    $('#lbNext').onclick = function () { lbShow(cur + 1); };
    $('#lbZoom').onclick = function () { setZoom(!zoomed); };
    lbImg.addEventListener('click', function (e) { e.stopPropagation(); setZoom(!zoomed, e); });
    lbStage.addEventListener('mousemove', function (e) { if (zoomed) moveZoom(e); });
    lbStage.addEventListener('click', function (e) { if (e.target === lbStage) lbClose(); });
    addEventListener('keydown', function (e) {
      if (lb.hidden) return;
      if (e.key === 'Escape') lbClose();
      else if (e.key === 'ArrowLeft') lbShow(cur - 1);
      else if (e.key === 'ArrowRight') lbShow(cur + 1);
    });
  }

  /* ---------- details panel stays in view without its own scrollbar ----------
     Taller than the screen: it scrolls with the page until its bottom shows, then sticks. Shorter: it sticks under the header. */
  var info = $('.pd-info'), wide = matchMedia('(min-width:901px)');
  function fitInfo() {
    if (!info) return;
    if (!wide.matches) { info.style.top = ''; return; }
    var head = (parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--hdr')) || document.querySelector('.header').offsetHeight);
    var room = innerHeight - head - 24, h = info.offsetHeight;
    info.style.top = (h > room ? innerHeight - h - 24 : head + 12) + 'px';
  }
  fitInfo();
  addEventListener('resize', fitInfo);
  wide.addEventListener('change', fitInfo);
  if ('ResizeObserver' in window && info) new ResizeObserver(fitInfo).observe(info);
})();
