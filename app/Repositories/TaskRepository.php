<?php

namespace App\Repositories;

use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TaskRepository implements TaskRepositoryInterface
{
    public function __construct(
        protected Task $model
    ) {}

    /**
     * @return Collection<int, Task>
     */
    public function getAllOrderedByCreatedDesc(): Collection
    {
        return $this->model->newQuery()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Task
    {
        return $this->model->newQuery()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Task $task, array $attributes): Task
    {
        $task->update($attributes);

        return $task->fresh();
    }

    public function delete(Task $task): bool
    {
        return $task->delete();
    }
}
