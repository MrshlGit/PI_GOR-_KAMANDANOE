<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ControllerDaftarTagihan;

// Rute Login & Register (dari update GitHub)
Route::get('/', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

Route::get('/register', function () {
    return view('register_view');
})->name('register');

Route::post('/logout', [LoginController::class, 'logout']);

// Rute Daftar Tagihan (milikmu)
Route::get('/daftar-tagihan', [ControllerDaftarTagihan::class, 'index']);