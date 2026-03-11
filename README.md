# Lovecraftian Escape Room 🐙

Una aplicación web de escape room con temática lovecraftiana. Los jugadores deben resolver 10 puzzles únicos dentro de un límite de tiempo de 25 minutos, ambientados en una gruta oscura llena de monstruos y terror cósmico.

## 🎮 Características

- **10 Puzzles Únicos**: Desde cifrados de símbolos hasta laberintos de tentáculos
- **Temporizador de 25 Minutos**: Cuenta regresiva en tiempo real
- **Sistema de Pistas**: Hasta 3 pistas progresivas por puzzle
- **Ranking Global**: Compite con otros jugadores por el mejor tiempo
- **Cinemáticas**: Secuencias narrativas inmersivas
- **Diseño Responsive**: Juega en desktop, tablet o móvil
- **Autenticación Segura**: Sistema de registro y login con Laravel Sanctum

## 🛠️ Stack Tecnológico

### Backend
- **Framework**: Laravel 10.x
- **Lenguaje**: PHP 8.1+
- **Base de Datos**: MySQL 8.0 / PostgreSQL 14+
- **Autenticación**: Laravel Sanctum
- **Testing**: PHPUnit + Pest

### Frontend
- **Framework**: React 18
- **Estado**: Redux Toolkit
- **Routing**: React Router
- **Estilos**: Tailwind CSS
- **Build**: Vite
- **Testing**: Vitest + fast-check

## 📋 Requisitos Previos

Antes de comenzar, asegúrate de tener instalado:

1. **PHP 8.1 o superior**
   - Descarga: https://windows.php.net/download/
   - O usa XAMPP: https://www.apachefriends.org/

2. **Composer**
   - Descarga: https://getcomposer.org/download/

3. **Node.js 18+ y npm**
   - Descarga: https://nodejs.org/

4. **MySQL 8.0 o PostgreSQL 14+**
   - MySQL: https://dev.mysql.com/downloads/mysql/
   - PostgreSQL: https://www.postgresql.org/download/

## 🚀 Instalación Rápida

### Opción 1: Script Automático (Windows)

```bash
# Ejecuta el script de instalación completo
install-all.bat
```

### Opción 2: Instalación Manual

#### 1. Configurar Backend

```bash
# Crear proyecto Laravel
composer create-project laravel/laravel:^10.0 backend
cd backend

# Instalar dependencias
composer require laravel/sanctum
composer require pestphp/pest --dev --with-all-dependencies
composer require pestphp/pest-plugin-laravel --dev

# Publicar configuración de Sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# Inicializar Pest
php artisan pest:install

# Copiar archivos de configuración
copy ..\backend-config\* config\

# Copiar migraciones
copy ..\database-migrations\* database\migrations\

# Configurar .env
copy .env.example .env
# Edita .env con tus credenciales de base de datos

# Generar clave de aplicación
php artisan key:generate

# Ejecutar migraciones
php artisan migrate
```

#### 2. Configurar Frontend

```bash
# Crear proyecto React con Vite
npm create vite@latest frontend -- --template react
cd frontend

# Instalar dependencias
npm install

# Instalar dependencias adicionales
npm install @reduxjs/toolkit react-redux react-router-dom axios
npm install -D tailwindcss postcss autoprefixer
npm install -D vitest @testing-library/react @testing-library/jest-dom jsdom fast-check @vitest/ui

# Inicializar Tailwind
npx tailwindcss init -p

# Copiar archivos de configuración
copy ..\frontend-config\* .
copy ..\frontend-config\src\* src\ /E

# Configurar variables de entorno
copy .env.example .env
```

## 🎯 Uso

### Iniciar Servidores de Desarrollo

**Terminal 1 - Backend:**
```bash
cd backend
php artisan serve
# Backend disponible en http://localhost:8000
```

**Terminal 2 - Frontend:**
```bash
cd frontend
npm run dev
# Frontend disponible en http://localhost:5173
```

### Ejecutar Tests

**Backend:**
```bash
cd backend
php artisan test
# O con Pest
./vendor/bin/pest
```

**Frontend:**
```bash
cd frontend
npm run test
# O con UI
npm run test:ui
```

## 📁 Estructura del Proyecto

