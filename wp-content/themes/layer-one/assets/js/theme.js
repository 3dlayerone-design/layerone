/* LayerOne – theme switch. Green & white is the default; the footer button switches to the dark theme and back.
   Loaded in <head> so the right theme is applied before the page paints. */
(function () {
  var theme = 'green';
  try { var s = localStorage.getItem('layerone_theme'); theme = s === 'dark' ? 'dark' : 'green'; } catch (e) {}
  var root = document.documentElement;
  function apply(t) {
    root.classList.toggle('green', t === 'green');
    root.dataset.theme = t;
    var m = document.querySelector('meta[name="theme-color"]');
    if (m) m.setAttribute('content', t === 'green' ? '#ffffff' : '#0A0A0A');
  }
  apply(theme);
  document.addEventListener('DOMContentLoaded', function () {
    apply(theme);
    var b = document.getElementById('themeBtn');
    if (!b) return;
    var SUN = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>';
    var MOON = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg>';
    /* light theme shows the moon (switch to dark), dark theme shows the sun (switch to light) */
    var label = function () {
      var toDark = theme === 'green';
      b.innerHTML = toDark ? MOON : SUN;
      b.setAttribute('aria-label', toDark ? 'Switch to dark theme' : 'Switch to light theme');
      b.title = toDark ? 'Dark theme' : 'Light theme';
    };
    label();
    b.addEventListener('click', function () {
      theme = theme === 'green' ? 'dark' : 'green';
      try { localStorage.setItem('layerone_theme', theme); } catch (e) {}
      apply(theme); label();
    });
  });
})();
