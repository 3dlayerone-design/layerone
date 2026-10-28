/* LayerOne – legal pages: highlight the current section in the contents list */
(function () {
  const links = [...document.querySelectorAll('.lg-toc a')];
  const secs = links.map(a => document.querySelector(a.getAttribute('href')));
  function mark() {
    let cur = 0;
    secs.forEach((s, i) => { if (s && s.getBoundingClientRect().top <= innerHeight * 0.35) cur = i; });
    links.forEach((a, i) => a.classList.toggle('on', i === cur));
  }
  addEventListener('scroll', mark, { passive: true });
  addEventListener('resize', mark);
  mark();
})();
