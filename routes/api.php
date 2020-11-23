<?php
use App\Http\Controllers\HomeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductsController;

Route::post('/auth', [AuthController::class, 'auth']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::fallback(function () {
    return response()->json([
        'message' => 'Route not found'], 404);
});

if (File::exists(base_path('routes/generated-routes.php'))) {
    require 'generated-routes.php';
}