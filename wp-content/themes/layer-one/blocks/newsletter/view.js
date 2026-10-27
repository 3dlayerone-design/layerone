/* Drop list signup (front end): sends the email without leaving the page. */
(function () {
  var form = document.getElementById('nf');
  if (!form || !window.fetch) return;
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var body = new FormData(form); body.append('ajax', '1');
    var btn = form.querySelector('button'); btn.disabled = true;
    /* form.action is shadowed by the hidden input named "action", so read the attribute */
    fetch(form.getAttribute('action'), { method: 'POST', body: body, credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (j) {
        if (window.LO) LO.toast(j && j.success ? form.dataset.thanks : 'Please enter a valid email');
        if (j && j.success) form.reset();
      })
      .catch(function () { if (window.LO) LO.toast('Could not subscribe. Please try again.'); })
      .then(function () { btn.disabled = false; });
  });
})();
