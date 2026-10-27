/* Layer One header: mobile drawer, cart sheet, tab bar, hide-on-scroll app bar. Cart data comes from WooCommerce over admin-ajax. */
(function () {
  var $ = function (s) { return document.querySelector(s); };
  var cart = $('#cart'), drawer = $('#drawer'), ov = $('#ov'), toastEl = $('#toast'), tt;
  if (!cart || !drawer || !ov) return;

  function toast(m) {
    toastEl.textContent = m; toastEl.classList.add('on');
    clearTimeout(tt); tt = setTimeout(function () { toastEl.classList.remove('on'); }, 2200);
  }
  function sheet(el, open) {
    if (open) toastEl.classList.remove('on');
    el.classList.toggle('on', open);
    ov.classList.toggle('on', open);
    document.body.classList.toggle('lock', open);
  }
  var onClose = [];
  function closeAll() { sheet(cart, false); sheet(drawer, false); onClose.forEach(function (f) { f(); }); }

  function on(sel, fn) { var e = $(sel); if (e) e.addEventListener('click', fn); }
  on('#cartBtn', function () { sheet(cart, true); });
  on('#tabCart', function () { sheet(cart, true); });
  on('#closeCart', function () { sheet(cart, false); });
  on('#burger', function () { sheet(drawer, true); });
  on('#tabCats', function () { sheet(drawer, true); });
  on('#closeDr', function () { sheet(drawer, false); });
  ov.addEventListener('click', closeAll);
  drawer.addEventListener('click', function (e) { if (e.target.closest('a')) sheet(drawer, false); });
  addEventListener('keydown', function (e) { if (e.key === 'Escape') closeAll(); });

  /* drag a bottom sheet down to close it (phones) */
  function swipeClose(el) {
    var y0 = null, dy = 0;
    el.addEventListener('touchstart', function (e) {
      var sc = e.target.closest('.cl');
      if (el.scrollTop > 0 || (sc && sc.scrollTop > 0)) return;
      y0 = e.touches[0].clientY; dy = 0;
    }, { passive: true });
    el.addEventListener('touchmove', function (e) {
      if (y0 === null) return;
      dy = Math.max(0, e.touches[0].clientY - y0);
      el.style.transition = 'none'; el.style.translate = '0 ' + dy + 'px';
    }, { passive: true });
    el.addEventListener('touchend', function () {
      if (y0 === null) return;
      y0 = null; el.style.transition = el.style.translate = '';
      if (dy > 90) sheet(el, false);
    });
  }
  swipeClose(cart); swipeClose(drawer);

  /* sticky bars below the header (--hdr) must also clear the admin bar when it is fixed (signed-in editors) */
  function setHdr() {
    var h = $('.header'), ab = document.getElementById('wpadminbar');
    var off = ab && getComputedStyle(ab).position === 'fixed' ? ab.offsetHeight : 0;
    var root = document.documentElement.style;
    root.setProperty('--lo-adminbar', off + 'px');
    if (h) root.setProperty('--hdr', (h.offsetHeight + off) + 'px');
  }
  setHdr(); addEventListener('resize', setHdr); addEventListener('load', setHdr);

  /* phone: shorter search hint, app bar hides while scrolling down */
  var phone = matchMedia('(max-width:860px)');
  var si = $('.header .search input');
  if (si && phone.matches) si.placeholder = si.placeholder.split(',').slice(0, 2).join(',') + '…';
  var hd = $('.header'), ly = scrollY;
  addEventListener('scroll', function () {
    var y = scrollY;
    if (Math.abs(y - ly) > 6) { hd.classList.toggle('up', phone.matches && y > 160 && y > ly); ly = y; }
  }, { passive: true });

  /* ---------- cart ---------- */
  function paint(d) {
    $('#cl').innerHTML = d.items;
    $('#st').textContent = d.subtotal;
    $('#shipNote').textContent = d.note;
    ['#cn', '#cn2'].forEach(function (s) {
      var b = $(s); if (!b) return;
      b.textContent = d.count;
      if (s === '#cn2') b.hidden = !d.count;
    });
  }
  function bump() {
    ['#cn', '#cn2'].forEach(function (s) {
      var b = $(s); if (!b) return;
      b.classList.remove('bump'); void b.offsetWidth; b.classList.add('bump');
    });
  }
  function post(data) {
    var body = new URLSearchParams(Object.assign({ action: 'layerone_cart', nonce: LAYERONE.nonce }, data));
    cart.classList.add('busy');
    return fetch(LAYERONE.ajax, { method: 'POST', credentials: 'same-origin', body: body })
      .then(function (r) { return r.json(); })
      .then(function (j) { if (j && j.success) paint(j.data); return j; })
      .catch(function () { toast('Could not update the cart. Please try again.'); })
      .then(function (j) { cart.classList.remove('busy'); return j; });
  }

  cart.addEventListener('click', function (e) {
    var b = e.target.closest('[data-q]');
    if (!b) return;
    var q = b.closest('.q'), cur = parseInt(q.querySelector('span').textContent, 10) || 0;
    post({ key: b.dataset.key, qty: Math.max(0, cur + parseInt(b.dataset.q, 10)) });
  });

  $('#checkout') && $('#checkout').addEventListener('click', function (e) {
    if (!$('#cl .ci')) { e.preventDefault(); toast('Your cart is empty'); }
  });

  /* WooCommerce ajax add-to-cart (shop buttons) fires this jQuery event */
  if (window.jQuery) {
    jQuery(document.body).on('added_to_cart', function () {
      post({}).then(function () { bump(); toast('✓ Added to cart'); });
    });
  }

  window.LO = { onCloseAll: function (f) { onClose.push(f); }, bump: bump, sheet: sheet, toast: toast, closeAll: closeAll, refreshCart: function () { return post({}); } };
})();

/* announcement bar: rotate the messages */
(function () {
  var bar = document.querySelector('.ann-slider');
  if (!bar) return;
  var slides = bar.querySelectorAll('.ann-s');
  if (slides.length < 2) return;
  var i = 0, secs = Math.max(2, parseInt(bar.dataset.speed, 10) || 4), timer;
  function show(n) {
    var cur = slides[i], next = slides[n];
    cur.classList.remove('on'); cur.classList.add('out');
    setTimeout(function () { cur.classList.remove('out'); }, 600);
    next.classList.add('on'); i = n;
  }
  function start() { timer = setInterval(function () { show((i + 1) % slides.length); }, secs * 1000); }
  function stop() { clearInterval(timer); }
  bar.addEventListener('mouseenter', stop); bar.addEventListener('mouseleave', start);
  document.addEventListener('visibilitychange', function () { document.hidden ? stop() : (stop(), start()); });
  start();
})();
