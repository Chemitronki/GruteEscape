# Estructura del Proyecto - Lovecraftian Escape Room

## 📂 Archivos Creados

### Scripts de Instalación
- `install-all.bat` - Script maestro que ejecuta toda la instalación
- `setup-backend.bat` - Script para configurar el backend Laravel
- `setup-frontend.bat` - Script para configurar el frontend React

### Documentación
- `README.md` - Documentación principal del proyecto
- `SETUP_INSTRUCTIONS.md` - Instrucciones detalladas de instalación
- `PROJECT_STRUCTURE.md` - Este archivo

### Configuración General
- `.gitignore` - Archivos a ignorar en Git

### Backend - Archivos de Configuración (backend-config/)
- `cors.php` - Configuración de CORS para SPA
- `sanctum.php` - Configuración de Laravel Sanctum
- `.env.example` - Plantilla de variables de entorno
- `Pest.php` - Configuración de Pest para testing
- `phpunit.xml` - Configuración de PHPUnit

### Frontend - Archivos de Configuración (frontend-config/)
- `vite.config.js` - Configuración de Vite con proxy y testing
- `tailwind.config.js` - Configuración de Tailwind con tema lovecraftiano
- `package.json` - Dependencias y scripts del frontend
- `.env.example` - Variables de entorno del frontend
- `src/config/api.js` - Cliente Axios configurado
- `src/test/setup.js` - Setup para Vitest

### Migraciones de Base de Datos (database-migrations/)
1. `2024_01_01_000001_create_users_table.php`
2. `2024_01_01_000002_create_game_sessions_table.php`
3. `2024_01_01_000003_create_puzzles_table.php`
4. `2024_01_01_000004_create_puzzle_progress_table.php`
5. `2024_01_01_000005_create_hints_table.php`
6. `2024_01_01_000006_create_rankings_table.php`

## 🗄️ Esquema de Base de Datos

### Tabla: users
- `id` - Identificador único
- `username` - Nombre de usuario (único)
- `email` - Correo electrónico (único)
- `password` - Contraseña hasheada
- `created_at`, `updated_at` - Timestamps

**Índices**: email, username

### Tabla: game_sessions
- `id` - Identificador único
- `user_id` - FK a users
- `started_at` - Timestamp de inicio
- `completed_at` - Timestamp de completado (nullable)
- `time_remaining` - Segundos restantes (default: 1500)
- `status` - Estado: active, completed, abandoned, timeout
- `completion_time` - Tiempo total en segundos (nullable)
- `created_at`, `updated_at` - Timestamps

**Índices**: (user_id, status), status

### Tabla: puzzles
- `id` - Identificador único
- `type` - Tipo de puzzle (symbol_cipher, ritual_pattern, etc.)
- `sequence_order` - Orden de presentación (1-10)
- `title` - Título del puzzle
- `description` - Descripción del puzzle
- `solution_data` - Datos de solución en JSON
- `created_at`, `updated_at` - Timestamps

**Índices**: sequence_order

### Tabla: puzzle_progress
- `id` - Identificador único
- `game_session_id` - FK a game_sessions
- `puzzle_id` - FK a puzzles
- `started_at` - Timestamp de inicio
- `completed_at` - Timestamp de completado (nullable)
- `time_spent` - Segundos gastados en el puzzle
- `attempts` - Número de intentos
- `hints_used` - Número de pistas usadas
- `is_completed` - Boolean de completado
- `created_at`, `updated_at` - Timestamps

**Índices**: game_session_id
**Unique**: (game_session_id, puzzle_id)

### Tabla: hints
- `id` - Identificador único
- `puzzle_id` - FK a puzzles
- `level` - Nivel de pista (1, 2, 3)
- `content` - Contenido de la pista
- `created_at`, `updated_at` - Timestamps

**Unique**: (puzzle_id, level)

### Tabla: rankings
- `id` - Identificador único
- `user_id` - FK a users
- `completion_time` - Tiempo de completado en segundos
- `completed_at` - Timestamp de completado
- `created_at`, `updated_at` - Timestamps

**Índices**: completion_time
**Unique**: user_id

## 🔧 Configuración Requerida

### Backend (.env)
```env
APP_NAME="Lovecraftian Escape Room"
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
DB_DATABASE=lovecraftian_escape
DB_USERNAME=root
DB_PASSWORD=
SANCTUM_STATEFUL_DOMAINS=localhost:5173,localhost:3000
SESSION_DOMAIN=localhost
```

### Frontend (.env)
```env
VITE_API_BASE_URL=http://localhost:8000
VITE_API_TIMEOUT=10000
```

## 🎨 Tema Visual (Tailwind)

### Paleta de Colores

**Eldritch** (Azul grisáceo):
- 50-900: Tonos de azul grisáceo para elementos UI

**Cosmic** (Púrpura):
- 50-900: Tonos púrpura para elementos místicos

**Abyss** (Gris oscuro):
- 50-900: Tonos de gris para fondos y sombras

### Fuentes
- `font-lovecraft`: Cinzel (serif) - Para títulos
- `font-body`: Crimson Text (serif) - Para texto

### Animaciones
- `animate-pulse-slow`: Pulso lento (3s)
- `animate-float`: Flotación (6s)
- `animate-tentacle`: Movimiento de tentáculo (4s)

