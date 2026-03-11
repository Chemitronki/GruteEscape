# 🚀 Quick Start Guide

## Instalación Rápida (Windows)

### 1️⃣ Instalar Requisitos (si no los tienes)

```bash
# Descargar e instalar en este orden:
# 1. XAMPP (incluye PHP y MySQL): https://www.apachefriends.org/
# 2. Composer: https://getcomposer.org/download/
# 3. Node.js LTS: https://nodejs.org/
```

### 2️⃣ Verificar Instalación

```bash
php -v        # Debe mostrar PHP 8.1+
composer -V   # Debe mostrar Composer 2.x
node -v       # Debe mostrar Node 18+
npm -v        # Debe mostrar npm 9+
```

### 3️⃣ Ejecutar Instalación Automática

```bash
# Desde la raíz del proyecto
install-all.bat
```

### 4️⃣ Configurar Base de Datos

```bash
# Opción A: MySQL (XAMPP)
# 1. Inicia XAMPP y arranca MySQL
# 2. Abre phpMyAdmin: http://localhost/phpmyadmin
# 3. Crea base de datos: lovecraftian_escape

# Opción B: MySQL CLI
mysql -u root -p
CREATE DATABASE lovecraftian_escape CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;
```

### 5️⃣ Configurar Variables de Entorno

```bash
# Edita backend/.env
cd backend
notepad .env

# Cambia estas líneas:
DB_DATABASE=lovecraftian_escape
DB_USERNAME=root
DB_PASSWORD=tu_password_mysql
```

### 6️⃣ Ejecutar Migraciones y Seeders

```bash
cd backend
php artisan migrate --seed
```

### 7️⃣ Iniciar Servidores

```bash
# Terminal 1 - Backend
cd backend
php artisan serve

# Terminal 2 - Frontend
cd frontend
npm run dev
```

### 8️⃣ Abrir en Navegador

```
Frontend: http://localhost:5173
Backend API: http://localhost:8000/api/health
```

---

## 🔧 Comandos Útiles

### Backend (Laravel)

```bash
cd backend

# Servidor de desarrollo
php artisan serve
php artisan serve --port=8001  # Puerto alternativo

# Base de datos
php artisan migrate              # Ejecutar migraciones
php artisan migrate:fresh        # Recrear BD
php artisan migrate:fresh --seed # Recrear BD con datos
php artisan db:seed              # Solo seeders

# Testing
php artisan test                 # Todos los tests
php artisan test --filter=AuthTest  # Test específico
./vendor/bin/pest                # Con Pest
./vendor/bin/pest --coverage     # Con cobertura

# Caché
php artisan config:cache         # Cachear config
php artisan config:clear         # Limpiar cache config
php artisan route:cache          # Cachear rutas
php artisan route:clear          # Limpiar cache rutas

# Información
php artisan route:list           # Listar rutas
php artisan tinker               # REPL interactivo
```

### Frontend (React)

```bash
cd frontend

# Desarrollo
npm run dev                      # Servidor desarrollo
npm run dev -- --port 3000       # Puerto alternativo

# Build
npm run build                    # Build producción
npm run preview                  # Preview del build

# Testing
npm run test                     # Tests en watch mode
npm run test:run                 # Tests una vez
npm run test:ui                  # UI de tests
npm run test:coverage            # Con cobertura

# Linting
npm run lint                     # Ejecutar linter
```

---

## 🐛 Solución de Problemas Comunes

### ❌ "composer: command not found"
```bash
# Solución: Reinicia tu terminal o agrega Composer al PATH
# Windows: Busca "Variables de entorno" y agrega:
# C:\ProgramData\ComposerSetup\bin
```

### ❌ "php: command not found"
```bash
# Solución: Agrega PHP al PATH
# Windows con XAMPP: Agrega a PATH:
# C:\xampp\php
```

### ❌ Error de conexión a base de datos
```bash
# 1. Verifica que MySQL esté corriendo (XAMPP Control Panel)
# 2. Verifica credenciales en backend/.env
# 3. Verifica que la BD existe:
mysql -u root -p
SHOW DATABASES;
```

### ❌ "SQLSTATE[HY000] [2002] No connection could be made"
```bash
# Solución: Inicia MySQL en XAMPP
# O cambia DB_HOST en .env a 127.0.0.1
```

### ❌ Puerto 8000 en uso
```bash
# Solución: Usa otro puerto
php artisan serve --port=8001

# Y actualiza frontend/.env:
VITE_API_BASE_URL=http://localhost:8001
```

### ❌ Puerto 5173 en uso
```bash
# Solución: Edita frontend/vite.config.js
# Cambia: server: { port: 3000 }
```

### ❌ CORS errors en el navegador
```bash
# 1. Verifica backend/config/cors.php incluye tu dominio
# 2. Verifica SANCTUM_STATEFUL_DOMAINS en backend/.env
# 3. Limpia cache: php artisan config:clear
```

### ❌ "npm ERR! code ENOENT"
```bash
# Solución: Verifica que estás en el directorio correcto
cd frontend
npm install
```

---

## 📋 Checklist de Verificación

Antes de comenzar el desarrollo, verifica:

- [ ] PHP 8.1+ instalado y en PATH
- [ ] Composer instalado y en PATH
- [ ] Node.js 18+ y npm instalados
- [ ] MySQL/PostgreSQL instalado y corriendo
- [ ] Base de datos `lovecraftian_escape` creada
- [ ] `backend/.env` configurado con credenciales correctas
- [ ] Migraciones ejecutadas: `php artisan migrate --seed`
- [ ] Backend corriendo: http://localhost:8000/api/health
- [ ] Frontend corriendo: http://localhost:5173
- [ ] No hay errores en la consola del navegador

---

## 🎯 Próximos Pasos de Desarrollo

Una vez que todo esté funcionando:

1. **Tarea 2**: Implementar modelos y controladores
   - Crear modelos Eloquent
   - Crear controladores de API
   - Implementar servicios de negocio

2. **Tarea 3**: Sistema de autenticación
   - Endpoints de registro/login
   - Middleware de autenticación
   - Gestión de tokens

3. **Tarea 4**: Lógica de juego
   - Gestión de sesiones
   - Validación de puzzles
   - Sistema de pistas
   - Temporizador

4. **Tarea 5**: Componentes React
   - Páginas de autenticación
   - Componentes de juego
   - Implementar 10 puzzles
   - Sistema de routing

5. **Tarea 6**: Sistema de ranking
   - Endpoint de ranking
   - Componente de leaderboard
   - Actualización en tiempo real

6. **Tarea 7**: Testing completo
   - Unit tests
   - Property-based tests
   - Integration tests
   - E2E tests

7. **Tarea 8**: Pulido final
   - Cinemáticas
   - Efectos de sonido
   - Animaciones
   - Optimización

---

## 📚 Recursos Adicionales

- [README.md](README.md) - Documentación completa
- [SETUP_INSTRUCTIONS.md](SETUP_INSTRUCTIONS.md) - Instrucciones detalladas
- [PROJECT_STRUCTURE.md](PROJECT_STRUCTURE.md) - Arquitectura del proyecto
- [TASK_1_COMPLETED.md](TASK_1_COMPLETED.md) - Resumen de la tarea 1

---

**¿Listo para comenzar? ¡Que los Antiguos te guíen! 🐙**
