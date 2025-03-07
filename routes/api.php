<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\CategoryController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/posts', [PostController::class, 'index']);
Route::get('/featured/posts', [PostController::class, 'featured']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/posts/{post:id}', [PostController::class, 'show']);
Route::get('/categories/{category:id}', [PostController::class, 'byCategory']);
