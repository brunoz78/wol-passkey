/*
  Client-Helfer für WebAuthn/Passkey-Login.
  Das Serialisierungsformat "=?BINARY?B?...?=" für Binärdaten in JSON
  entspricht dem Format der PHP-Bibliothek lbuchs/WebAuthn.
*/

function waRecursiveBase64StrToArrayBuffer(obj) {
  var prefix = '=?BINARY?B?';
  var suffix = '?=';
  if (typeof obj === 'object' && obj !== null) {
    for (var key in obj) {
      if (typeof obj[key] === 'string') {
        var str = obj[key];
        if (str.substring(0, prefix.length) === prefix && str.substring(str.length - suffix.length) === suffix) {
          var b64 = str.substring(prefix.length, str.length - suffix.length);
          var bin = window.atob(b64);
          var bytes = new Uint8Array(bin.length);
          for (var i = 0; i < bin.length; i++) {
            bytes[i] = bin.charCodeAt(i);
          }
          obj[key] = bytes.buffer;
        }
      } else {
        waRecursiveBase64StrToArrayBuffer(obj[key]);
      }
    }
  }
}

function waArrayBufferToBase64(buffer) {
  var binary = '';
  var bytes = new Uint8Array(buffer);
  for (var i = 0; i < bytes.byteLength; i++) {
    binary += String.fromCharCode(bytes[i]);
  }
  return window.btoa(binary);
}

function waSetStatus(el, msg, isError) {
  if (!el) return;
  el.textContent = msg;
  el.className = isError ? 'messageNOK' : 'messageOK';
}

/*
  Neutraler Hinweis - weder Erfolg noch Fehler. Gebraucht, wenn der Nutzer
  eine automatisch gestartete Passkey-Abfrage abbricht: Das ist kein Fehler,
  sondern eine Entscheidung, und soll auch nicht rot aussehen.
*/
function waSetHint(el, msg) {
  if (!el) return;
  el.textContent = msg;
  el.className = 'messageInfo';
}

/*
  Übersetzter Text. Die Texte liefert partials/head.php als window.WOL_I18N
  (Schlüssel "js.*" aus den Dateien in lang/, ohne das Präfix).
*/
function waT(key) {
  return (window.WOL_I18N && window.WOL_I18N[key]) || key;
}

/*
  Prüft, ob Passkeys überhaupt möglich sind, und liefert den Grund als
  fertigen Text zurück (oder null, wenn alles passt).

  Wichtig: Ohne sicheren Kontext (HTTPS bzw. localhost) blendet der Browser
  window.PublicKeyCredential komplett aus. Ohne diese Unterscheidung sähe das
  wie ein zu alter Browser aus, obwohl nur das Zertifikat fehlt.
*/
function waUnavailableReason() {
  if (!window.isSecureContext) return waT('no_secure_context');
  if (!window.PublicKeyCredential) return waT('no_support');
  return null;
}

/*
  Übersetzt die Fehler der WebAuthn-Schnittstelle. Der Browser wirft dort
  DOMException-Objekte mit englischem Text und Link zur Spezifikation - für
  den Abbruch durch den Nutzer, mit Abstand der häufigste Fall, ist das
  unbrauchbar. Unterschieden wird über err.name, weil der Text je nach
  Browser abweicht. Eigene Fehler tragen bereits einen übersetzten Text und
  fallen unten durch.
*/
function waErrorMessage(err) {
  if (!err) return waT('unknown_error');
  switch (err.name) {
    case 'NotAllowedError':   // abgebrochen oder Zeitlimit
    case 'AbortError':
      return waT('err_cancelled');
    case 'InvalidStateError': // auf diesem Gerät gibt es den Passkey schon
      return waT('err_already_registered');
    case 'SecurityError':     // Hostname passt nicht zur RP-ID
      return waT('err_origin');
    case 'NotSupportedError':
      return waT('no_support');
  }
  return err.message || waT('unknown_error');
}

