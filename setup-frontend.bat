@echo off
echo ========================================
echo Configurando Frontend React
echo ========================================

REM Crear proyecto Vite con React
echo.
echo [1/6] Creando proyecto Vite con React 18...
call npm create vite@latest frontend -- --template react
if %errorlevel% neq 0 (
    echo Error: No se pudo crear el proyecto Vite
    pause
    exit /b 1
)

cd frontend

REM Instalar dependencias base
echo.
echo [2/6] Instalando dependencias base...
call npm install
if %errorlevel% neq 0 (
    echo Error: No se pudieron instalar las dependencias
    pause
    exit /b 1
)

REM Instalar dependencias adicionales
echo.
echo [3/6] Instalando Redux Toolkit, React Router, Axios...
call npm install @reduxjs/toolkit react-redux react-router-dom axios
if %errorlevel% neq 0 (
    echo Error: No se pudieron instalar las dependencias adicionales
    pause
    exit /b 1
)

REM Instalar Tailwind CSS
echo.
echo [4/6] Instalando Tailwind CSS...
call npm install -D tailwindcss postcss autoprefixer
call npx tailwindcss init -p
if %errorlevel% neq 0 (
    echo Error: No se pudo instalar Tailwind CSS
    pause
    exit /b 1
)

REM Instalar herramientas de testing
echo.
echo [5/6] Instalando herramientas de testing...
call npm install -D vitest @testing-library/react @testing-library/jest-dom jsdom fast-check @vitest/ui
if %errorlevel% neq 0 (
    echo Error: No se pudieron instalar las herramientas de testing
    pause
    exit /b 1
)

REM Copiar archivos de configuración
echo.
echo [6/6] Copiando archivos de configuración...
copy ..\frontend-config\* . /Y

REM Mensaje final
echo.
echo ¡Frontend configurado exitosamente!
echo.
echo Próximos pasos:
echo 1. Ejecuta: npm run dev
echo 2. Visita: http://localhost:5173
echo.
pause
