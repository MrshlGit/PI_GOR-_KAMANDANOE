<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;

// Route::get('/', [LoginController::class, 'index'])->name('login');


Route::get('/', function () {
    return view('home_view');
})->name('home');

Route::get('/login', function () {
    return view('login_view');
})->name('login');

Route::post('/login', [LoginController::class, 'login']);

Route::get('/register', function () {
    return view('register_view');
})->name('register');

Route::post('/register', [RegisterController::class, 'register']);

Route::get('/dashboard', function () {
    return view('dashboardUser');
})->name('dashboard');

Route::post('/logout', [LoginController::class, 'logout']);