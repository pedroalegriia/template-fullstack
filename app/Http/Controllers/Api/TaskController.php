<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class TaskController extends Controller
{
    public function __construct(
        protected TaskRepositoryInterface $taskRepository
    ) {}

    /**
     * Listar todas las tareas.
     */
    public function index(): JsonResponse
    {
        try {
            $tasks = $this->taskRepository->getAllOrderedByCreatedDesc();

            return ApiResponse::success($tasks);
        } catch (Throwable $e) {
            return ApiResponse::handleException($e, 'TaskController@index');
        }
    }

    /**
     * Crear una tarea.
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        try {
            $task = DB::transaction(function () use ($request) {
                return $this->taskRepository->create($request->validated());
            });

            return ApiResponse::created($task, 'Tarea creada correctamente.');
        } catch (Throwable $e) {
            return ApiResponse::handleException($e, 'TaskController@store');
        }
    }

    /**
     * Mostrar una tarea.
     */
    public function show(Task $task): JsonResponse
    {
        try {
            return ApiResponse::success($task);
        } catch (Throwable $e) {
            return ApiResponse::handleException($e, 'TaskController@show');
        }
    }

    /**
     * Actualizar una tarea.
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        try {
            $task = DB::transaction(function () use ($request, $task) {
                return $this->taskRepository->update($task, $request->validated());
            });

            return ApiResponse::success($task, 'Tarea actualizada correctamente.');
        } catch (Throwable $e) {
            return ApiResponse::handleException($e, 'TaskController@update');
        }
    }

    /**
     * Eliminar una tarea.
     */
    public function destroy(Task $task): JsonResponse
    {
        try {
            DB::transaction(function () use ($task) {
                $this->taskRepository->delete($task);
            });

            return ApiResponse::success(null, 'Tarea eliminada correctamente.');
        } catch (Throwable $e) {
            return ApiResponse::handleException($e, 'TaskController@destroy');
        }
    }
}
