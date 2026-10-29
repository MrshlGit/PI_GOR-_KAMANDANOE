<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;

// Route::get('/', [LoginController::class, 'index'])->name('login');


Route::get('/', function () {
    return view('login_view');
})->name('login');

Route::post('/login', [LoginController::class, 'login']);

Route::get('/register', function () {
    return view('register_view');
})->name('register');

Route::post('/logout', [LoginController::class, 'logout']);