async function waRegisterPasskey(statusEl, deviceName) {
  try {
    var unavailable = waUnavailableReason();
    if (unavailable) {
      throw new Error(unavailable);
    }

    waSetStatus(statusEl, waT('confirm_biometry'), false);

    var optRes = await fetch('webauthn-register-options.php', { credentials: 'same-origin' });
    var optJson = await optRes.json();
    if (!optJson.success) {
      throw new Error(optJson.msg || waT('register_prepare'));
    }

    var createArgs = optJson.options;
    waRecursiveBase64StrToArrayBuffer(createArgs);

    var cred = await navigator.credentials.create(createArgs);

    var payload = {
      deviceName: deviceName || '',
      clientDataJSON: waArrayBufferToBase64(cred.response.clientDataJSON),
      attestationObject: waArrayBufferToBase64(cred.response.attestationObject)
    };

    var verifyRes = await fetch('webauthn-register-verify.php', {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    var verifyJson = await verifyRes.json();
    if (!verifyJson.success) {
      throw new Error(verifyJson.msg || waT('register_failed'));
    }

    waSetStatus(statusEl, waT('register_ok'), false);
    waRememberDevice();
    return true;
  } catch (err) {
    waSetStatus(statusEl, waErrorMessage(err), true);
    return false;
  }
}

/*
  Passkey-Anmeldung. "auto" ist true, wenn die Abfrage beim Laden der Seite
  von selbst gestartet wurde und nicht, weil der Nutzer den Knopf gedrückt hat.
*/
/*
  Die gerade laufende Abfrage (als AbortController).

  Chrome und Brave lehnen eine zweite WebAuthn-Abfrage sofort mit
  NotAllowedError ab, solange noch eine offen ist - ohne Dialog, sodass es wie
  ein sofortiger Abbruch durch den Nutzer aussieht. Deshalb:
    - eine automatische Abfrage tritt zurück, wenn schon eine läuft,
    - ein Klick des Nutzers hat Vorrang und bricht eine laufende Abfrage
      sauber ab, statt daneben eine zweite zu starten.
*/
var waLaufendeAbfrage = null;

/*
  Wurde auf dieser Seite schon einmal eine Abfrage vom Nutzer abgebrochen?
  Dann wird nicht mehr automatisch nachgefragt (siehe waWatchReturn).
*/
var waAbfrageAbgebrochen = false;

/*
  Zeitpunkt, zu dem die Seite zuletzt unsichtbar wurde. Wird gebraucht, um
  eine "tote" Abfrage zu erkennen: Geht die Seite in den Hintergrund, während
  eine Passkey-Abfrage läuft, beendet Android das zugehörige Promise oft nie.
  Die Sperre bliebe dann für immer stehen und es käme nie wieder eine Abfrage
  - bis die Seite neu geladen wird.
*/
var waVerstecktSeit = 0;

function waClearStatus(el) {
  if (!el) return;
  el.textContent = '';
  el.className = '';
}

async function waLoginWithPasskey(statusEl, auto) {
  // Automatisch gestartete Abfragen funken nie dazwischen.
  if (auto && waLaufendeAbfrage) return false;

  // Der Nutzer hat Vorrang: laufende Abfrage abbrechen, damit der Browser die
  // neue nicht als "läuft schon" abweist.
  if (waLaufendeAbfrage) {
    waLaufendeAbfrage.wolAbgeloest = true;
    try { waLaufendeAbfrage.abort(); } catch (e) { /* egal */ }
    waLaufendeAbfrage = null;
  }

  var abbruch = new AbortController();
  abbruch.wolStart = Date.now();
  waLaufendeAbfrage = abbruch;

  // Wird direkt vor dem Aufruf gesetzt; hier vorbelegt, damit ein früher
  // Fehler (z.B. beim Laden der Optionen) nicht in den Fokus-Sonderfall läuft.
  var hatteFokus = true;

  // Alte Meldung wegräumen, sonst wirkt sie wie die Antwort auf diesen Klick.
  if (!auto) waClearStatus(statusEl);

  try {
    var unavailable = waUnavailableReason();
    if (unavailable) {
      throw new Error(unavailable);
    }

    var optRes = await fetch('webauthn-login-options.php', { credentials: 'same-origin' });
    var optJson = await optRes.json();
    if (!optJson.success) {
      throw new Error(optJson.msg || waT('login_prepare'));
    }

    var getArgs = optJson.options;
    waRecursiveBase64StrToArrayBuffer(getArgs);

    waSetStatus(statusEl, waT('confirm_fingerprint'), false);

    // Über das Signal lässt sich diese Abfrage später sauber abbrechen.
    getArgs.signal = abbruch.signal;

    // Ohne Fokus lehnt Chrome die Abfrage mit demselben Fehler ab wie ein
    // Abbruch durch den Nutzer. Der Unterschied wird unten gebraucht.
    hatteFokus = document.hasFocus ? document.hasFocus() : true;

    var cred = await navigator.credentials.get(getArgs);

    var payload = {
      id: waArrayBufferToBase64(cred.rawId),
      clientDataJSON: waArrayBufferToBase64(cred.response.clientDataJSON),
      authenticatorData: waArrayBufferToBase64(cred.response.authenticatorData),
      signature: waArrayBufferToBase64(cred.response.signature),
      userHandle: cred.response.userHandle ? waArrayBufferToBase64(cred.response.userHandle) : null
    };

    var verifyRes = await fetch('webauthn-login-verify.php', {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    var verifyJson = await verifyRes.json();
    if (!verifyJson.success) {
      throw new Error(verifyJson.msg || waT('login_failed'));
    }

    waSetStatus(statusEl, waT('login_ok'), false);
    waRememberDevice();
    window.location.href = verifyJson.redirect || 'index.php';
    return true;
  } catch (err) {
    // Diese Abfrage wurde von einer neueren abgelöst (der Nutzer hat den Knopf
    // gedrückt, während die automatische noch lief). Das ist kein Fehler und
    // darf weder eine Meldung zeigen noch eine Einstellung ändern.
    if (abbruch.wolAbgeloest) return false;

    // Scheiterte eine automatische Abfrage, obwohl die Seite gar keinen Fokus
    // hatte, hat der Nutzer nichts abgebrochen - der Browser wollte nur
    // gerade nicht. Folgenlos beenden: keine Meldung, keine Einstellung
    // ändern. Das nächste Fokus-Ereignis versucht es erneut.
    if (auto && !hatteFokus &&
        err && (err.name === 'NotAllowedError' || err.name === 'AbortError')) {
      waClearStatus(statusEl); // sonst bliebe "Bitte bestätigen …" stehen
      return false;
    }

    // Bricht der Nutzer eine von selbst gestartete Abfrage ab, ist das keine
    // Fehlermeldung wert - er wollte sie ja gar nicht. Stattdessen wird die
    // automatische Abfrage auf diesem Gerät abgeschaltet, damit sie beim
    // nächsten Aufruf nicht wieder dazwischenfunkt.
    if (auto && (err && (err.name === 'NotAllowedError' || err.name === 'AbortError'))) {
      waSetAutoAsk(false);
      waSyncAutoAsk();
      waSetHint(statusEl, waT('auto_off'));
      return false;
    }
    // Hat der Nutzer eine selbst gestartete Abfrage abgebrochen, wird auf
    // dieser Seite nicht mehr von selbst nachgefragt - sonst käme beim
    // nächsten Fensterwechsel sofort wieder ein Dialog.
    if (err && (err.name === 'NotAllowedError' || err.name === 'AbortError')) {
      waAbfrageAbgebrochen = true;
    }
    waSetStatus(statusEl, waErrorMessage(err), true);
    return false;
  } finally {
    // Nur zurücksetzen, wenn inzwischen keine neuere Abfrage übernommen hat.
    if (waLaufendeAbfrage === abbruch) waLaufendeAbfrage = null;
  }
}

/*
  Merkt sich im lokalen Browser-Speicher, dass auf diesem Gerät ein Passkey
  registriert/benutzt wurde. Nur dann startet der Login beim Öffnen der
  Seite automatisch - Geräte ohne Passkey bekommen kein aufdringliches
  QR-Code-Popup.
*/
function waRememberDevice() {
  try {
    localStorage.setItem('wol_passkey_device', '1');
  } catch (e) { /* localStorage gesperrt (z.B. Privatmodus) - dann eben ohne */ }
}

function waIsKnownDevice() {
  try {
    return localStorage.getItem('wol_passkey_device') === '1';
  } catch (e) {
    return false;
  }
}

/*
  Ob beim Öffnen der Loginseite von selbst nach dem Passkey gefragt wird -
  pro Gerät einstellbar. Am Handy ist das bequem, am Desktop stört es, wenn
  man sich lieber mit dem Passwort anmeldet. Voreingestellt ist "ja", damit
  sich für bestehende Geräte nichts ändert.
*/
function waAutoAskEnabled() {
  try {
    return localStorage.getItem('wol_passkey_autoask') !== '0';
  } catch (e) {
    return true;
  }
}

function waSetAutoAsk(on) {
  try {
    localStorage.setItem('wol_passkey_autoask', on ? '1' : '0');
  } catch (e) { /* localStorage gesperrt (z.B. Privatmodus) - dann eben ohne */ }
}

/*
  Das Kästchen "Beim Öffnen automatisch fragen" auf der Loginseite. Es wird
  nur eingeblendet, wenn dieses Gerät überhaupt schon einen Passkey benutzt
  hat - sonst wäre es eine Einstellung für etwas, das hier gar nicht passiert.
*/
function waInitAutoAsk() {
  var wrap = document.getElementById('waAutoAsk');
  var box = document.getElementById('waAutoAskBox');
  if (!wrap || !box) return;
  if (waUnavailableReason() || !waIsKnownDevice()) return;

  wrap.hidden = false;
  box.checked = waAutoAskEnabled();
  box.addEventListener('change', function () {
    waSetAutoAsk(box.checked);
  });
}

function waSyncAutoAsk() {
  var box = document.getElementById('waAutoAskBox');
  if (box) box.checked = waAutoAskEnabled();
}

/*
  "?logout=1" (von logout.php) unterdrückt die automatische Abfrage direkt
  nach dem Abmelden - sonst käme man nie von der Loginseite weg.

  Der Parameter wird dabei sofort aus der Adresse entfernt, denn gemeint ist
  "dieses eine Mal", nicht "diese Adresse für immer". Sonst bleibt die Abfrage
  auch später aus: Mobilbrowser stellen beim Neustart den letzten Tab samt
  Adresse wieder her, und auch ein Neuladen oder ein Lesezeichen auf diese
  Adresse würde den Parameter mitschleppen.

  Liefert true, wenn gerade abgemeldet wurde.
*/
function waConsumeLogoutFlag() {
  var params = new URLSearchParams(window.location.search);
  if (!params.has('logout')) return false;

  params.delete('logout');
  try {
    var rest = params.toString();
    window.history.replaceState(null, '',
      window.location.pathname + (rest ? '?' + rest : '') + window.location.hash);
  } catch (e) { /* replaceState gesperrt - dann bleibt der Parameter eben stehen */ }
  return true;
}

/*
  Automatischer Passkey-Login beim Laden der Loginseite - aber nur wenn
  dieses Gerät als Passkey-Gerät bekannt ist und die automatische Abfrage
  nicht abgeschaltet wurde.
*/
function waAutoLoginIfKnownDevice(statusEl) {
  // Immer zuerst, damit die Adresse auch dann bereinigt wird, wenn unten
  // ohnehin abgebrochen wird (z.B. ohne HTTPS).
  var geradeAbgemeldet = waConsumeLogoutFlag();

  if (geradeAbgemeldet) return;
  if (waAbfrageAbgebrochen) return;
  if (waUnavailableReason()) return;
  if (!waIsKnownDevice()) return;
  if (!waAutoAskEnabled()) return;
  waLoginWithPasskey(statusEl, true);
}

/*
  Zusätzlicher Auslöser für die automatische Abfrage.

  Öffnet man die Seite über eine Verknüpfung auf dem Startbildschirm, holt der
  Browser oft nur den noch offenen Tab der letzten Sitzung nach vorne, statt
  die Seite neu zu laden. Dann läuft der Startcode kein zweites Mal - die
  Abfrage kam erst nach einem manuellen Neuladen. Deshalb wird zusätzlich
  reagiert, wenn die Seite wieder sichtbar wird ("visibilitychange") oder aus
  dem Vor-/Zurück-Zwischenspeicher zurückkommt ("pageshow" mit persisted).

  Ein erneutes Fragen ist dabei ungefährlich: Läuft schon eine Abfrage, blockt
  waLoginWithPasskey ab, und wer eine automatische Abfrage abbricht, schaltet
  sie damit für dieses Gerät ohnehin aus.
*/
function waWatchReturn(statusEl) {
  var geplant = false;

  function versuchen() {
    if (geplant) return;
    geplant = true;

    // Kurz warten statt sofort loszulegen, damit der Browser den
    // Seitenwechsel abschliessen kann. Bewusst OHNE Fokus-Bedingung: Meldet
    // ein Browser beim Zurückkehren keinen Fokus, käme sonst nie eine
    // Abfrage. Ein Fehlschlag mangels Fokus wird stattdessen unten in
    // waLoginWithPasskey folgenlos behandelt.
    setTimeout(function () {
      geplant = false;
      if (document.visibilityState !== 'visible') return;

      // Eine Abfrage, die noch von vor dem Verstecken der Seite stammt, gilt
      // als tot (siehe waVerstecktSeit) und wird abgeräumt - sonst blockiert
      // ihre Sperre jede weitere Abfrage dauerhaft.
      if (waLaufendeAbfrage && waLaufendeAbfrage.wolStart < waVerstecktSeit) {
        waLaufendeAbfrage.wolAbgeloest = true;
        try { waLaufendeAbfrage.abort(); } catch (e) { /* egal */ }
        waLaufendeAbfrage = null;
        waClearStatus(statusEl);
      }

      waAutoLoginIfKnownDevice(statusEl);
    }, 200);
  }

  // Mehrere Wege, weil die Browser sich unterschiedlich verhalten: Chrome auf
  // Android meldet die Rückkehr mal als Sichtbarkeit, mal als Fokus, mal über
  // die Page-Lifecycle-Ereignisse.
  document.addEventListener('visibilitychange', function () {
    if (document.visibilityState === 'visible') {
      versuchen();
    } else {
      waVerstecktSeit = Date.now();
    }
  });
  window.addEventListener('focus', versuchen);
  window.addEventListener('pageshow', function (e) {
    if (e && e.persisted) versuchen();
  });
  document.addEventListener('resume', versuchen);
}

