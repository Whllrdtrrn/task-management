<?php
namespace App\Events;

use App\Models\Task;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TasksReordered implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $tasks;
    public $userId;

    public function __construct(array $tasks, int $userId)
    {
        $this->tasks = $tasks;
        $this->userId = $userId;
    }

    public function broadcastOn()
    {
        return new PresenceChannel('user.' . $this->userId);
    }

    public function broadcastWith()
    {
        return [
            'tasks' => $this->tasks,
            'type' => 'reordered',
        ];
    }
}
