/* LayerOne – contact page: the form opens the visitor's email app with the message filled in (no server needed). */
(function () {
  const $ = s => document.querySelector(s);
  const TO = $('#ctForm').dataset.to;
  $('#ctForm').addEventListener('submit', e => {
    e.preventDefault();
    const f = e.currentTarget;
    if (!f.checkValidity()) { f.reportValidity(); return; }
    const v = n => f.elements[n].value.trim();
    const subject = `[${v('topic')}] ${v('name')}${v('order') ? ' · Order ' + v('order') : ''}`;
    const body = `${v('message')}\n\n—\nName: ${v('name')}\nEmail: ${v('email')}\nPhone: ${v('phone') || '-'}\nOrder no.: ${v('order') || '-'}`;
    location.href = `mailto:${TO}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
    window.LO && LO.toast('Opening your email app…');
  });
  const copy = $('#copyMail');
  if (copy) copy.onclick = async () => {
    try { await navigator.clipboard.writeText(TO); window.LO && LO.toast('Email address copied'); } catch (e) { window.LO && LO.toast(TO); }
  };
})();
