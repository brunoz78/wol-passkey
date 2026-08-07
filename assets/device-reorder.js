(function () {
  var list = document.getElementById('deviceList');
  if (!list) return;

  var dragEl = null;
  var ghost = null;
  var startX = 0, startY = 0, baseTop = 0, ghostHeight = 0;
  var lastEvent = null;
  var rafScheduled = false;

  function items() {
    return Array.prototype.slice.call(list.querySelectorAll('.item'));
  }

  function startDrag(item, e) {
    var rect = item.getBoundingClientRect();
    dragEl = item;
    startX = e.clientX;
    startY = e.clientY;
    baseTop = rect.top;
    ghostHeight = rect.height;

    ghost = item.cloneNode(true);
    ghost.classList.add('drag-ghost');
    ghost.style.width = rect.width + 'px';
    ghost.style.left = rect.left + 'px';
    ghost.style.top = rect.top + 'px';
    document.body.appendChild(ghost);

    item.classList.add('drag-placeholder');

    document.addEventListener('pointermove', onPointerMove, { passive: false });
    document.addEventListener('pointerup', endDrag);
    document.addEventListener('pointercancel', endDrag);
  }

  function onPointerMove(e) {
    if (!dragEl) return;
    e.preventDefault();
    lastEvent = e;
    if (!rafScheduled) {
      rafScheduled = true;
      requestAnimationFrame(applyMove);
    }
  }

  // Läuft höchstens 1x pro Frame statt bei jedem einzelnen Touch-/Mausevent -
  // hält es auf dem Handy flüssig und verhindert liegenbleibende Events.
  function applyMove() {
    rafScheduled = false;
    if (!dragEl || !lastEvent) return;
    var e = lastEvent;
    var dx = e.clientX - startX;
    var dy = e.clientY - startY;
    ghost.style.transform = 'translate(' + dx + 'px,' + dy + 'px)';

    // Zielposition anhand der Mitte des Geist-Elements bestimmen (nicht des
    // Cursors direkt) - fühlt sich beim "Reinspringen" natürlicher an.
    var ghostMid = baseTop + dy + ghostHeight / 2;
    var siblings = items().filter(function (it) { return it !== dragEl; });
    var next = null;
    for (var i = 0; i < siblings.length; i++) {
      var r = siblings[i].getBoundingClientRect();
      if (ghostMid < r.top + r.height / 2) {
        next = siblings[i];
        break;
      }
    }
    if (dragEl.nextElementSibling !== next) {
      list.insertBefore(dragEl, next);
    }
  }

  function endDrag() {
    if (!dragEl) return;
    var el = dragEl;
    dragEl = null;
    lastEvent = null;
    el.classList.remove('drag-placeholder');
    if (ghost) {
      ghost.remove();
      ghost = null;
    }
    document.removeEventListener('pointermove', onPointerMove);
    document.removeEventListener('pointerup', endDrag);
    document.removeEventListener('pointercancel', endDrag);

    var fd = new FormData();
    fd.append('csrf_token', list.getAttribute('data-csrf'));
    fd.append('action', 'reorder');
    items().forEach(function (it) { fd.append('order[]', it.getAttribute('data-name')); });

    fetch('devices.php', { method: 'POST', credentials: 'same-origin', body: fd })
      .then(function () { window.location.reload(); })
      .catch(function () { window.location.reload(); });
  }

  items().forEach(function (item) {
    var handle = item.querySelector('.drag-handle');
    if (!handle) return;
    handle.addEventListener('pointerdown', function (e) {
      e.preventDefault();
      startDrag(item, e);
    });
  });
})();
