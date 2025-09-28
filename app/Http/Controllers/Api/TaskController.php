<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;

class TaskController extends Controller
{
    //all task
    public function index()
    {
        $savedTasks = Task::latest()->get();
        return response()->json([
            'success' => true,
            'savedTasks' => $savedTasks,
        ]);
    }

    //create task
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
                'status' => $task['status'] ?? 'Pending',
            ]);
        }

        $savedTasks = Task::latest()->get();

        return response()->json([
            'status' => 'success',
            'savedTasks' => $savedTasks,
        ]);
    }

    //delete task
    public function destroy($id)
    {
        $task = Task::find($id);
        if ($task) {
            $task->delete();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    }

    //update status
    public function updateStatus(Request $request, $id)
    {
        $task = Task::find($id);
        if (!$task) {
            return response()->json(['success' => false, 'message' => 'Task not found'], 404);
        }

        $status = $request->input('status');
        if (!in_array($status, ['Pending', 'In Progress', 'Completed'])) {
            return response()->json(['success' => false, 'message' => 'Invalid status'], 422);
        }

        $task->status = $status;
        $task->save();

        return response()->json([
            'success' => true,
            'task' => $task,
        ]);
    }
}
