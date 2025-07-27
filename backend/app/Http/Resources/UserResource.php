<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'is_admin' => $this->is_admin,
            'tasks_count' => $this->whenCounted('tasks'),
            'completed_tasks_count' => $this->when(
                isset($this->completed_tasks_count),
                $this->completed_tasks_count
            ),
            'pending_tasks_count' => $this->when(
                isset($this->pending_tasks_count),
                $this->pending_tasks_count
            ),
            'created_at' => $this->created_at,
        ];
    }
}