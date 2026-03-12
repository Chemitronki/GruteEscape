@echo off
echo ========================================
echo Lovecraftian Escape Room - Iniciando Servidores
echo ========================================
echo.

REM Check if backend and frontend directories exist
if not exist "backend" (
    echo ERROR: No se encuentra el directorio backend
    pause
    exit /b 1
)

if not exist "frontend" (
    echo ERROR: No se encuentra el directorio frontend
    pause
    exit /b 1
)

echo Iniciando servidor Laravel (Backend)...
echo Backend estara disponible en: http://localhost:8000
echo.

REM Start Laravel server in a new window
start "Laravel Backend" cmd /k "cd backend && php artisan serve"

REM Wait a moment for Laravel to start
timeout /t 3 /nobreak >nul

echo Iniciando servidor Vite (Frontend)...
echo Frontend estara disponible en: http://localhost:5173
echo.

REM Start Vite server in a new window
start "React Frontend" cmd /k "cd frontend && npm run dev"

echo.
echo ========================================
echo Servidores iniciados!
echo ========================================
echo.
echo Backend:  http://localhost:8000
echo Frontend: http://localhost:5173
echo.
echo Abre tu navegador en: http://localhost:5173
echo.
echo Para detener los servidores, cierra las ventanas de terminal
echo o presiona Ctrl+C en cada una.
echo.
echo Presiona cualquier tecla para abrir el navegador...
pause >nul

REM Open browser
start http://localhost:5173

echo.
echo Navegador abierto. Disfruta del juego!
echo.
