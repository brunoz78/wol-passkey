(function () {
  // Zeiten des Verlaufs in der Zeitzone des Browsers anzeigen und nach Tagen
  // gruppieren. Der Server kennt die Zeitzone des Betrachters nicht; PHP läuft
  // im Container oft auf UTC.
  document.addEventListener('DOMContentLoaded', function () {
    var list = document.getElementById('logList');
    if (!list) return;

    var lang = document.documentElement.lang || 'de';
    var i18n = window.WOL_I18N || {};
    var timeFmt = new Intl.DateTimeFormat(lang, { hour: '2-digit', minute: '2-digit' });
    var now = new Date();

    function dayKey(d) { return d.getFullYear() + '-' + d.getMonth() + '-' + d.getDate(); }
    var today = dayKey(now);
    var yesterday = dayKey(new Date(now.getFullYear(), now.getMonth(), now.getDate() - 1));

    function dayLabel(d) {
      var key = dayKey(d);
      if (key === today) return i18n.log_today || key;
      if (key === yesterday) return i18n.log_yesterday || key;
      var opts = { weekday: 'long', day: 'numeric', month: 'long' };
      if (d.getFullYear() !== now.getFullYear()) opts.year = 'numeric';
      return new Intl.DateTimeFormat(lang, opts).format(d);
    }

    var lastKey = null;
    list.querySelectorAll('.logrow').forEach(function (row) {
      var d = new Date(parseInt(row.getAttribute('data-ts'), 10) * 1000);
      var t = row.querySelector('.lt');
      if (t) t.textContent = timeFmt.format(d);

      var key = dayKey(d);
      if (key !== lastKey) {
        var head = document.createElement('li');
        head.className = 'logday';
        head.textContent = dayLabel(d);
        list.insertBefore(head, row);
        lastKey = key;
      }
    });
  });
})();
