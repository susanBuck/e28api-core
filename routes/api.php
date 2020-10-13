<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use App\User;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductsController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/{name}', function () {
    return response(['error' => 'GET Method Not Allowed'], 409);
})->where('name', 'login|register|');

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::fallback(function () {
    return response()->json([
        'message' => 'Route not found'], 404);
});

if (File::exists(base_path('routes/generated-routes.php'))) {
    require 'generated-routes.php';
}