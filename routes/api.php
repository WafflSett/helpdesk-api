<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // tickets
    Route::get('/tickets', [TicketController::class, 'index']);
    Route::post('/tickets', [TicketController::class, 'store']);
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->middleware('can:view,ticket');
    Route::put('/tickets/{ticket}', [TicketController::class, 'update'])->middleware('can:update,ticket');

    // admin only
    Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy'])->middleware('can:delete,ticket');
    Route::get('/users', [UserController::class, 'users']);
});
