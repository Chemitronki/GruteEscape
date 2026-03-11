# Guía de Instalación de Requisitos para Windows

## Opción 1: XAMPP (Recomendado - Más Fácil)

XAMPP incluye PHP, MySQL, Apache y todo lo necesario en un solo paquete.

### Pasos:

1. **Descargar XAMPP:**
   - Ve a: https://www.apachefriends.org/download.html
   - Descarga XAMPP para Windows (versión 8.2.x)

2. **Instalar XAMPP:**
   - Ejecuta el instalador
   - Instala en `C:\xampp` (ruta por defecto)
   - Selecciona: Apache, MySQL, PHP, phpMyAdmin

3. **Agregar PHP al PATH:**
   - Abre "Variables de entorno" en Windows
   - Edita la variable PATH del sistema
   - Agrega: `C:\xampp\php`
   - Reinicia la terminal

4. **Instalar Composer:**
   - Ve a: https://getcomposer.org/download/
   - Descarga y ejecuta `Composer-Setup.exe`
   - El instalador detectará automáticamente PHP de XAMPP

5. **Verificar instalación:**
   ```bash
   php --version
   composer --version
   ```

6. **Iniciar servicios:**
   - Abre XAMPP Control Panel
   - Inicia Apache y MySQL

---

## Opción 2: Laragon (Alternativa Moderna)

Laragon es más moderno y fácil de usar que XAMPP.

### Pasos:

1. **Descargar Laragon:**
   - Ve a: https://laragon.org/download/
   - Descarga Laragon Full (incluye PHP, MySQL, Node.js)

2. **Instalar Laragon:**
   - Ejecuta el instalador
   - Laragon configura automáticamente todo (PHP, MySQL, Composer, Node.js)

3. **Verificar instalación:**
   - Abre Laragon
   - Click derecho en la ventana → Terminal
   - Ejecuta:
   ```bash
   php --version
   composer --version
   node --version
   ```

4. **Ventajas de Laragon:**
   - Configuración automática del PATH
   - Incluye Composer pre-instalado
   - Incluye Node.js y npm
   - Gestión fácil de proyectos Laravel
   - Virtual hosts automáticos

---

## Opción 3: Instalación Manual (Avanzado)

Si prefieres instalar manualmente, ejecuta el script que creé:

```bash
install-php-composer.bat
```

**IMPORTANTE:** Debes ejecutarlo como Administrador:
- Click derecho en el archivo
- "Ejecutar como administrador"
- Después de la instalación, cierra y vuelve a abrir la terminal

---

## Después de Instalar

Una vez tengas PHP y Composer instalados:

1. **Verifica las versiones:**
   ```bash
   php --version    # Debe ser 8.1 o superior
   composer --version
   node --version   # Debe ser 16 o superior
   npm --version
   ```

2. **Instala el proyecto:**
   ```bash
   # Opción A: Instalación automática
   install-all.bat

   # Opción B: Instalación manual
   setup-backend.bat
   setup-frontend.bat
   ```

3. **Configura la base de datos:**
   - Abre phpMyAdmin (http://localhost/phpmyadmin)
   - Crea una base de datos llamada: `lovecraftian_escape`
   - Usuario: `root`
   - Contraseña: (vacía por defecto en XAMPP/Laragon)

4. **Configura el backend:**
   - Copia `backend/.env.example` a `backend/.env`
   - Edita `backend/.env`:
   ```
   DB_DATABASE=lovecraftian_escape
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Ejecuta las migraciones:**
   ```bash
   cd backend
   php artisan migrate --seed
   ```

6. **Inicia los servidores:**
   
   Terminal 1 (Backend):
   ```bash
   cd backend
   php artisan serve
   ```
   
   Terminal 2 (Frontend):
   ```bash
   cd frontend
   npm run dev
   ```

7. **Accede a la aplicación:**
   - Frontend: http://localhost:5173
   - Backend API: http://localhost:8000

---

## Solución de Problemas

### "composer no se reconoce como comando"
- Cierra y vuelve a abrir la terminal
- Verifica que Composer esté en el PATH
- Reinicia el PC si es necesario

### "php no se reconoce como comando"
- Verifica que PHP esté en el PATH
- En XAMPP: Agrega `C:\xampp\php` al PATH
- En Laragon: Usa la terminal integrada de Laragon

### Error de extensiones PHP
- Edita `php.ini`
- Descomenta (quita el `;`) de estas líneas:
  - `extension=curl`
  - `extension=fileinfo`
  - `extension=mbstring`
  - `extension=openssl`
  - `extension=pdo_mysql`
  - `extension=zip`

### Puerto 8000 o 5173 ocupado
- Backend: `php artisan serve --port=8001`
- Frontend: Edita `vite.config.js` y cambia el puerto

---

## Recomendación Final

**Para principiantes:** Usa XAMPP o Laragon (Opción 1 o 2)
**Para desarrolladores:** Instalación manual (Opción 3)

¡Una vez instalado todo, estarás listo para desarrollar el escape room lovecraftiano! 🐙
