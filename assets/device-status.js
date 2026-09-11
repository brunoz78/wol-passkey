(function () {
  // Nach dem Aufwecken so lange nachfragen, bis das Gerät antwortet.
  // Grosszügig bemessen: Ein NAS mit Festplatten braucht gut zwei Minuten.
  var WAIT_SECONDS = 180;
  var WAIT_INTERVAL = 3000;

  var lang = document.documentElement.lang || 'de';

  function text(key, vars) {
    var s = (window.WOL_I18N && window.WOL_I18N[key]) || key;
    vars = vars || {};
    vars.min = String(WAIT_SECONDS / 60);
    return s.replace(/\{(\w+)\}/g, function (m, k) { return k in vars ? vars[k] : m; });
  }

  function unit(n, u) {
    try {
      return new Intl.NumberFormat(lang, { style: 'unit', unit: u, unitDisplay: 'short' }).format(n);
    } catch (e) {
      return n + ' ' + u.charAt(0);
    }
  }

  function duration(sec) {
    var d = Math.floor(sec / 86400), h = Math.floor(sec % 86400 / 3600), m = Math.floor(sec % 3600 / 60);
    if (d > 0) return unit(d, 'day') + (h ? ' ' + unit(h, 'hour') : '');
    if (h > 0) return unit(h, 'hour') + (m ? ' ' + unit(m, 'minute') : '');
    return m > 0 ? unit(m, 'minute') : '< ' + unit(1, 'minute');
  }

  function clock(date) {
    var sameDay = date.toDateString() === new Date().toDateString();
    var opts = sameDay ? { hour: '2-digit', minute: '2-digit' }
                       : { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' };
    return new Intl.DateTimeFormat(lang, opts).format(date);
  }

  function status(name) {
    return fetch('device-status.php?name=' + encodeURIComponent(name), { credentials: 'same-origin' })
      .then(function (r) { return r.json(); });
  }

  /*
    "Läuft seit 3 Std. 12 Min." bzw. bei ungenauer Datierung "Läuft spätestens
    seit 14:32". Der Startzeitpunkt wird am Element gemerkt, damit die Anzeige
    jede Minute weiterzählen kann, ohne erneut beim Server nachzufragen.
  */
  function showSince(el, data) {
    var since = el.querySelector('.since');
    if (!since || typeof data.since_s !== 'number') return;
    el._since = { start: Date.now() - data.since_s * 1000, exact: !!data.exact, online: data.status === 'online' };
    renderSince(el);
  }

  function renderSince(el) {
    var since = el.querySelector('.since'), s = el._since;
    if (!since || !s) return;
    var key = (s.online ? 'since_online' : 'since_offline') + (s.exact ? '' : '_approx');
    since.textContent = s.exact
      ? text(key, { d: duration(Math.max(0, (Date.now() - s.start) / 1000)) })
      : text(key, { t: clock(new Date(s.start)) });
    since.hidden = false;
  }

  function setOnline(el, online) {
    el.classList.toggle('is-online', online);
    var dot = el.querySelector('.status-dot');
    if (dot) dot.setAttribute('title', online ? text('device_online') : '');
  }

  function checkDevice(el) {
    status(el.getAttribute('data-check-name'))
      .then(function (data) {
        if (!data || data.status === 'unknown') return;
        setOnline(el, data.status === 'online');
        showSince(el, data);
      })
      .catch(function () { /* Netzwerkfehler: einfach keinen Status anzeigen */ });
  }

  function tileFor(name) {
    var tiles = document.querySelectorAll('[data-check-name]');
    for (var i = 0; i < tiles.length; i++) {
      if (tiles[i].getAttribute('data-check-name') === name) return tiles[i];
    }
    return null;
  }

  function waitForWake(box) {
    var name = box.getAttribute('data-wait-name');
    var start = Date.now();
    box.className = 'messageInfo';
    box.textContent = text('wake_waiting', { name: name });
    box.hidden = false;

    function poll() {
      status(name)
        .catch(function () { return null; })
        .then(function (data) {
          if (data && data.status === 'online') {
            box.className = 'messageOK';
            box.textContent = text('wake_online', { name: name });
            var tile = tileFor(name);
            if (tile) {
              setOnline(tile, true);
              showSince(tile, data);
            }
          } else if (Date.now() - start >= WAIT_SECONDS * 1000) {
            box.textContent = text('wake_timeout', { name: name });
          } else {
            setTimeout(poll, WAIT_INTERVAL);
          }
        });
    }
    poll();
  }

  document.addEventListener('DOMContentLoaded', function () {
    var tiles = document.querySelectorAll('[data-check-name]');
    tiles.forEach(checkDevice);
    setInterval(function () { tiles.forEach(renderSince); }, 60000);

    var box = document.getElementById('wakeWait');
    if (box) waitForWake(box);
  });
})();
