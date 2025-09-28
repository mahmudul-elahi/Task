<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;

class TaskController extends Controller
{
    public function index()
    {
        $savedTasks = Task::latest()->get();
        return view('tasks', compact('savedTasks'));
    }


    public function readme()
    {
        return view('readme');
    }
}
