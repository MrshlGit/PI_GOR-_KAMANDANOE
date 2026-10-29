<?php

use Illuminate\Support\Facades\Route;
<<<<<<< Updated upstream
use App\Http\Controllers\LoginController;

Route::get('/', [LoginController::class, 'index'])->name('login');

Route::post('/login', [LoginController::class, 'login']);

Route::get('/register', function () {
    return view('register_view');
})->name('register');

Route::post('/logout', [LoginController::class, 'logout']);
=======
use App\Http\Controllers\ControllerDaftarTagihan;

Route::get('/', function () {
    return view('login_view');
});

// Pastikan baris ini ada di web.php
Route::get('/daftar-tagihan', [ControllerDaftarTagihan::class, 'index']);
>>>>>>> Stashed changes
