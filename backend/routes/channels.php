<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;

Broadcast::channel('user.{userId}', function (User $user, $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('tasks', function (User $user) {
    return ['id' => $user->id, 'name' => $user->name];
});

Broadcast::channel('admin', function (User $user) {
    return $user->isAdmin() ? $user : null;
});