/* Wishlist (no plugin): hearts on product cards and the header counter.
   Signed in: the list lives on the server (user meta). Guest: in this browser, then merged into the account at sign-in. */
(function () {
  var cfg = window.LAYERONE && LAYERONE.wish;
  if (!cfg) return;
  var KEY = 'layerone_wishlist';
  var ids = [];

  function readLocal() { try { return (JSON.parse(localStorage.getItem(KEY) || '[]') || []).map(Number).filter(Boolean); } catch (e) { return []; } }
  function writeLocal(a) { try { localStorage.setItem(KEY, JSON.stringify(a)); } catch (e) {} }
  function post(data) {
    var body = new URLSearchParams(Object.assign({ action: 'layerone_wish', nonce: LAYERONE.nonce }, data));
    return fetch(LAYERONE.ajax, { method: 'POST', credentials: 'same-origin', body: body }).then(function (r) { return r.json(); });
  }
  function toast(m) { if (window.LO && LO.toast) LO.toast(m); }

  /* paint hearts and the counter */
  function paint(root) {
    (root || document).querySelectorAll('[data-wish]').forEach(function (h) {
      var on = ids.indexOf(+h.dataset.wish) > -1;
      h.classList.toggle('on', on); h.setAttribute('aria-pressed', on);
      h.setAttribute('aria-label', on ? 'Remove from wishlist' : 'Save to wishlist');
    });
    var n = document.getElementById('wn');
    if (n) { if (n.textContent !== String(ids.length)) n.textContent = ids.length; n.hidden = !ids.length; }
  }

  function has(id) { return ids.indexOf(+id) > -1; }

  function toggle(id) {
    id = +id;
    var adding = !has(id);
    ids = adding ? ids.concat([id]) : ids.filter(function (x) { return x !== id; });
    paint();
    document.dispatchEvent(new CustomEvent('lo:wish', { detail: { id: id, on: adding, ids: ids } }));
    toast(adding ? '♥ Saved to your wishlist' : 'Removed from wishlist');
    if (cfg.loggedIn) {
      post({ do: 'toggle', product_id: id }).then(function (j) { if (j && j.success) { ids = j.data.ids; paint(); } }).catch(function () {});
    } else {
      writeLocal(ids);
    }
  }

  /* start: server list for customers (merging what was saved as a guest), browser list for guests */
  if (cfg.loggedIn) {
    ids = (cfg.ids || []).map(Number);
    var guest = readLocal();
    if (guest.length) {
      post({ do: 'merge', ids: guest }).then(function (j) {
        if (j && j.success) { ids = j.data.ids; writeLocal([]); paint(); document.dispatchEvent(new CustomEvent('lo:wish', { detail: { ids: ids } })); }
      }).catch(function () {});
    }
  } else {
    ids = readLocal();
  }
  paint();

  document.addEventListener('click', function (e) {
    var h = e.target.closest('[data-wish]');
    if (!h) return;
    e.preventDefault(); e.stopPropagation();
    toggle(h.dataset.wish);
  });

  /* cards drawn later (filters, load more, quick view) get their hearts painted */
  new MutationObserver(function (list) {
    for (var i = 0; i < list.length; i++) { if (list[i].addedNodes.length) { paint(); return; } }
  }).observe(document.body, { childList: true, subtree: true });

  window.LOWish = { has: has, toggle: toggle, list: function () { return ids.slice(); }, paint: paint };
})();
