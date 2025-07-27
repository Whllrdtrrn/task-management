<?php

namespace App\Services;

use App\Models\Task;
use App\Repositories\TaskRepository;
use App\Events\TaskCreated;
use App\Events\TaskUpdated;
use App\Events\TaskDeleted;
use App\Events\TasksReordered;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskService
{
    protected $taskRepository;

    public function __construct(TaskRepository $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    public function getUserTasks(int $userId, array $filters = []): Collection
    {
        return $this->taskRepository->getAllForUser($userId, $filters);
    }

    public function getAllTasks(int $perPage = 15): LengthAwarePaginator
    {
        return $this->taskRepository->getAllPaginated($perPage);
    }

    public function createTask(array $data): Task
    {
        $task = $this->taskRepository->create($data);
        
        broadcast(new TaskCreated($task))->toOthers();
        
        return $task;
    }

    public function updateTask(Task $task, array $data): Task
    {
        $updatedTask = $this->taskRepository->update($task, $data);
        
        broadcast(new TaskUpdated($updatedTask))->toOthers();
        
        return $updatedTask;
    }

    public function deleteTask(Task $task): bool
    {
        broadcast(new TaskDeleted($task))->toOthers();
        
        $result = $this->taskRepository->delete($task);
        
        return $result;
    }

    public function reorderTasks(array $tasks): void
    {
        $userId = null;
        
        if (!empty($tasks) && isset($tasks[0]['user_id'])) {
            $userId = $tasks[0]['user_id'];
        } elseif (!empty($tasks) && isset($tasks[0]['id'])) {
            $firstTask = Task::find($tasks[0]['id']);
            $userId = $firstTask?->user_id;
        }

        $this->taskRepository->reorder($tasks);
        
        if ($userId) {
            broadcast(new TasksReordered($tasks, $userId))->toOthers();
        }
    }
    
    public function getTaskStatistics(int $userId = null): array
    {
        $baseQuery = function() use ($userId) {
            $query = Task::query();
            if ($userId) {
                $query->where('user_id', $userId);
            }
            return $query;
        };

        return [
            'total' => $baseQuery()->count(),
            'completed' => $baseQuery()->where('status', 'completed')->count(),
            'pending' => $baseQuery()->where('status', 'pending')->count(),
            'high_priority' => $baseQuery()->where('priority', 'high')->count(),
            'medium_priority' => $baseQuery()->where('priority', 'medium')->count(),
            'low_priority' => $baseQuery()->where('priority', 'low')->count(),
        ];
    }
}