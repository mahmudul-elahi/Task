<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;

Route::get('/', fn() => redirect('/tasks'));
Route::get('/tasks', [TaskController::class, 'index']);
Route::get('/readme', [TaskController::class, 'readme'])->name('readme');
