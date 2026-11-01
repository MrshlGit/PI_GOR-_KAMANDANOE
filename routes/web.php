<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::get('/register', fn () => view('register_view'))->name('register');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/user-dashboard', fn () => view('dashboardUser'))->name('user.dashboard');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');