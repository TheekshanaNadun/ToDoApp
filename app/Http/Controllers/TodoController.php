<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;

class TodoController extends Controller
{
    public function index()
    {
        return Todo::all();
    }


    
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'category' => 'nullable|string',
                'due_date' => 'nullable|date',
            ]);
    
            $todo = Todo::create($validated + ['completed' => false]);
    
            return response()->json($todo, 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }




    public function update(Request $request, Todo $todo)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'completed' => 'sometimes|boolean',
            'category' => 'sometimes|string',
            'due_date' => 'sometimes|date'
        ]);

        $todo->update($validated);
        return $todo;
    }
    

    public function destroy(Todo $todo)
    {
        $todo->delete();
        return response()->noContent();
    }



    public function updatePosition(Request $request, Todo $todo)
    {
    $request->validate([
        'completed' => 'required|boolean'
    ]);

    $todo->update(['completed' => $request->completed]);
    return response()->json($todo);
    }

}