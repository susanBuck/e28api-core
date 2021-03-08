<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;

Route::get('/', [HomeController::class, 'index']);
Route::get('/refresh', [HomeController::class, 'refresh']);
Route::post('/login-as', [AuthController::class, 'loginAs']);

Route::get('/login-required', function () {
    return 'Access denied - Login required.';
})->name('login-requred');