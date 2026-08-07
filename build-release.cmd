@echo off
setlocal enabledelayedexpansion
rem Baut das Installations-ZIP.  Aufruf:  build-release.cmd 1.0.1

set "VERSION=%~1"
if "%VERSION%"=="" (
    echo Aufruf: build-release.cmd ^<version^>   z.B.  build-release.cmd 1.0.1
    exit /b 1
)

rem PHP suchen: erst im PATH, sonst gaengige Installationspfade.
set "PHP="
for /f "delims=" %%F in ('where php 2^>nul') do if not defined PHP set "PHP=%%F"
if not defined PHP if exist "C:\Program Files\PHP\current\php.exe" set "PHP=C:\Program Files\PHP\current\php.exe"
if not defined PHP for /d %%D in ("%LOCALAPPDATA%\Programs\PHP\*") do if exist "%%D\nts\x64\php.exe" set "PHP=%%D\nts\x64\php.exe"

if not defined PHP (
    echo PHP wurde nicht gefunden. Bitte den Pfad in dieser .cmd anpassen.
    exit /b 1
)

for %%F in ("%PHP%") do set "PHP_DIR=%%~dpF"

rem Erster Versuch still im Hintergrund - manche PHP-ZIP-Downloads haben keine
rem aktive php.ini, dadurch zeigt extension_dir ins Leere und die zip-Extension
rem wird nicht gefunden. Die Warnung dafuer ist erwartbar und wuerde nur
rem verwirren; sie landet in einer Logdatei und wird nur bei echtem Fehlschlag
rem (kein automatischer Fallback moeglich) angezeigt.
set "BUILD_LOG=%TEMP%\wol-build-release-%RANDOM%.log"
"%PHP%" -d extension=zip "%~dp0tools\build-release.php" %VERSION% >"%BUILD_LOG%" 2>&1
set "RESULT=!errorlevel!"

if !RESULT! neq 0 (
    if exist "%PHP_DIR%ext\php_zip.dll" (
        echo Erneuter Versuch mit "-d extension_dir=%PHP_DIR%ext" ...
        "%PHP%" -d extension_dir="%PHP_DIR%ext" -d extension=zip "%~dp0tools\build-release.php" %VERSION%
        set "RESULT=!errorlevel!"
    ) else (
        type "%BUILD_LOG%"
    )
)
del "%BUILD_LOG%" >nul 2>nul
exit /b !RESULT!
