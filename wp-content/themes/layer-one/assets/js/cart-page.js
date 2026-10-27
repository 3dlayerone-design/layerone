/* Cart page: the quantity buttons change the hidden quantity and send the cart form (WooCommerce updates the totals). */
(function () {
  var form = document.querySelector('.woocommerce-cart-form');
  if (!form) return;
  var btn = document.getElementById('cpUpdate');
  var tmr;

  form.addEventListener('click', function (e) {
    var b = e.target.closest('[data-d]');
    if (!b) return;
    var wrap = b.closest('.cp-qty'), input = wrap.querySelector('input.qty'), label = wrap.querySelector('.cp-q');
    var max = parseInt(input.dataset.max, 10) || 99;
    var next = Math.max(0, Math.min(max, (parseInt(input.value, 10) || 0) + parseInt(b.dataset.d, 10)));
    if (next === 0) { var rm = b.closest('.cp-item').querySelector('.cp-rm'); if (rm) location.href = rm.href; return; }
    input.value = next; label.textContent = next;
    clearTimeout(tmr);
    tmr = setTimeout(function () { btn.hidden = false; btn.click(); }, 350);
  });

  var clear = document.getElementById('cpClear');
  if (clear) clear.addEventListener('click', function (e) { if (!confirm('Remove everything from your cart?')) e.preventDefault(); });
})();
