<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Task;
use App\Services\TaskService;
use App\Repositories\TaskRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $taskService;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->taskService = app(TaskService::class);
    }

    public function test_creates_task_with_correct_order()
    {
        $taskData = [
            'title' => 'First Task',
            'description' => 'Test Description',
            'user_id' => $this->user->id,
        ];

        $task = $this->taskService->createTask($taskData);

        $this->assertEquals(1, $task->order);
        $this->assertEquals($this->user->id, $task->user_id);
    }

    public function test_gets_user_tasks_with_filters()
    {
        Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
            'priority' => 'high'
        ]);
        
        Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'completed',
            'priority' => 'low'
        ]);

        $pendingTasks = $this->taskService->getUserTasks($this->user->id, ['status' => 'pending']);
        $highPriorityTasks = $this->taskService->getUserTasks($this->user->id, ['priority' => 'high']);

        $this->assertCount(1, $pendingTasks);
        $this->assertCount(1, $highPriorityTasks);
    }

    public function test_calculates_statistics_correctly()
    {
        Task::factory()->create(['user_id' => $this->user->id, 'status' => 'pending']);
        Task::factory()->create(['user_id' => $this->user->id, 'status' => 'completed']);
        Task::factory()->create(['user_id' => $this->user->id, 'status' => 'pending']);

        $stats = $this->taskService->getTaskStatistics($this->user->id);

        $this->assertEquals(3, $stats['total']);
        $this->assertEquals(1, $stats['completed']);
        $this->assertEquals(2, $stats['pending']);
    }
}