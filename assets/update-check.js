(function () {
  // Manueller Update-Check aus dem Menü (Knopf in partials/head.php). Fragt
  // update-check.php per POST ab und zeigt das Ergebnis kurz im Knopf selbst
  // an, statt die Seite neu zu laden.
  document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('waUpdateCheckBtn');
    var label = document.getElementById('waUpdateCheckLabel');
    if (!btn || !label) return;

    var defaultText = label.textContent;
    var i18n = window.WOL_I18N || {};
    var resetTimer = null;

    function zeigen(text) {
      label.textContent = text;
      clearTimeout(resetTimer);
      resetTimer = setTimeout(function () { label.textContent = defaultText; }, 6000);
    }

    btn.addEventListener('click', function () {
      btn.disabled = true;
      label.textContent = i18n.update_checking || defaultText;

      var body = new URLSearchParams();
      body.set('csrf_token', btn.getAttribute('data-csrf') || '');

      fetch('update-check.php', { method: 'POST', body: body, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (!data.ok) {
            zeigen(i18n.update_check_failed || defaultText);
          } else if (data.available) {
            zeigen((i18n.update_available || '{v}').replace('{v}', data.latest));
          } else {
            zeigen(i18n.update_uptodate || defaultText);
          }
        })
        .catch(function () {
          zeigen(i18n.update_check_failed || defaultText);
        })
        .finally(function () {
          btn.disabled = false;
        });
    });
  });
})();
