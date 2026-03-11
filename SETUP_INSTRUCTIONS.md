# Instrucciones de Configuración - Lovecraftian Escape Room

## Requisitos Previos

### 1. Instalar PHP 8.1+
- Descarga PHP desde: https://windows.php.net/download/
- Recomendado: Usar XAMPP (https://www.apachefriends.org/) que incluye PHP, MySQL y Apache
- Verifica la instalación: `php -v`

### 2. Instalar Composer
- Descarga desde: https://getcomposer.org/download/
- Ejecuta el instalador de Windows
- Verifica la instalación: `composer --version`

### 3. Instalar Node.js y npm
- Descarga desde: https://nodejs.org/ (versión LTS recomendada)
- Verifica la instalación: `node -v` y `npm -v`

### 4. Instalar MySQL o PostgreSQL
- MySQL: Incluido en XAMPP o descarga desde https://dev.mysql.com/downloads/mysql/
- PostgreSQL: https://www.postgresql.org/download/windows/

## Pasos de Configuración

### Paso 1: Crear proyecto Laravel (Backend)

```bash
# Crear proyecto Laravel 10.x
composer create-project laravel/laravel:^10.0 backend

# Navegar al directorio backend
cd backend

# Instalar Laravel Sanctum
composer require laravel/sanctum

# Publicar configuración de Sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# Instalar Pest para testing
composer require pestphp/pest --dev --with-all-dependencies
composer require pestphp/pest-plugin-laravel --dev

# Inicializar Pest
php artisan pest:install
```

### Paso 2: Configurar Base de Datos

1. Crea una base de datos MySQL/PostgreSQL llamada `lovecraftian_escape`

2. Edita el archivo `backend/.env`:

```env
APP_NAME="Lovecraftian Escape Room"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lovecraftian_escape
DB_USERNAME=root
DB_PASSWORD=

# Para PostgreSQL usa:
# DB_CONNECTION=pgsql
# DB_PORT=5432
```

3. Genera la clave de aplicación:

```bash
cd backend
php artisan key:generate
```

### Paso 3: Configurar CORS y Sanctum

Los archivos de configuración ya están creados. Solo necesitas ejecutar:

```bash
cd backend
php artisan config:cache
```

### Paso 4: Ejecutar Migraciones

```bash
cd backend
php artisan migrate
```

### Paso 5: Crear proyecto Frontend (React)

```bash
# Desde la raíz del proyecto
npm create vite@latest frontend -- --template react

# Navegar al directorio frontend
cd frontend

# Instalar dependencias
npm install

# Instalar dependencias adicionales
npm install @reduxjs/toolkit react-redux react-router-dom axios tailwindcss postcss autoprefixer fast-check vitest @testing-library/react @testing-library/jest-dom jsdom -D

# Inicializar Tailwind CSS
npx tailwindcss init -p
```

### Paso 6: Iniciar Servidores de Desarrollo

**Terminal 1 - Backend:**
```bash
cd backend
php artisan serve
# El backend estará disponible en http://localhost:8000
```

**Terminal 2 - Frontend:**
```bash
cd frontend
npm run dev
# El frontend estará disponible en http://localhost:5173
```

## Verificación

1. Backend: Visita http://localhost:8000/api/health (después de crear la ruta)
2. Frontend: Visita http://localhost:5173
3. Base de datos: Verifica que las tablas se crearon correctamente

## Próximos Pasos

Una vez completada la configuración inicial:
1. Implementar los modelos y controladores del backend
2. Crear los componentes del frontend
3. Implementar los 10 puzzles
4. Configurar el sistema de autenticación
5. Implementar el temporizador y sistema de juego

## Solución de Problemas

### Error: "composer: command not found"
- Reinicia tu terminal después de instalar Composer
- Verifica que Composer esté en tu PATH

### Error: "php: command not found"
- Agrega PHP a tu PATH de Windows
- O usa XAMPP y ejecuta desde su terminal

### Error de conexión a base de datos
- Verifica que MySQL/PostgreSQL esté ejecutándose
- Verifica las credenciales en el archivo .env
- Asegúrate de que la base de datos existe

### Puerto en uso
- Backend: Cambia el puerto con `php artisan serve --port=8001`
- Frontend: Cambia el puerto en vite.config.js
