/* LayerOne – FAQ page: search + category filter */
(function () {
  const $ = s => document.querySelector(s);
  const items = [...document.querySelectorAll('.fq-item')], groups = [...document.querySelectorAll('.fq-group')];
  let cat = 'all', q = '';
  function apply() {
    let shown = 0;
    items.forEach(d => {
      const ok = (cat === 'all' || d.dataset.cat === cat) && (!q || d.textContent.toLowerCase().includes(q));
      d.hidden = !ok; if (ok) shown++;
    });
    groups.forEach(g => { g.hidden = !g.querySelector('.fq-item:not([hidden])'); });
    $('#fqNone').hidden = shown > 0;
    $('#fqNoneQ').textContent = q;
  }
  $('#fqSearch').addEventListener('input', e => { q = e.target.value.trim().toLowerCase(); apply(); });
  $('#fqChips').addEventListener('click', e => {
    const b = e.target.closest('[data-cat]'); if (!b) return;
    cat = b.dataset.cat;
    document.querySelectorAll('#fqChips button').forEach(x => x.classList.toggle('on', x === b));
    apply();
  });
  const h = location.hash.slice(1), b = h && document.querySelector(`#fqChips [data-cat="${h}"]`);
  if (b) b.click();
})();
