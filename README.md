# Prueba Técnica - Full Stack Developer

Gracias por participar en este proceso técnico.

El objetivo de esta prueba es evaluar tus habilidades en desarrollo Full Stack utilizando:

- JavaScript
- HTML
- CSS
- SASS
- Laravel
- MySQL
- Consumo y construcción de APIs

---

# Objetivo

Desarrollar una pequeña aplicación de gestión de tareas (Task Manager) que incluya:

- Backend en Laravel (API REST)
- Base de datos MySQL
- Frontend con HTML + CSS + SASS + JavaScript
- Consumo de API desde el frontend

---

# Requerimientos

## Backend (Laravel)

Construir una API REST que permita:

- Crear una tarea
- Listar tareas
- Actualizar una tarea
- Eliminar una tarea

Cada tarea debe contener al menos:

- id
- title
- description
- status (pendiente, en progreso, completada)
- timestamps

### Requisitos técnicos:

- Crear migraciones
- Crear modelo
- Crear controlador
- Validaciones
- Seeders
- Manejo adecuado de errores
- Respuestas JSON estructuradas

---

## Base de Datos

- Usar MySQL
- Diseñar correctamente la estructura
- Usar migraciones

---

## Frontend

Crear una interfaz que permita:

- Crear tareas
- Listarlas
- Editarlas
- Eliminarlas

### Requisitos:

- HTML semántico
- CSS organizado
- Uso de SASS
- JavaScript limpio y estructurado
- Consumo de la API mediante fetch o axios
- Manejo de estados y errores

No se requiere framework frontend (React, Vue, etc.).

---

# Instalación

## Requisitos previos

- PHP 8.2+
- Composer
- Node.js 18.x (recomendado 18.x; el proyecto usa Vite 5 y Tailwind v3 compatibles con Node 18)
- npm (incluido con Node.js)
- MySQL 8.x (o MariaDB compatible)

### Importante para `npm run build`

El frontend está configurado para **Node.js 18**. Si al ejecutar `npm run build` obtienes:

- **"Vite requires Node.js version 20.19+"** o **"Cannot find native binding"** (por @tailwindcss/oxide): el proyecto ya está ajustado para Node 18 (Vite 5, Tailwind v3). Asegúrate de usar Node 18 y reinstala dependencias:

```bash
rm -rf node_modules package-lock.json
npm install
npm run build
```

- Si **npm install** falla o el build sigue fallando: verifica tu versión de Node con `node -v` (debe ser v18.x). Si tienes Node 20+, no es necesario cambiar; si tienes Node 18 y falla, el borrado de `node_modules` y `package-lock.json` suele resolver conflictos de dependencias nativas.

## Pasos para su instalación

```bash
# Clonar e instalar dependencias PHP
git clone <repo>
cd fullstack-test-main
composer install

# Configuración de entorno
cp .env.example .env
php artisan key:generate

# Configurar base de datos en .env (MySQL)
# Descomentar y ajustar DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD
# Si usas `php artisan serve`, puedes setear APP_URL=http://localhost:8000
# Crear la base en MySQL (ej: CREATE DATABASE fullstack_test;) y luego:
php artisan migrate
php artisan db:seed

# Dependencias frontend y compilación de assets
npm install
npm run build
```

## Ejecutar la aplicación

```bash
php artisan serve
```

Abre `http://localhost:8000`. Para desarrollo con recarga de CSS/JS, ejecuta además `npm run dev` en otra terminal.

---

# Notas técnicas

## Stack

- **Backend:** Laravel 12, API en `routes/api.php`, controlador `App\Http\Controllers\Api\TaskController`.
- **Base de datos:** MySQL; migración `*_create_tasks_table.php`; modelo `Task` con `id`, `title`, `description`, `status` (pendiente, en_progreso, completada), timestamps.
- **Frontend:** Vista `resources/views/tasks.blade.php` (HTML semántico); SASS en `resources/scss/`; lógica en `resources/js/taskManager.js` (fetch, estado, errores). Build: Vite 5, Tailwind CSS v3, PostCSS, SASS (compatible con Node 18).

## API REST

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | /api/tasks | Listar tareas |
| POST | /api/tasks | Crear tarea |
| GET | /api/tasks/{id} | Ver una tarea |
| PUT | /api/tasks/{id} | Actualizar |
| DELETE | /api/tasks/{id} | Eliminar |

Respuestas JSON: `{ "success": true|false, "data": ..., "message": "...", "errors": ... }`. Validación 422 con `errors` por campo.

## Estructura relevante

- `app/Models/Task.php`, `app/Http/Controllers/Api/TaskController.php`
- `database/seeders/TaskSeeder.php` (llamado desde `DatabaseSeeder`)
- `resources/views/tasks.blade.php`, `resources/scss/`, `resources/js/taskManager.js`

Código organizado por capas; validaciones con mensajes en español; frontend sin framework, con manejo explícito de estados y errores.
