<?php
/*
  Gemeinsamer Seitenkopf für alle Seiten.
  Erwartet (optional) vor dem include gesetzt:
    $page_title  - Zusatz für den Browser-Tab-Titel (z.B. "Login")
    $brand_title - grosse Überschrift (Standard: $sitename)
    $brand_sub   - Untertitel unter der Überschrift
  Danach folgt der Seiteninhalt, abgeschlossen durch partials/foot.php.
*/
$sitename    = $sitename    ?? 'Wake on LAN';
$brand_title = $brand_title ?? $sitename;
$titleSuffix = isset($page_title) && $page_title !== '' ? ' – ' . $page_title : '';
?><!DOCTYPE html>
<html lang="de" data-theme="daylight">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
  <link rel="stylesheet" href="smartphone.css" />
  <title><?php echo htmlspecialchars($sitename . $titleSuffix); ?></title>
  <script>
    /* Theme vor dem Rendern setzen, damit es nicht kurz aufblitzt. */
    (function () {
      try {
        var t = localStorage.getItem('wol_theme');
        if (t) document.documentElement.setAttribute('data-theme', t);
      } catch (e) {}
    })();
  </script>
</head>
<body>
  <!-- Icon-Sammlung (einmal pro Seite, per <use> referenziert) -->
  <svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
    <symbol id="i-fp" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
      <path d="M2 12C2 6.5 6.5 2 12 2a10 10 0 0 1 8 4"/><path d="M5 19.5C5.5 18 6 15 6 12c0-.7.12-1.37.34-2"/>
      <path d="M17.29 21.02c.12-.6.43-2.3.5-3.02"/><path d="M12 10a2 2 0 0 0-2 2c0 1.02-.1 2.51-.26 4"/>
      <path d="M8.65 22c.21-.66.45-1.32.57-2"/><path d="M14 13.12c0 2.38 0 6.38-1 8.88"/>
      <path d="M2 16h.01"/><path d="M21.8 16c.2-2 .13-5.35 0-6"/><path d="M9 6.8a6 6 0 0 1 9 5.2c0 .47 0 1.17-.02 2"/>
    </symbol>
    <symbol id="i-pw" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M12 2v10"/><path d="M18.4 6.6a9 9 0 1 1-12.77.04"/></symbol>
    <symbol id="i-mon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
      <rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></symbol>
    <symbol id="i-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></symbol>
    <symbol id="i-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></symbol>
    <symbol id="i-spark" viewBox="0 0 24 24" fill="currentColor">
      <path d="M12 2l2.1 6L20 10l-5.9 2L12 18l-2.1-6L4 10l5.9-2L12 2Z"/></symbol>
    <symbol id="i-trash" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
      <path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></symbol>
    <symbol id="i-back" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M19 12H5M12 19l-7-7 7-7"/></symbol>
    <symbol id="i-plus" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M12 5v14M5 12h14"/></symbol>
    <symbol id="i-menu" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M3 6h18M3 12h18M3 18h18"/></symbol>
    <symbol id="i-close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M18 6 6 18M6 6l12 12"/></symbol>
    <symbol id="i-logout" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
      <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></symbol>
  </svg>

  <div class="app">
    <div class="topbar">
      <?php if (!empty($show_menu)):
        $cur = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
        $navItems = [
          'index.php'           => ['i-pw',     'Aufwecken'],
          'devices.php'         => ['i-mon',    'Geräte verwalten'],
          'register-passkey.php'=> ['i-fp',     'Passkey verwalten'],
          'logout.php'          => ['i-logout', 'Abmelden'],
        ];
      ?>
        <input type="checkbox" id="navToggle" class="nav-toggle" hidden />
        <label for="navToggle" class="hamburger" aria-label="Menü öffnen"><svg><use href="#i-menu"/></svg></label>
        <label for="navToggle" class="nav-overlay" aria-hidden="true"></label>
        <nav class="nav-menu" aria-label="Navigation">
          <div class="nav-head">
            <span><?php echo htmlspecialchars($sitename); ?></span>
            <label for="navToggle" class="nav-close" aria-label="Menü schließen"><svg><use href="#i-close"/></svg></label>
          </div>
          <?php foreach ($navItems as $href => $it): ?>
            <a href="<?php echo $href; ?>"<?php echo $href === $cur ? ' class="active" aria-current="page"' : ''; ?>>
              <svg><use href="#<?php echo $it[0]; ?>"/></svg><?php echo htmlspecialchars($it[1]); ?>
            </a>
          <?php endforeach; ?>
        </nav>
      <?php else: ?>
        <span class="topbar-slot"></span>
      <?php endif; ?>

      <div class="theme-switch" role="group" aria-label="Design wählen">
        <button type="button" data-theme-btn="daylight" title="Hell" aria-label="Helles Design"><svg><use href="#i-sun"/></svg></button>
        <button type="button" data-theme-btn="midnight" title="Dunkel" aria-label="Dunkles Design"><svg><use href="#i-moon"/></svg></button>
        <button type="button" data-theme-btn="vivid" title="Bunt" aria-label="Buntes Design"><svg><use href="#i-spark"/></svg></button>
      </div>
    </div>

    <header class="brand">
      <h1><?php echo htmlspecialchars($brand_title); ?></h1>
      <?php if (!empty($brand_sub)): ?><p class="sub"><?php echo htmlspecialchars($brand_sub); ?></p><?php endif; ?>
    </header>
