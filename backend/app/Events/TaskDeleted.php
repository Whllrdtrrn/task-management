<?php
namespace App\Events;

use App\Models\Task;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function broadcastOn()
    {
        return [
            new Channel('tasks'),
        ];
    }

    public function broadcastWith()
    {
        return [
            'task_id' => $this->task->id,
            'user_id' => $this->task->user_id,
        ];
    }

    public function broadcastAs()
    {
        return 'task.deleted';
    }
}