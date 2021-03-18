<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;

Route::get('/', [HomeController::class, 'index']);
Route::get('/refresh', [HomeController::class, 'refresh']);
Route::get('/login-as/{user_id}', [AuthController::class, 'loginAs']);

Route::get('/login-required', function () {
    return response()->json([
        'success' => false,
        'errors' => ['Access denied - Login required.'],
        'test' => 'access-denied-login-required'
    ]);
})->name('login-required');