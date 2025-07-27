<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/broadcasting/auth', function () {
        return response()->json(['message' => 'Authenticated']);
    });
});