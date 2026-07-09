<?php
/*
 * Baut ein Installations-ZIP für ein Release.
 *
 * Aufruf (im Projektordner):
 *   php tools/build-release.php 1.0.1
 *
 * Ergebnis:  dist/wol-passkey-1.0.1.zip
 *
 * Das ZIP enthält nur die zum Betrieb nötigen Dateien (alles unter dem
 * Ordner "wol-passkey/", mit Vorwärts-Schrägstrichen, damit es auf
 * Linux/NAS sauber entpackt). Geheimnisse und Entwicklungs-Dateien werden
 * bewusst ausgeschlossen – siehe $excludes unten.
 */

if (!extension_loaded('zip')) {
    fwrite(STDERR, "Fehler: PHP-Extension 'zip' ist nicht aktiv.\n");
    fwrite(STDERR, "  -> php -d extension=zip tools/build-release.php <version>\n");
    exit(1);
}

$version = $argv[1] ?? '';
if ($version === '' || !preg_match('/^\d+\.\d+\.\d+$/', $version)) {
    fwrite(STDERR, "Aufruf: php tools/build-release.php <version>   (z.B. 1.0.1)\n");
    exit(1);
}

$root    = dirname(__DIR__);                 // Projekt-Hauptordner
$distDir = $root . DIRECTORY_SEPARATOR . 'dist';
$out     = $distDir . DIRECTORY_SEPARATOR . "wol-passkey-$version.zip";
$prefix  = 'wol-passkey/';                   // Ordner-Präfix im ZIP

// Diese Pfade/Muster kommen NICHT ins Installations-ZIP.
// Vergleich erfolgt gegen den relativen Pfad mit Vorwärts-Schrägstrichen.
$excludes = [
    'config.php',              // enthält den geheimen Setup-Schlüssel!
    'README.md',               // Doku, für den Betrieb nicht nötig
    '.gitignore',
    'remote.png',              // ungenutztes Alt-Asset
    'debug-host.php',          // lokales Werkzeug
    'build-release.cmd',       // Build-Werkzeug (Windows-Starter)
    'auth/data.php',           // zur Laufzeit erzeugte Nutzerdaten
    'auth/devices-data.php',   // zur Laufzeit erzeugte Geräteliste
];
$excludeDirs   = ['.git', '.claude', 'docs', 'dist', 'tools']; // ganze Ordner
$excludeGlobs  = ['auth/*.tmp', '*.cmd'];                      // Muster

function is_excluded(string $rel, array $excludes, array $excludeDirs, array $excludeGlobs): bool {
    foreach ($excludeDirs as $d) {
        if ($rel === $d || strpos($rel, $d . '/') === 0) return true;
    }
    if (in_array($rel, $excludes, true)) return true;
    foreach ($excludeGlobs as $g) {
        if (fnmatch($g, $rel)) return true;
    }
    return false;
}

if (!is_dir($distDir) && !mkdir($distDir, 0777, true) && !is_dir($distDir)) {
    fwrite(STDERR, "Konnte Ordner 'dist' nicht anlegen.\n");
    exit(1);
}

$zip = new ZipArchive();
if ($zip->open($out, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    fwrite(STDERR, "Konnte ZIP nicht anlegen: $out\n");
    exit(1);
}

$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
);

$count = 0;
$rootN = rtrim(str_replace(DIRECTORY_SEPARATOR, '/', $root), '/');
foreach ($it as $file) {
    if ($file->isDir()) continue;
    $abs = str_replace(DIRECTORY_SEPARATOR, '/', $file->getPathname());
    $rel = ltrim(substr($abs, strlen($rootN)), '/');
    if (is_excluded($rel, $excludes, $excludeDirs, $excludeGlobs)) continue;
    $zip->addFile($file->getPathname(), $prefix . $rel);
    $count++;
}
$zip->close();

// Sicherheits-Gegenprobe: keine Backslashes, kein Root-config.php im Archiv.
$check = new ZipArchive();
$check->open($out);
$bad = 0; $hasSecret = false;
for ($i = 0; $i < $check->numFiles; $i++) {
    $name = $check->getNameIndex($i);
    if (strpos($name, chr(92)) !== false) $bad++;
    if ($name === $prefix . 'config.php') $hasSecret = true;
}
$check->close();

if ($bad > 0 || $hasSecret) {
    fwrite(STDERR, "ABBRUCH: Archiv enthaelt fehlerhafte Pfade oder das geheime config.php.\n");
    unlink($out);
    exit(1);
}

$rel = str_replace($rootN . '/', '', str_replace(DIRECTORY_SEPARATOR, '/', $out));
printf("OK: %d Dateien -> %s (%d KB)\n", $count, $rel, (int)round(filesize($out) / 1024));
