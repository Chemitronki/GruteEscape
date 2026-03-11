@echo off
echo ========================================
echo Lovecraftian Escape Room - Instalacion Completa
echo ========================================
echo.
echo Este script instalara y configurara todo el proyecto.
echo Asegurate de tener instalado:
echo - PHP 8.1+
echo - Composer
echo - Node.js y npm
echo - MySQL o PostgreSQL
echo.
pause

REM Configurar backend
echo.
echo ========================================
echo PASO 1: Configurando Backend Laravel
echo ========================================
call setup-backend.bat
if %errorlevel% neq 0 (
    echo Error en la configuracion del backend
    pause
    exit /b 1
)

REM Copiar migraciones al proyecto Laravel
echo.
echo Copiando migraciones a Laravel...
xcopy database-migrations\* backend\database\migrations\ /Y /I

REM Configurar frontend
echo.
echo ========================================
echo PASO 2: Configurando Frontend React
echo ========================================
call setup-frontend.bat
if %errorlevel% neq 0 (
    echo Error en la configuracion del frontend
    pause
    exit /b 1
)

REM Mensaje final
echo.
echo ========================================
echo INSTALACION COMPLETADA
echo ========================================
echo.
echo Proximos pasos:
echo.
echo 1. Configura tu base de datos:
echo    - Crea una base de datos llamada 'lovecraftian_escape'
echo    - Edita backend\.env con tus credenciales
echo.
echo 2. Ejecuta las migraciones:
echo    cd backend
echo    php artisan migrate
echo.
echo 3. Inicia los servidores:
echo    Terminal 1: cd backend ^&^& php artisan serve
echo    Terminal 2: cd frontend ^&^& npm run dev
echo.
echo 4. Visita http://localhost:5173 en tu navegador
echo.
pause