## 📦 Dependencias Principales

### Backend
- `laravel/framework: ^10.0` - Framework principal
- `laravel/sanctum` - Autenticación SPA
- `pestphp/pest` - Testing framework
- `pestphp/pest-plugin-laravel` - Plugin Laravel para Pest

### Frontend
- `react: ^18.2.0` - Framework UI
- `@reduxjs/toolkit: ^2.0.1` - State management
- `react-router-dom: ^6.21.0` - Routing
- `axios: ^1.6.2` - HTTP client
- `tailwindcss: ^3.4.0` - CSS framework
- `vitest: ^1.0.4` - Testing framework
- `fast-check: ^3.15.0` - Property-based testing

## 🧪 Configuración de Testing

### Backend (PHPUnit/Pest)
- Base de datos: SQLite en memoria
- Entorno: testing
- Bcrypt rounds: 4 (más rápido en tests)
- Cache/Queue: array driver

### Frontend (Vitest)
- Entorno: jsdom
- Globals: true
- Setup: src/test/setup.js
- Coverage: disponible con `npm run test:coverage`

## 🚀 Comandos Útiles

### Backend
```bash
cd backend
php artisan serve              # Iniciar servidor
php artisan migrate            # Ejecutar migraciones
php artisan migrate:fresh      # Recrear base de datos
php artisan test               # Ejecutar tests
./vendor/bin/pest              # Ejecutar tests con Pest
php artisan config:cache       # Cachear configuración
php artisan route:list         # Listar rutas
```

### Frontend
```bash
cd frontend
npm run dev                    # Servidor de desarrollo
npm run build                  # Build de producción
npm run preview                # Preview del build
npm run test                   # Ejecutar tests
npm run test:ui                # UI de tests
npm run lint                   # Linter
```

## 📋 Próximos Pasos de Implementación

### Tarea 2: Implementar Modelos y Controladores
- Crear modelos Eloquent
- Implementar controladores de API
- Crear servicios de lógica de negocio
- Implementar validadores de request

### Tarea 3: Implementar Sistema de Autenticación
- Endpoints de registro y login
- Middleware de autenticación
- Gestión de sesiones con Sanctum
- Rate limiting

### Tarea 4: Implementar Sistema de Juego
- Gestión de sesiones de juego
- Temporizador y sincronización
- Sistema de puzzles
- Sistema de pistas

### Tarea 5: Implementar Frontend
- Componentes de autenticación
- Componentes de juego
- Implementar 10 puzzles
- Sistema de routing
- Redux store

### Tarea 6: Implementar Sistema de Ranking
- Endpoint de ranking
- Actualización en tiempo real
- Componente de leaderboard

### Tarea 7: Testing
- Unit tests backend
- Property-based tests backend
- Unit tests frontend
- Property-based tests frontend
- Integration tests
- E2E tests

### Tarea 8: Multimedia y Pulido
- Cinemáticas
- Efectos de sonido
- Animaciones CSS/JS
- Optimización de assets
- Responsive design

## 🔐 Consideraciones de Seguridad

### Implementadas en Configuración
- CORS configurado para dominios específicos
- Sanctum con dominios stateful
- CSRF protection habilitado
- Credentials en requests (withCredentials: true)

### Por Implementar
- Rate limiting en rutas de autenticación
- Validación de inputs en todos los endpoints
- Sanitización de datos de usuario
- Hashing de contraseñas con bcrypt (cost 10+)
- Validación server-side de soluciones de puzzles
- Session timeout (2 horas)
- Logging de intentos de autenticación

## 📊 Métricas de Calidad

### Objetivos de Testing
- Cobertura backend: 80%+
- Cobertura frontend: 70%+
- Todas las propiedades de correctness testeadas
- Todos los endpoints con integration tests

### Performance
- Tiempo de carga inicial: <3s
- Respuesta de API: <200ms
- Sincronización de timer: cada 30s
- Bundle size frontend: <500KB (gzipped)

## 🎯 Estado Actual

✅ **Completado - Tarea 1: Setup project infrastructure**
- ✅ 1.1: Estructura de proyecto Laravel preparada
- ✅ 1.2: Estructura de proyecto React preparada
- ✅ 1.3: Migraciones de base de datos creadas
- ✅ 1.4: Frameworks de testing configurados

⏳ **Pendiente**: Instalación de dependencias (requiere Composer y npm)

## 📝 Notas Importantes

1. **Composer y npm requeridos**: Los scripts de instalación requieren que Composer y npm estén instalados y en el PATH del sistema.

2. **Base de datos**: Debes crear manualmente la base de datos `lovecraftian_escape` antes de ejecutar las migraciones.

3. **Variables de entorno**: Después de ejecutar los scripts, edita los archivos `.env` en backend y frontend con tus configuraciones específicas.

4. **Migraciones**: Las migraciones se copiarán automáticamente al directorio `backend/database/migrations/` durante la instalación.

5. **Configuración de CORS**: Ya está configurada para localhost:5173 (Vite) y localhost:3000 (alternativa).

6. **Testing**: Los frameworks de testing están configurados pero los tests específicos se implementarán en tareas posteriores.
