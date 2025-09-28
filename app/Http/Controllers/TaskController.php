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

    public function indexApi()
    {
        $savedTasks = Task::latest()->get();

        return response()->json([
            'success' => true,
            'savedTasks' => $savedTasks,
        ]);
    }

    public function store(Request $request)
    {
        $tasks = $request->input('tasks', []);

        $errors = [];
        foreach ($tasks as $i => $task) {
            if (empty(trim($task['title'] ?? ''))) {
                $errors["tasks.$i.title"] = ['Title is required'];
            }
        }

        if (!empty($errors)) {
            return response()->json(['errors' => $errors], 422);
        }

        foreach ($tasks as $task) {
            Task::create([
                'title' => $task['title'],
                'description' => $task['description'] ?? null,
                'status' => $task['status'] ?? 'Pending',  // ✅ Added
            ]);
        }

        $savedTasks = Task::latest()->get();

        return response()->json([
            'status' => 'success',
            'savedTasks' => $savedTasks,
        ]);
    }

    public function destroy($id)
    {
        $task = Task::find($id);
        if ($task) {
            $task->delete();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
    }

    public function readme()
    {
        return view('readme');
    }
}
