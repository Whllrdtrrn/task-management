<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Middleware\CheckAdmin; 

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    Route::post('/broadcasting/auth', function (Request $request) {
        \Log::info('Broadcasting auth attempt', [
            'user' => $request->user() ? $request->user()->id : 'none',
            'headers' => $request->headers->all(),
            'token' => $request->bearerToken() ? 'present' : 'missing',
            'channel' => $request->input('channel_name'),
            'socket_id' => $request->input('socket_id'),
        ]);
        
        $user = $request->user();
        
        if (!$user) {
            \Log::error('Broadcasting auth failed - no user');
            return response()->json(['error' => 'Unauthorized - no user found'], 401);
        }
        
        $channelName = $request->input('channel_name');
        $socketId = $request->input('socket_id');
        
        if (!$channelName || !$socketId) {
            \Log::error('Broadcasting auth failed - missing params', [
                'channel_name' => $channelName,
                'socket_id' => $socketId
            ]);
            return response()->json(['error' => 'Missing channel_name or socket_id'], 400);
        }
        
        if (strpos($channelName, 'private-user.') === 0) {
            $userId = str_replace('private-user.', '', $channelName);
            if ((int)$userId !== $user->id) {
                \Log::error('Broadcasting auth failed - wrong user', [
                    'requested_user' => $userId,
                    'actual_user' => $user->id
                ]);
                return response()->json(['error' => 'Access denied to this channel'], 403);
            }
        }
        
        \Log::info('Broadcasting auth successful', ['user_id' => $user->id]);
        
        return response()->json([
            'auth' => 'authenticated',
            'user_data' => json_encode([
                'id' => $user->id,
                'name' => $user->name,
            ])
        ]);
    });
    
    Route::apiResource('tasks', TaskController::class);
    Route::post('/tasks/reorder', [TaskController::class, 'reorder']);
    
    Route::middleware(CheckAdmin::class)->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/users/{user}/tasks', [AdminController::class, 'userTasks']);
        Route::get('/tasks', [AdminController::class, 'allTasks']);
    });
});