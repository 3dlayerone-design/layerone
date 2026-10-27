/* Flexi Crew band (front end): figure videos play only while on screen; bundle arrows scroll the row. */
(function () {
  var reduce = matchMedia('(prefers-reduced-motion:reduce)').matches;

  var vids = document.querySelectorAll('.l2-tile video');
  if (vids.length && !reduce && 'IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (es) {
      es.forEach(function (e) {
        var v = e.target;
        if (e.isIntersecting) { v.preload = 'auto'; v.play().catch(function () {}); } else { v.pause(); }
      });
    }, { rootMargin: '0px 120px' });
    vids.forEach(function (v) {
      v.addEventListener('playing', function () { var t = v.closest('[data-ready]'); if (t) t.classList.add('ready'); }, { once: true });
      io.observe(v);
    });
  }

  var row = document.getElementById('bundleRow');
  var prev = document.getElementById('bPrev'), next = document.getElementById('bNext');
  if (!row || !prev || !next) return;
  function step() { var f = row.firstElementChild; return (f ? f.getBoundingClientRect().width : 300) + 16; }
  function arrows() {
    prev.disabled = row.scrollLeft < 4;
    next.disabled = row.scrollLeft + row.clientWidth > row.scrollWidth - 4;
  }
  prev.addEventListener('click', function () { row.scrollBy({ left: -step(), behavior: 'smooth' }); });
  next.addEventListener('click', function () { row.scrollBy({ left: step(), behavior: 'smooth' }); });
  row.addEventListener('scroll', function () { requestAnimationFrame(arrows); }, { passive: true });
  addEventListener('resize', arrows);
  arrows();
})();
