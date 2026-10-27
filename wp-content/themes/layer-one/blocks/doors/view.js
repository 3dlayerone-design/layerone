/* Three doors (front end): each card cross-fades through its photos and videos. A video plays to its end before the next one shows. */
(function () {
  var reduce = matchMedia('(prefers-reduced-motion:reduce)').matches;

  document.querySelectorAll('.l2-doors .door-media').forEach(function (box, k) {
    var els = Array.prototype.slice.call(box.querySelectorAll('img, video'));
    var onScreen = true, i = 0, timer = 0;

    /* a lone video loops by itself (autoplay is set in the markup) */
    if (els.length < 2) {
      var only = els[0];
      if (only && only.tagName === 'VIDEO' && !reduce) { only.play().catch(function () {}); }
      return;
    }
    if (reduce) return;

    function play(el) {
      if (el.tagName !== 'VIDEO') return;
      el.currentTime = 0;
      el.play().catch(function () {});
    }
    function stop(el) { if (el.tagName === 'VIDEO') el.pause(); }

    function advance() {
      clearTimeout(timer);
      stop(els[i]);
      els[i].classList.remove('on');
      i = (i + 1) % els.length;
      els[i].classList.add('on');
      play(els[i]);
      schedule();
    }
    function schedule() {
      clearTimeout(timer);
      var cur = els[i];
      if (cur.tagName === 'VIDEO') {
        cur.onended = function () { if (onScreen && !document.hidden) advance(); };
        timer = setTimeout(advance, 20000); /* safety: never get stuck on a clip that cannot play */
      } else {
        timer = setTimeout(function () { if (document.hidden || !onScreen) { schedule(); } else { advance(); } }, 3600);
      }
    }

    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (es) {
        onScreen = es[0].isIntersecting;
        if (els[i].tagName === 'VIDEO') { onScreen ? els[i].play().catch(function () {}) : els[i].pause(); }
      }).observe(box);
    }

    setTimeout(function () { play(els[i]); schedule(); }, 900 + k * 1200);
  });
})();
