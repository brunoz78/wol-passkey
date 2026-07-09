@echo off
setlocal
cd /d "%~dp0"

echo ============================================
echo   Installations-ZIP fuer WOL erstellen
echo ============================================
echo.

:frage
set "VERSION="
set /p "VERSION=Versionsnummer eingeben (z.B. 1.0.1): "
if "%VERSION%"=="" (
    echo.
    echo Keine Eingabe - bitte eine Versionsnummer eingeben.
    echo.
    goto frage
)

echo.
call "%~dp0build-release.cmd" %VERSION%
echo.

if errorlevel 1 (
    echo Es ist ein Fehler aufgetreten - bitte Meldung oben pruefen.
) else (
    echo Fertig^! Das ZIP liegt im Ordner "dist".
)

echo.
pause
