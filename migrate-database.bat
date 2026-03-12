@echo off
echo ========================================
echo Lovecraftian Escape Room - Migraciones
echo ========================================
echo.

if not exist "backend" (
    echo ERROR: No se encuentra el directorio backend
    pause
    exit /b 1
)

cd backend

echo Ejecutando migraciones y seeders...
echo (Esto creara todas las tablas y datos iniciales)
echo.

call php artisan migrate:fresh --seed

if errorlevel 1 (
    echo.
    echo ERROR: Fallo la migracion
    echo.
    echo Verifica que:
    echo 1. MySQL este ejecutandose
    echo 2. La base de datos 'lovecraftian_escape' exista
    echo 3. Las credenciales en .env sean correctas
    echo.
    cd ..
    pause
    exit /b 1
)

echo.
echo ========================================
echo Migraciones completadas exitosamente!
echo ========================================
echo.
echo Se han creado:
echo - Tabla de usuarios
echo - Tabla de sesiones de juego
echo - Tabla de puzzles (10 puzzles)
echo - Tabla de progreso de puzzles
echo - Tabla de pistas (30 pistas, 3 por puzzle)
echo - Tabla de rankings
echo.
echo Ya puedes iniciar los servidores con: start-servers.bat
echo.

cd ..
pause
