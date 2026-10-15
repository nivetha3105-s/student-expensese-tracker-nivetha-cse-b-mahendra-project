document.addEventListener('DOMContentLoaded', function () {

  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Auto-submit filters (month picker, category dropdown) on change
  document.querySelectorAll('[data-auto]').forEach(function (el) {
    el.addEventListener('change', function () { el.form.submit(); });
  });

  // Count-up numbers: <b data-count="123.45"> (the final value is already in the HTML)
  function fmt(n) {
    return '\u20B9' + n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }
  document.querySelectorAll('[data-count]').forEach(function (el) {
    var end = parseFloat(el.dataset.count);
    if (reduce || isNaN(end)) return;
    var t0 = null, dur = 1100;
    function tick(t) {
      if (t0 === null) t0 = t;
      var p = Math.min((t - t0) / dur, 1), e = 1 - Math.pow(1 - p, 4);
      el.textContent = fmt(end * e);
      if (p < 1) requestAnimationFrame(tick); else el.textContent = fmt(end);
    }
    el.textContent = fmt(0);
    requestAnimationFrame(tick);
  });

  // Success / info messages fade away on their own
  document.querySelectorAll('.alert.success, .alert.info').forEach(function (a) {
    setTimeout(function () { a.classList.add('gone'); }, 4500);
  });

  // Styled confirm dialog for destructive forms (delete)
  var dlg = document.createElement('dialog');
  dlg.innerHTML = '<h3>Delete transaction?</h3><p>This can\u2019t be undone.</p><div class="btns">' +
    '<button type="button" class="btn ghost" data-no>Keep it</button>' +
    '<button type="button" class="btn danger" data-yes>Delete</button></div>';
  document.body.appendChild(dlg);
  var pending = null;
  dlg.querySelector('[data-no]').onclick = function () { dlg.close(); pending = null; };
  dlg.querySelector('[data-yes]').onclick = function () {
    var form = pending; dlg.close(); pending = null;
    var row = form.closest('tr');
    if (row && !reduce) { row.classList.add('removing'); setTimeout(function () { form.submit(); }, 300); }
    else form.submit();
  };
  document.querySelectorAll('form[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (ev) {
      ev.preventDefault();
      pending = form;
      if (dlg.showModal) dlg.showModal();
      else if (confirm(form.getAttribute('data-confirm'))) form.submit();
    });
  });

  // Client-side validation for forms with class "js-validate"
  function setError(field, message) {
    clearError(field);
    field.classList.add('invalid');
    var span = document.createElement('span');
    span.className = 'field-error';
    span.textContent = message;
    field.parentNode.appendChild(span);
  }
  function clearError(field) {
    field.classList.remove('invalid');
    var old = field.parentNode.querySelector('.field-error');
    if (old) old.remove();
  }
  document.querySelectorAll('form.js-validate').forEach(function (form) {
    form.addEventListener('submit', function (ev) {
      var ok = true;
      form.querySelectorAll('input, select, textarea').forEach(function (f) {
        if (f.type === 'hidden' || f.offsetParent === null) return;
        clearError(f);
        var v = f.value.trim();
        if (f.required && v === '') { setError(f, 'This field is required.'); ok = false; return; }
        if (f.type === 'email' && v !== '' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) {
          setError(f, 'Enter a valid email address.'); ok = false; return;
        }
        if (f.name === 'password' && f.getAttribute('minlength') && v.length < 6) {
          setError(f, 'Password must be at least 6 characters.'); ok = false; return;
        }
        if (f.name === 'amount' && v !== '' && !(parseFloat(v) > 0)) {
          setError(f, 'Amount must be greater than 0.'); ok = false; return;
        }
      });
      if (!ok) ev.preventDefault();
    });
  });
});
