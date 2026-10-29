<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ControllerDaftarTagihan;

<<<<<<< HEAD
// Rute Login & Register (dari update GitHub)
Route::get('/', [LoginController::class, 'index'])->name('login');
=======
// Route::get('/', [LoginController::class, 'index'])->name('login');


Route::get('/', function () {
    return view('login_view');
})->name('login');

>>>>>>> 2de267ddf7b9b61755f0922ae214cb70ac25ec79
Route::post('/login', [LoginController::class, 'login']);

Route::get('/register', function () {
    return view('register_view');
})->name('register');

Route::post('/logout', [LoginController::class, 'logout']);

// Rute Daftar Tagihan (milikmu)
Route::get('/daftar-tagihan', [ControllerDaftarTagihan::class, 'index']);