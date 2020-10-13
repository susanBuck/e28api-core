<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index']);



Route::get('/login', [HomeController::class, 'methodNotAllowed'])->name('login');
Route::get('/register', [HomeController::class, 'methodNotAllowed'])->name('register');