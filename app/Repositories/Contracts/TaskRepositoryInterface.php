<?php

namespace App\Repositories\Contracts;

use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

interface TaskRepositoryInterface
{
    /**
     * Obtener todas las tareas ordenadas por fecha de creación descendente.
     *
     * @return Collection<int, Task>
     */
    public function getAllOrderedByCreatedDesc(): Collection;

    /**
     * Crear una nueva tarea.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Task;

    /**
     * Actualizar una tarea existente.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function update(Task $task, array $attributes): Task;

    /**
     * Eliminar una tarea.
     */
    public function delete(Task $task): bool;
}
