<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TaskController;

Route::get('/tasks', [TaskController::class, 'index']);
Route::post('/tasks', [TaskController::class, 'store']);
Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);
Route::patch('/tasks/{id}/status', [TaskController::class, 'updateStatus']);
