<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_create_task()
    {
        Sanctum::actingAs($this->user);

        $taskData = [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'priority' => 'high',
        ];

        $response = $this->postJson('/api/tasks', $taskData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'task' => [
                        'id', 'title', 'description', 'status', 'priority', 'order', 'user_id'
                    ],
                    'message'
                ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_user_can_update_own_task()
    {
        Sanctum::actingAs($this->user);

        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $updateData = [
            'title' => 'Updated Task',
            'status' => 'completed',
        ];

        $response = $this->putJson("/api/tasks/{$task->id}", $updateData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Task',
            'status' => 'completed',
        ]);
    }

    public function test_user_cannot_update_other_users_task()
    {
        $otherUser = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $otherUser->id]);

        Sanctum::actingAs($this->user);

        $response = $this->putJson("/api/tasks/{$task->id}", [
            'title' => 'Hacked Task',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_reorder_tasks()
    {
        Sanctum::actingAs($this->user);

        $task1 = Task::factory()->create(['user_id' => $this->user->id, 'order' => 1]);
        $task2 = Task::factory()->create(['user_id' => $this->user->id, 'order' => 2]);

        $reorderData = [
            'tasks' => [
                ['id' => $task1->id, 'order' => 2],
                ['id' => $task2->id, 'order' => 1],
            ]
        ];

        $response = $this->postJson('/api/tasks/reorder', $reorderData);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('tasks', [
            'id' => $task1->id,
            'order' => 2,
        ]);
        
        $this->assertDatabaseHas('tasks', [
            'id' => $task2->id,
            'order' => 1,
        ]);
    }

    public function test_task_validation_rules()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/tasks', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['title']);
    }
}