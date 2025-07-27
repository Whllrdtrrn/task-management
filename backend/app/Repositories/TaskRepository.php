<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class TaskRepository
{
    protected $model;

    public function __construct(Task $model)
    {
        $this->model = $model;
    }

    public function getAllForUser(int $userId, array $filters = []): Collection
    {
        // Temporarily disable cache for testing
        // $cacheKey = "user_{$userId}_tasks_" . md5(serialize($filters));
        // return Cache::remember($cacheKey, 300, function () use ($userId, $filters) {

        $query = $this->model->byUser($userId)->ordered();

        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (!empty($filters['priority'])) {
            $query->byPriority($filters['priority']);
        }

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        return $query->get();
        // });
    }

    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with('user')
            ->ordered()
            ->paginate($perPage);
    }

    public function create(array $data): Task
    {
        if (!isset($data['order'])) {
            $data['order'] = $this->getNextOrder($data['user_id']);
        }

        $task = $this->model->create($data);
        $this->clearUserCache($data['user_id']);

        return $task;
    }

    public function update(Task $task, array $data): Task
    {
        $task->update($data);
        $this->clearUserCache($task->user_id);

        return $task->fresh();
    }

    public function delete(Task $task): bool
    {
        $userId = $task->user_id;
        $result = $task->delete();
        $this->clearUserCache($userId);

        return $result;
    }

    public function reorder(array $tasks): void
    {
        foreach ($tasks as $taskData) {
            $this->model->where('id', $taskData['id'])
                ->update(['order' => $taskData['order']]);
        }

        $userIds = collect($tasks)->pluck('user_id')->unique();
        foreach ($userIds as $userId) {
            $this->clearUserCache($userId);
        }
    }

    protected function getNextOrder(int $userId): int
    {
        return $this->model->byUser($userId)->max('order') + 1;
    }


    protected function clearUserCache(?int $userId): void
    {
        if ($userId === null) {
            return;
        }

        $commonFilters = [
            [],
            ['status' => 'pending'],
            ['status' => 'completed'],
            ['priority' => 'low'],
            ['priority' => 'medium'],
            ['priority' => 'high'],
        ];

        foreach ($commonFilters as $filters) {
            $cacheKey = "user_{$userId}_tasks_" . md5(serialize($filters));
            Cache::forget($cacheKey);
        }
    }
}
