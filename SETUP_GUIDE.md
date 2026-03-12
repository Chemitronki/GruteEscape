# Guía de Instalación - Lovecraftian Escape Room

## Requisitos Previos

- PHP 8.1 o superior
- Composer
- Node.js 18 o superior
- MySQL o MariaDB
- Laragon (recomendado para Windows)

## Instalación Paso a Paso

### 1. Configurar Base de Datos

Crea una base de datos en MySQL:
```sql
CREATE DATABASE lovecraftian_escape CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Configurar Backend (Laravel)

```bash
# Navegar al directorio backend
cd backend

# Copiar archivo de configuración
copy .env.example .env

# Instalar dependencias de Composer
composer install

# Generar clave de aplicación
php artisan key:generate

# Configurar base de datos en .env
# Edita el archivo .env y configura:
# DB_DATABASE=lovecraftian_escape
# DB_USERNAME=root
# DB_PASSWORD=

# Ejecutar migraciones y seeders
php artisan migrate:fresh --seed

# Iniciar servidor Laravel
php artisan serve
```

El backend estará disponible en: http://localhost:8000

### 3. Configurar Frontend (React)

Abre una nueva terminal:

```bash
# Navegar al directorio frontend
cd frontend

# Copiar archivo de configuración
copy .env.example .env

# Instalar dependencias de npm
npm install

# Iniciar servidor de desarrollo
npm run dev
```

El frontend estará disponible en: http://localhost:5173

### 4. Acceder a la Aplicación

1. Abre tu navegador en: http://localhost:5173
2. Regístrate con un nuevo usuario
3. ¡Comienza a jugar!

## Estructura del Proyecto

```
lovecraftian-escape-room/
├── backend/              # Laravel API
│   ├── app/
│   ├── database/
│   └── routes/
├── frontend/             # React SPA
│   ├── src/
│   │   ├── components/
│   │   ├── features/
│   │   └── pages/
│   └── public/
└── database-migrations/  # Migraciones adicionales
```

## Solución de Problemas

### Error de conexión a la base de datos
- Verifica que MySQL esté ejecutándose
- Confirma las credenciales en backend/.env
- Asegúrate de que la base de datos existe

### Error de CORS
- Verifica que FRONTEND_URL en backend/.env sea http://localhost:5173
- Confirma que VITE_API_URL en frontend/.env sea http://localhost:8000/api

### Errores de Composer
```bash
composer update
composer dump-autoload
```

### Errores de npm
```bash
npm cache clean --force
rm -rf node_modules package-lock.json
npm install
```

## Características del Juego

- **Timer**: 25 minutos para completar todos los puzzles
- **Puzzles**: 10 puzzles únicos con temática lovecraftiana
- **Pistas**: 3 pistas por puzzle (disponibles después de 2 minutos)
- **Ranking**: Tabla de clasificación global con los mejores tiempos
- **Tema**: Diseño oscuro lovecraftiano con efectos visuales y sonoros

## Comandos Útiles

### Backend
```bash
# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Recrear base de datos
php artisan migrate:fresh --seed

# Ver rutas
php artisan route:list
```

### Frontend
```bash
# Build para producción
npm run build

# Preview de producción
npm run preview

# Linting
npm run lint
```

## Notas de Desarrollo

- El proyecto usa Laravel Sanctum para autenticación
- Redux Toolkit para gestión de estado en frontend
- Todos los puzzles se validan en el servidor
- El timer se sincroniza cada 30 segundos con el backend
- Las sesiones se guardan en localStorage para recuperación

¡Disfruta del juego!
