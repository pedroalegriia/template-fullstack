<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Task Manager — {{ config('app.name') }}</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/scss/task-manager.scss', 'resources/js/taskManager.js'])
    @else
        <link rel="stylesheet" href="{{ asset('css/task-manager.css') }}">
        <script src="{{ asset('js/taskManager.js') }}" type="module"></script>
    @endif
</head>
<body>
    <div class="task-manager" id="task-manager-app">
        <header class="task-manager__header">
            <h1 class="task-manager__title">Gestión de tareas</h1>
            <p class="task-manager__subtitle">Arrastra las tarjetas entre columnas para cambiar el estado</p>
        </header>

        <div class="task-manager__message task-manager__message--hidden" id="message" role="alert" aria-live="polite"></div>

        <section class="task-form task-form--compact" aria-labelledby="form-title">
            <h2 id="form-title" class="visually-hidden">Formulario de tarea</h2>
            <form id="task-form" novalidate>
                <input type="hidden" id="task-id" name="id" value="">
                <div class="task-form__row">
                    <div class="task-form__group task-form__group--flex">
                        <label class="task-form__label" for="task-title">Título</label>
                        <input type="text" id="task-title" name="title" class="task-form__input" required maxlength="255" placeholder="Título de la tarea" autocomplete="off">
                    </div>
                    <div class="task-form__group task-form__group--flex task-form__group--narrow">
                        <label class="task-form__label" for="task-status">Estado</label>
                        <select id="task-status" name="status" class="task-form__select">
                            <option value="pendiente">Pendiente</option>
                            <option value="en_progreso">En progreso</option>
                            <option value="completada">Completada</option>
                        </select>
                    </div>
                </div>
                <div class="task-form__row">
                    <div class="task-form__group task-form__group--flex">
                        <label class="task-form__label" for="task-description">Descripción</label>
                        <textarea id="task-description" name="description" class="task-form__input task-form__textarea task-form__textarea--short" placeholder="Descripción (opcional)" rows="2"></textarea>
                    </div>
                    <div class="task-form__actions task-form__actions--inline">
                        <button type="submit" class="task-form__submit" id="submit-btn">Crear tarea</button>
                        <button type="button" class="task-form__cancel" id="cancel-edit-btn" style="display: none;">Cancelar</button>
                    </div>
                </div>
            </form>
        </section>

        <section class="kanban" aria-label="Tablero de tareas">
            <h2 class="visually-hidden">Tablero Kanban</h2>
            <div class="task-manager__loading" id="loading" style="display: none;">Cargando tareas…</div>
            <p class="task-manager__empty" id="empty" style="display: none;">No hay tareas. Crea una desde el formulario.</p>

            <div class="kanban__board" id="kanban-board" style="display: none;">
                <div class="kanban__column" data-status="pendiente" id="column-pendiente">
                    <div class="kanban__column-header kanban__column-header--pendiente">
                        <span class="kanban__column-title">Pendiente</span>
                        <span class="kanban__column-count" id="count-pendiente">0</span>
                    </div>
                    <div class="kanban__cards" id="cards-pendiente"></div>
                </div>
                <div class="kanban__column" data-status="en_progreso" id="column-en_progreso">
                    <div class="kanban__column-header kanban__column-header--en_progreso">
                        <span class="kanban__column-title">En progreso</span>
                        <span class="kanban__column-count" id="count-en_progreso">0</span>
                    </div>
                    <div class="kanban__cards" id="cards-en_progreso"></div>
                </div>
                <div class="kanban__column" data-status="completada" id="column-completada">
                    <div class="kanban__column-header kanban__column-header--completada">
                        <span class="kanban__column-title">Completada</span>
                        <span class="kanban__column-count" id="count-completada">0</span>
                    </div>
                    <div class="kanban__cards" id="cards-completada"></div>
                </div>
            </div>
        </section>
    </div>

    <script>
        window.API_BASE = '{{ url("/api") }}';
    </script>
</body>
</html>
