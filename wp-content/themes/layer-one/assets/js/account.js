/* My account pages: order tabs, address dialog, confirmations, returns, order again, and the guest wishlist. */
(function () {
  var $ = function (s) { return document.querySelector(s); };
  var $$ = function (s) { return Array.prototype.slice.call(document.querySelectorAll(s)); };
  var toast = function (m) { if (window.LO && LO.toast) LO.toast(m); };

  /* forms that ask "are you sure?" first */
  document.addEventListener('submit', function (e) {
    var q = e.target.closest('[data-confirm]');
    if (q && !confirm(q.dataset.confirm)) e.preventDefault();
  });

  /* orders: All / In progress / Delivered */
  var tabs = $('#ordTabs');
  if (tabs) tabs.addEventListener('click', function (e) {
    var b = e.target.closest('[data-f]'); if (!b) return;
    $$('#ordTabs button').forEach(function (x) { x.classList.toggle('on', x === b); });
    var shown = 0;
    $$('#ordList .ord-wrap').forEach(function (w) {
      var s = w.dataset.state, ok = b.dataset.f === 'all' || (b.dataset.f === 'open' ? s === 'open' : s === 'done');
      w.hidden = !ok; if (ok) shown++;
    });
    var empty = $('#ordEmpty'); if (empty) empty.hidden = shown > 0;
  });

  /* addresses: one dialog for add and edit */
  var dlg = $('#adDlg');
  if (dlg && dlg.showModal) {
    var form = $('#adForm');
    var open = function (a) {
      form.reset();
      form.elements.id.value = a ? a.id : '';
      $('#adTitle').textContent = a ? 'Edit address' : 'Add a new address';
      if (a) ['label', 'name', 'phone', 'a1', 'a2', 'city', 'state', 'pin'].forEach(function (k) { form.elements[k].value = a[k] || ''; });
      form.elements.default.checked = !!(a && a.default);
      dlg.showModal();
    };
    $('#adNew').onclick = function () { open(null); };
    $('#adCancel').onclick = function () { dlg.close(); };
    $('#adList').addEventListener('click', function (e) {
      var ed = e.target.closest('[data-edit]');
      if (ed) { try { open(JSON.parse(ed.dataset.edit)); } catch (x) {} }
    });
    form.addEventListener('submit', function (e) { if (!form.checkValidity()) { e.preventDefault(); form.reportValidity(); } });
  }

  /* returns: the item list follows the chosen order */
  var rt = $('#rtForm');
  if (rt) {
    var items = {}; try { items = JSON.parse(rt.dataset.items || '{}'); } catch (e) {}
    var sel = $('#rtOrder'), it = $('#rtItem');
    var fill = function () { it.innerHTML = (items[sel.value] || []).map(function (n, i) { return '<option value="' + i + '"></option>'; }).join(''); (items[sel.value] || []).forEach(function (n, i) { it.options[i].textContent = n; }); };
    sel.addEventListener('change', fill); fill();
    rt.addEventListener('submit', function (e) { if (!rt.checkValidity()) { e.preventDefault(); rt.reportValidity(); } });
  }
  [$('#pForm'), $('#pwForm')].forEach(function (f) { if (f) f.addEventListener('submit', function (e) { if (!f.checkValidity()) { e.preventDefault(); f.reportValidity(); } }); });

  /* order again */
  document.addEventListener('click', function (e) {
    var b = e.target.closest('[data-reorder]'); if (!b || !window.LAYERONE) return;
    b.disabled = true;
    var body = new URLSearchParams({ action: 'layerone_reorder', nonce: LAYERONE.nonce, order_id: b.dataset.reorder });
    fetch(LAYERONE.ajax, { method: 'POST', credentials: 'same-origin', body: body })
      .then(function (r) { return r.json(); })
      .then(function (j) {
        if (j && j.success) { var done = function () { if (window.LO) LO.bump(); toast('✓ ' + j.data.message); }; window.LO && LO.refreshCart ? LO.refreshCart().then(done) : done(); }
        else toast((j && j.data && j.data.message) || 'Could not add these items.');
      })
      .catch(function () { toast('Could not reach the shop. Please try again.'); })
      .then(function () { b.disabled = false; });
  });

  /* wishlist: "Add all to cart" (both the account page and the guest page) */
  var all = $('#wlAll');
  if (all) all.addEventListener('click', function () {
    var cards = $$('#wlGrid .prod [data-add]'); if (!cards.length) return;
    cards.reduce(function (p, b) { return p.then(function () { b.click(); return new Promise(function (r) { setTimeout(r, 450); }); }); }, Promise.resolve());
  });

  /* wishlist for visitors who are not signed in: cards for the ids saved in this browser */
  var guest = $('[data-guest-wishlist]');
  if (guest && window.LAYERONE && window.LOWish) {
    var grid = $('#wlGrid');
    var draw = function () {
      var ids = LOWish.list();
      $('#wlCount').textContent = ids.length ? ids.length + ' saved item' + (ids.length > 1 ? 's' : '') : '';
      $('#wlAll').hidden = !ids.length;
      if (!ids.length) {
        grid.className = '';
        grid.innerHTML = '<div class="acc-empty"><h3>Your wishlist is empty</h3><p>Tap the heart on any product to save it here.</p><a class="btn btn-dark btn-sm" href="' + (LAYERONE.shopUrl || '/') + '">Browse products</a></div>';
        return;
      }
      fetch(LAYERONE.ajax + '?action=layerone_wish_cards&ids=' + ids.join(','))
        .then(function (r) { return r.json(); })
        .then(function (j) {
          if (!j || !j.success) return;
          grid.className = 'grid';
          grid.innerHTML = j.data.cards.map(function (c) { return c.html; }).join('');
          if (window.LOWish) LOWish.paint(grid);
        });
    };
    document.addEventListener('lo:wish', function () { setTimeout(draw, 250); });
    draw();
  }
})();
