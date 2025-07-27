<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Http\Resources\TaskResource;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function dashboard(): JsonResponse
    {
        $users = User::withCount([
            'tasks',
            'tasks as completed_tasks_count' => function ($query) {
                $query->where('status', 'completed');
            },
            'tasks as pending_tasks_count' => function ($query) {
                $query->where('status', 'pending');
            }
        ])->paginate(15);

        $globalStats = $this->taskService->getTaskStatistics();

        return response()->json([
            'users' => UserResource::collection($users->items()),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
            'global_statistics' => $globalStats,
        ]);
    }

    public function userTasks(User $user): JsonResponse
    {
        $tasks = $user->tasks()->with('user')->ordered()->get();
        
        return response()->json([
            'user' => new UserResource($user),
            'tasks' => TaskResource::collection($tasks),
        ]);
    }

    public function allTasks(Request $request): JsonResponse
    {
        $tasks = $this->taskService->getAllTasks(
            $request->get('per_page', 15)
        );

        return response()->json([
            'tasks' => TaskResource::collection($tasks->items()),
            'pagination' => [
                'current_page' => $tasks->currentPage(),
                'last_page' => $tasks->lastPage(),
                'per_page' => $tasks->perPage(),
                'total' => $tasks->total(),
            ],
        ]);
    }
}