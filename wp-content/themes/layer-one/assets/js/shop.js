/* Shop pieces used on every page with product cards: colour dots and "Add to cart". */
(function () {
  function toast(m) { if (window.LO && LO.toast) LO.toast(m); }

  /* choose a colour dot on a card */
  document.addEventListener('click', function (e) {
    var b = e.target.closest('.cdots [data-sw]');
    if (!b) return;
    e.preventDefault();
    var card = b.closest('.prod'), sw = b.closest('.cdots');
    sw.querySelectorAll('[data-sw]').forEach(function (x) { var on = x === b; x.classList.toggle('on', on); x.setAttribute('aria-pressed', on); });
    var n = sw.querySelector('.cd-name'); if (n) n.textContent = b.dataset.sw.charAt(0).toUpperCase() + b.dataset.sw.slice(1);
    var q = card && card.querySelector('.quick[data-add]'); if (q) q.dataset.col = b.dataset.sw;
  });

  /* add to cart (cards, product page): [data-add="<product id>"] with optional data-col and data-qty */
  function addToCart(btn) {
    var id = btn.dataset.add;
    if (!id || !window.LAYERONE) return;
    var qtyEl = btn.dataset.qtyFrom ? document.querySelector(btn.dataset.qtyFrom) : null;
    var body = new URLSearchParams({
      action: 'layerone_add_to_cart', nonce: LAYERONE.nonce, product_id: id,
      qty: qtyEl ? qtyEl.value : (btn.dataset.qty || 1), colour: btn.dataset.col || ''
    });
    btn.disabled = true;
    fetch(LAYERONE.ajax, { method: 'POST', credentials: 'same-origin', body: body })
      .then(function (r) { return r.json(); })
      .then(function (j) {
        if (!(j && j.success)) { toast((j && j.data && j.data.message) || 'It could not be added to your cart.'); return; }
        var done = function () { if (window.LO) LO.bump(); toast('✓ ' + j.data.message); };
        window.LO && LO.refreshCart ? LO.refreshCart().then(done) : done();
      })
      .catch(function () { toast('Could not reach the shop. Please try again.'); })
      .then(function () { btn.disabled = false; });
  }
  document.addEventListener('click', function (e) {
    var b = e.target.closest('[data-add]');
    if (!b || b.tagName === 'A') return;
    e.preventDefault();
    addToCart(b);
  });
})();
