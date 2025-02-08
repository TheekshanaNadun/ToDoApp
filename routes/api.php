<?php

use App\Http\Controllers\TodoController;
use Illuminate\Support\Facades\Route;


Route::apiResource('todos', TodoController::class);
Route::get('/todos/{id}', [TodoController::class, 'show']);

Route::put('/todos/{todo}/position', [TodoController::class, 'updatePosition']);
Route::post('/todos', [TodoController::class, 'store']);
Route::put('/todos/{id}', function ($id) {
    $todo = Todo::find($id);
    $todo->completed = request()->boolean('completed');
    $todo->save();
    return $todo;
});
