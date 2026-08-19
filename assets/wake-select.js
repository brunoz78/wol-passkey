(function () {
  // Schaltet den Aufwecken-Knopf erst frei, wenn ein Gerät ausgewählt ist.
  // Verhindert auch, dass ein Klick ins Leere läuft: Die Radio-Buttons sind
  // unsichtbar gemacht (siehe .dev input in smartphone.css), worauf der
  // Browser seine native "Bitte auswählen"-Sprechblase sonst unsichtbar
  // verankern würde.
  document.addEventListener('DOMContentLoaded', function () {
    var form = document.forms['WakeOnLan'];
    var btn = document.getElementById('wakeBtn');
    if (!form || !btn) return;

    function sync() {
      btn.disabled = !form.querySelector('input[name="wake_machine"]:checked');
    }
    form.addEventListener('change', sync);
    sync();
  });
})();
