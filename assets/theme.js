/*
  Theme-Umschalter. Merkt die Wahl im Browser (localStorage) und setzt das
  Attribut data-theme auf <html>. Standard ist "daylight".
*/
(function () {
  function current() {
    try { return localStorage.getItem('wol_theme') || 'daylight'; }
    catch (e) { return 'daylight'; }
  }

  function markActive(theme) {
    var btns = document.querySelectorAll('[data-theme-btn]');
    for (var i = 0; i < btns.length; i++) {
      btns[i].classList.toggle('active', btns[i].getAttribute('data-theme-btn') === theme);
    }
  }

  window.wolSetTheme = function (theme) {
    try { localStorage.setItem('wol_theme', theme); } catch (e) {}
    document.documentElement.setAttribute('data-theme', theme);
    markActive(theme);
  };

  document.addEventListener('DOMContentLoaded', function () {
    var btns = document.querySelectorAll('[data-theme-btn]');
    for (var i = 0; i < btns.length; i++) {
      (function (btn) {
        btn.addEventListener('click', function () {
          window.wolSetTheme(btn.getAttribute('data-theme-btn'));
        });
      })(btns[i]);
    }
    if (!document.documentElement.getAttribute('data-theme')) {
      document.documentElement.setAttribute('data-theme', 'daylight');
    }
    markActive(current());
  });
})();
