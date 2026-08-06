(function () {
  function checkDevice(el) {
    var name = el.getAttribute('data-check-name');
    if (!name) return;

    fetch('device-status.php?name=' + encodeURIComponent(name), { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data || data.status !== 'online') return;
        el.classList.add('is-online');
        var dot = el.querySelector('.status-dot');
        var label = (window.WOL_I18N && window.WOL_I18N.device_online) || 'Online';
        if (dot) dot.setAttribute('title', label);
      })
      .catch(function () { /* Netzwerkfehler: einfach keinen Status anzeigen */ });
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-check-name]').forEach(checkDevice);
  });
})();
