<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;

Route::get('/', fn() => redirect('/tasks'));
Route::get('/tasks', [TaskController::class, 'index']);


Route::get('/tasks/all', [TaskController::class, 'indexApi']);
Route::post('/tasks', [TaskController::class, 'store']);
Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);


Route::get('/readme', [TaskController::class, 'readme'])->name('readme');
