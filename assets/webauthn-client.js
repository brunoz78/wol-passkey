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

async function waRegisterPasskey(statusEl, deviceName) {
  try {
    if (!window.PublicKeyCredential) {
      throw new Error('Dieser Browser unterstützt keine Passkeys.');
    }

    waSetStatus(statusEl, 'Bitte Biometrie am Gerät bestätigen …', false);

    var optRes = await fetch('webauthn-register-options.php', { credentials: 'same-origin' });
    var optJson = await optRes.json();
    if (!optJson.success) {
      throw new Error(optJson.msg || 'Fehler beim Vorbereiten der Registrierung.');
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
      throw new Error(verifyJson.msg || 'Registrierung fehlgeschlagen.');
    }

    waSetStatus(statusEl, 'Passkey erfolgreich registriert!', false);
    waRememberDevice();
    return true;
  } catch (err) {
    waSetStatus(statusEl, err.message || 'Unbekannter Fehler', true);
    return false;
  }
}

async function waLoginWithPasskey(statusEl) {
  try {
    if (!window.PublicKeyCredential) {
      throw new Error('Dieser Browser unterstützt keine Passkeys.');
    }

    var optRes = await fetch('webauthn-login-options.php', { credentials: 'same-origin' });
    var optJson = await optRes.json();
    if (!optJson.success) {
      throw new Error(optJson.msg || 'Fehler beim Vorbereiten der Anmeldung.');
    }

    var getArgs = optJson.options;
    waRecursiveBase64StrToArrayBuffer(getArgs);

    waSetStatus(statusEl, 'Bitte Fingerabdruck/Face ID bestätigen …', false);

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
      throw new Error(verifyJson.msg || 'Anmeldung fehlgeschlagen.');
    }

    waSetStatus(statusEl, 'Angemeldet, du wirst weitergeleitet …', false);
    waRememberDevice();
    window.location.href = verifyJson.redirect || 'index.php';
    return true;
  } catch (err) {
    waSetStatus(statusEl, err.message || 'Unbekannter Fehler', true);
    return false;
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
  Automatischer Passkey-Login beim Laden der Loginseite - aber nur wenn
  dieses Gerät als Passkey-Gerät bekannt ist. Nach "Abmelden" (?logout=1)
  wird nicht sofort wieder gefragt, sonst käme man nie von der Seite weg.
*/
function waAutoLoginIfKnownDevice(statusEl) {
  if (!window.PublicKeyCredential) return;
  if (!waIsKnownDevice()) return;
  if (new URLSearchParams(window.location.search).has('logout')) return;
  waLoginWithPasskey(statusEl);
}