```
lovecraftian-escape-room/
├── backend/                    # Laravel backend
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/   # API controllers
│   │   │   └── Middleware/    # Custom middleware
│   │   ├── Models/            # Eloquent models
│   │   └── Services/          # Business logic
│   ├── database/
│   │   ├── migrations/        # Database migrations
│   │   └── seeders/           # Database seeders
│   ├── routes/
│   │   └── api.php           # API routes
│   └── tests/                # PHPUnit/Pest tests
│
├── frontend/                  # React frontend
│   ├── src/
│   │   ├── components/       # React components
│   │   ├── features/         # Redux slices
│   │   ├── pages/            # Page components
│   │   ├── config/           # Configuration files
│   │   └── test/             # Test utilities
│   └── public/               # Static assets
│
├── database-migrations/       # Migration templates
├── backend-config/           # Backend config templates
├── frontend-config/          # Frontend config templates
├── SETUP_INSTRUCTIONS.md     # Detailed setup guide
└── README.md                 # This file
```

## 🎨 Puzzles Implementados

1. **Symbol Cipher**: Decodifica símbolos lovecraftianos
2. **Ritual Pattern**: Ordena items rituales en secuencia
3. **Ancient Lock**: Resuelve combinación basada en pistas
4. **Memory Fragments**: Empareja imágenes eldritchas
5. **Cosmic Alignment**: Alinea cuerpos celestes
6. **Tentacle Maze**: Navega un laberinto cambiante
7. **Forbidden Tome**: Reconstruye páginas antiguas
8. **Shadow Reflection**: Refleja patrones de sombras
9. **Cultist Code**: Decodifica mensajes interceptados
10. **Elder Sign Drawing**: Traza patrones geométricos complejos

## 🔒 Seguridad

- Contraseñas hasheadas con bcrypt (cost factor 10+)
- HTTPS en producción
- Protección CSRF
- Sanitización de inputs (prevención XSS)
- Queries parametrizadas (prevención SQL injection)
- Rate limiting en endpoints de autenticación
- Validación server-side de todas las acciones del juego
- Session timeout después de 2 horas de inactividad

## 🧪 Testing

El proyecto implementa una estrategia dual de testing:

- **Unit Tests**: Casos específicos y edge cases
- **Property-Based Tests**: Propiedades universales con fast-check/Pest
- **Integration Tests**: Flujos completos de API
- **E2E Tests**: Journeys de usuario completos

Objetivo de cobertura:
- Backend: 80%+
- Frontend: 70%+

## 📝 Configuración de Base de Datos

### MySQL

```sql
CREATE DATABASE lovecraftian_escape CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Edita `backend/.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lovecraftian_escape
DB_USERNAME=root
DB_PASSWORD=tu_password
```

### PostgreSQL

```sql
CREATE DATABASE lovecraftian_escape;
```

Edita `backend/.env`:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=lovecraftian_escape
DB_USERNAME=postgres
DB_PASSWORD=tu_password
```

## 🐛 Solución de Problemas

### "composer: command not found"
- Reinicia tu terminal después de instalar Composer
- Verifica que Composer esté en tu PATH

### "php: command not found"
- Agrega PHP a tu PATH de Windows
- O usa XAMPP y ejecuta desde su terminal

### Error de conexión a base de datos
- Verifica que MySQL/PostgreSQL esté ejecutándose
- Verifica las credenciales en `.env`
- Asegúrate de que la base de datos existe

### Puerto en uso
- Backend: `php artisan serve --port=8001`
- Frontend: Cambia el puerto en `vite.config.js`

### CORS errors
- Verifica que `SANCTUM_STATEFUL_DOMAINS` en `.env` incluya tu dominio frontend
- Verifica que `withCredentials: true` esté configurado en axios

## 📚 Documentación Adicional

- [Especificación de Requisitos](.kiro/specs/lovecraftian-escape-room/requirements.md)
- [Documento de Diseño](.kiro/specs/lovecraftian-escape-room/design.md)
- [Lista de Tareas](.kiro/specs/lovecraftian-escape-room/tasks.md)
- [Instrucciones de Setup](SETUP_INSTRUCTIONS.md)

## 🤝 Contribución

Este proyecto es parte de un spec de desarrollo. Para contribuir:

1. Lee la documentación en `.kiro/specs/lovecraftian-escape-room/`
2. Sigue las convenciones de código establecidas
3. Escribe tests para nuevas funcionalidades
4. Asegúrate de que todos los tests pasen

## 📄 Licencia

Este proyecto es de código abierto y está disponible bajo la licencia MIT.

## 🎭 Créditos

Inspirado en las obras de H.P. Lovecraft y el género de terror cósmico.

---

**¡Que los Antiguos te guíen en tu escape! 🐙👁️**
