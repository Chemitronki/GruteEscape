@echo off
echo ========================================
echo Configurando Backend Laravel
echo ========================================

REM Crear proyecto Laravel
echo.
echo [1/8] Creando proyecto Laravel 10.x...
composer create-project laravel/laravel:^10.0 backend
if %errorlevel% neq 0 (
    echo Error: No se pudo crear el proyecto Laravel
    pause
    exit /b 1
)

cd backend

REM Instalar Laravel Sanctum
echo.
echo [2/8] Instalando Laravel Sanctum...
composer require laravel/sanctum
if %errorlevel% neq 0 (
    echo Error: No se pudo instalar Sanctum
    pause
    exit /b 1
)

REM Publicar configuración de Sanctum
echo.
echo [3/8] Publicando configuración de Sanctum...
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

REM Instalar Pest
echo.
echo [4/8] Instalando Pest para testing...
composer require pestphp/pest --dev --with-all-dependencies
composer require pestphp/pest-plugin-laravel --dev

REM Inicializar Pest
echo.
echo [5/8] Inicializando Pest...
php artisan pest:install

REM Generar clave de aplicación
echo.
echo [6/8] Generando clave de aplicación...
php artisan key:generate

REM Copiar archivos de configuración
echo.
echo [7/8] Copiando archivos de configuración...
copy ..\backend-config\* config\ /Y

REM Mensaje final
echo.
echo [8/8] ¡Backend configurado exitosamente!
echo.
echo Próximos pasos:
echo 1. Edita el archivo .env con tus credenciales de base de datos
echo 2. Ejecuta: php artisan migrate
echo 3. Ejecuta: php artisan serve
echo.
pause
