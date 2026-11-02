<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\BookingController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home_view')->name('home');

Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

Route::get('/register', fn () => view('register_view'))->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.submit');

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    });

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

Route::get('/booking', [BookingController::class, 'create'])
    ->name('booking.create');

Route::post('/booking', [BookingController::class, 'store'])
    ->name('booking.store');

Route::get('/booking', function () {
    return view('booking_view');
})->name('booking.create');
