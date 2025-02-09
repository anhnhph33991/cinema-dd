<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MovieController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')
    ->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::prefix('/users')
            ->group(function () {
                Route::middleware('admin')->group(function () {
                    Route::post('/', [UserController::class, 'store']);
                    Route::put('/{id}', [UserController::class, 'update']);
                    Route::delete('/{id}', [UserController::class, 'destroy']);
                });

                Route::get('/', [UserController::class, 'index']);
                Route::get('/{id}', [UserController::class, 'show']);
            });

        Route::prefix('movies')
            ->group(function () {
                Route::get('/', [MovieController::class, 'index']);
                Route::get('/{slug}', [MovieController::class, 'show']);

                Route::middleware('admin')->group(function () {
                    Route::post('/', [MovieController::class, 'store']);
                });
            });

        Route::prefix('rooms')
            ->group(function () {
                Route::get('/', [RoomController::class, 'index']);
                Route::get('/{name}', [RoomController::class, 'show']);

                Route::middleware('admin')->group(function () {
                    Route::post('/', [RoomController::class, 'store']);
                    Route::delete('/{name}', [RoomController::class, 'destroy']);
                });
            });
    });
