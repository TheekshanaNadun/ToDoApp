<?php

use App\Http\Controllers\TodoController;
use Illuminate\Support\Facades\Route;


Route::apiResource('todos', TodoController::class);
Route::put('/todos/{todo}/position', [TodoController::class, 'updatePosition']);
Route::post('/todos', [TodoController::class, 'store']);
