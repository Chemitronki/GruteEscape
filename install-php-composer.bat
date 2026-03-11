@echo off
echo ========================================
echo Instalador de PHP y Composer para Windows
echo ========================================
echo.

REM Verificar si tenemos permisos de administrador
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo ADVERTENCIA: Se recomienda ejecutar como Administrador
    echo.
)

echo Paso 1: Descargando PHP 8.2...
echo.

REM Crear directorio para PHP
if not exist "C:\php" mkdir C:\php

REM Descargar PHP (versión thread-safe para Windows)
powershell -Command "& {Invoke-WebRequest -Uri 'https://windows.php.net/downloads/releases/php-8.2.13-Win32-vs16-x64.zip' -OutFile 'php.zip'}"

echo Paso 2: Extrayendo PHP...
powershell -Command "& {Expand-Archive -Path 'php.zip' -DestinationPath 'C:\php' -Force}"
del php.zip

echo Paso 3: Configurando PHP...
copy C:\php\php.ini-development C:\php\php.ini

REM Habilitar extensiones necesarias para Laravel
powershell -Command "(Get-Content C:\php\php.ini) -replace ';extension=curl', 'extension=curl' | Set-Content C:\php\php.ini"
powershell -Command "(Get-Content C:\php\php.ini) -replace ';extension=fileinfo', 'extension=fileinfo' | Set-Content C:\php\php.ini"
powershell -Command "(Get-Content C:\php\php.ini) -replace ';extension=mbstring', 'extension=mbstring' | Set-Content C:\php\php.ini"
powershell -Command "(Get-Content C:\php\php.ini) -replace ';extension=openssl', 'extension=openssl' | Set-Content C:\php\php.ini"
powershell -Command "(Get-Content C:\php\php.ini) -replace ';extension=pdo_mysql', 'extension=pdo_mysql' | Set-Content C:\php\php.ini"
powershell -Command "(Get-Content C:\php\php.ini) -replace ';extension=zip', 'extension=zip' | Set-Content C:\php\php.ini"

echo Paso 4: Agregando PHP al PATH...
setx PATH "%PATH%;C:\php" /M

echo Paso 5: Descargando Composer...
cd C:\php
powershell -Command "& {Invoke-WebRequest -Uri 'https://getcomposer.org/installer' -OutFile 'composer-setup.php'}"

echo Paso 6: Instalando Composer...
C:\php\php.exe composer-setup.php --install-dir=C:\php --filename=composer
del composer-setup.php

echo.
echo ========================================
echo Instalacion completada!
echo ========================================
echo.
echo IMPORTANTE: Cierra y vuelve a abrir esta ventana de comandos
echo para que los cambios en el PATH surtan efecto.
echo.
echo Luego verifica la instalacion con:
echo   php --version
echo   composer --version
echo.
pause
