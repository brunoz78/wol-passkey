@echo off
setlocal
rem Baut das Installations-ZIP.  Aufruf:  build-release.cmd 1.0.1

set "VERSION=%~1"
if "%VERSION%"=="" (
    echo Aufruf: build-release.cmd ^<version^>   z.B.  build-release.cmd 1.0.1
    exit /b 1
)

rem PHP suchen: erst im PATH, sonst der Standard-Installationspfad.
set "PHP="
where php >nul 2>nul && set "PHP=php"
if not defined PHP if exist "C:\Program Files\PHP\current\php.exe" set "PHP=C:\Program Files\PHP\current\php.exe"

if not defined PHP (
    echo PHP wurde nicht gefunden. Bitte den Pfad in dieser .cmd anpassen.
    exit /b 1
)

"%PHP%" -d extension=zip "%~dp0tools\build-release.php" %VERSION%
