<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Requests\TaskRequest;
use App\Http\Requests\ReorderTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    use AuthorizesRequests;
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'priority', 'search']);
        $tasks = $this->taskService->getUserTasks($request->user()->id, $filters);

        return response()->json([
            'tasks' => TaskResource::collection($tasks),
            'statistics' => $this->taskService->getTaskStatistics($request->user()->id),
        ]);
    }

    public function store(TaskRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        $task = $this->taskService->createTask($data);

        return response()->json([
            'task' => new TaskResource($task),
            'message' => 'Task created successfully',
        ], 201);
    }

    public function show(Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        return response()->json([
            'task' => new TaskResource($task),
        ]);
    }

    public function update(TaskRequest $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $updatedTask = $this->taskService->updateTask($task, $request->validated());

        return response()->json([
            'task' => new TaskResource($updatedTask),
            'message' => 'Task updated successfully',
        ]);
    }

    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        $this->taskService->deleteTask($task);

        return response()->json([
            'message' => 'Task deleted successfully',
        ]);
    }

    public function forceDelete($id): JsonResponse
    {
        $task = Task::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $task);

        $task->forceDelete();

        return response()->json([
            'message' => 'Task permanently deleted',
        ]);
    }

    public function restore($id): JsonResponse
    {
        $task = Task::withTrashed()->findOrFail($id);
        $this->authorize('delete', $task);

        $task->restore(); 

        return response()->json([
            'message' => 'Task restored successfully',
        ]);
    }

    public function reorder(ReorderTaskRequest $request): JsonResponse
    {
        $tasks = collect($request->validated()['tasks']);

        $userTaskIds = $request->user()->tasks()->pluck('id');
        $requestedTaskIds = $tasks->pluck('id');

        if ($requestedTaskIds->diff($userTaskIds)->isNotEmpty()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $this->taskService->reorderTasks($tasks->toArray());

        return response()->json([
            'message' => 'Tasks reordered successfully',
        ]);
    }
}
