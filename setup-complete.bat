@echo off
echo ========================================
echo Lovecraftian Escape Room - Setup
echo ========================================
echo.

REM Check if we're in the right directory
if not exist "backend" (
    echo ERROR: No se encuentra el directorio backend
    echo Por favor ejecuta este script desde la raiz del proyecto
    pause
    exit /b 1
)

if not exist "frontend" (
    echo ERROR: No se encuentra el directorio frontend
    echo Por favor ejecuta este script desde la raiz del proyecto
    pause
    exit /b 1
)

echo [1/6] Configurando Backend...
echo.

REM Setup Backend
cd backend

REM Copy .env if it doesn't exist
if not exist ".env" (
    echo Copiando archivo .env...
    copy .env.example .env
) else (
    echo Archivo .env ya existe, saltando...
)

echo.
echo Instalando dependencias de Composer...
echo (Esto puede tardar varios minutos)
call composer install --no-interaction --prefer-dist --optimize-autoloader

if errorlevel 1 (
    echo.
    echo ERROR: Fallo la instalacion de Composer
    echo Asegurate de tener Composer instalado: https://getcomposer.org/
    cd ..
    pause
    exit /b 1
)

echo.
echo Generando clave de aplicacion...
call php artisan key:generate --force

echo.
echo [2/6] Configurando Frontend...
cd ..\frontend

REM Copy .env if it doesn't exist
if not exist ".env" (
    echo Copiando archivo .env...
    copy .env.example .env
) else (
    echo Archivo .env ya existe, saltando...
)

echo.
echo Instalando dependencias de npm...
echo (Esto puede tardar varios minutos)
call npm install

if errorlevel 1 (
    echo.
    echo ERROR: Fallo la instalacion de npm
    echo Asegurate de tener Node.js instalado: https://nodejs.org/
    cd ..
    pause
    exit /b 1
)

cd ..

echo.
echo ========================================
echo Instalacion completada!
echo ========================================
echo.
echo IMPORTANTE: Antes de continuar, debes:
echo.
echo 1. Crear la base de datos MySQL:
echo    CREATE DATABASE lovecraftian_escape;
echo.
echo 2. Configurar las credenciales en backend/.env:
echo    DB_DATABASE=lovecraftian_escape
echo    DB_USERNAME=root
echo    DB_PASSWORD=tu_password
echo.
echo 3. Ejecutar las migraciones:
echo    cd backend
echo    php artisan migrate:fresh --seed
echo.
echo 4. Iniciar los servidores con: start-servers.bat
echo.
pause
