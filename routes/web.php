<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AkunController;
use App\Http\Controllers\Admin\LapanganController;
use App\Http\Controllers\Admin\PemesananController;
use App\Http\Controllers\User\BookingController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\LoginController;
use App\Http\Controllers\User\RegisterController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\daftartagihanController;
use App\Http\Controllers\PilihBookingController;
use App\Http\Controllers\User\RingkasanTagihanController;
// use App\Http\Controllers\User\RingkasanTagihanController;

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
        Route::get('/grafik', [AdminController::class, 'grafik'])->name('grafik');

        Route::resource('akun', AkunController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('lapangan', LapanganController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::patch('/lapangan/pemesanan/{booking}/status', [LapanganController::class, 'updateBookingStatus'])
            ->name('lapangan.booking-status');
        Route::get('/pemesanan', [PemesananController::class, 'index'])->name('pemesanan.index');
        Route::post('/pemesanan', [PemesananController::class, 'store'])->name('pemesanan.store');
        Route::patch('/pemesanan/{booking}/status', [PemesananController::class, 'updateStatus'])
            ->name('pemesanan.status');
    });

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Route::get('/booking', [BookingController::class, 'create'])
    //     ->name('booking.create');

    // Route::post('/booking', [BookingController::class, 'store'])
    //     ->name('booking.store');

    // ===== BAGIAN DAFTAR TAGIHAN / TRANSAKSI =====
    Route::get('/transaksi', [daftartagihanController::class, 'index'])
        ->name('tagihan.index');
});

Route::get('/bookingLapangan', [pilihBookingController::class, 'create1'])
        ->name('lapangan_view');

Route::get('/bookingAlat', [pilihBookingController::class, 'create2'])
        ->name('alat_view');

Route::get('/bookingMakanan', [pilihBookingController::class, 'create3'])
        ->name('makanan_view');

        

Route::post('/pilih-booking', [pilihBookingController::class, 'store'])
        ->name('booking.pilih.store');

Route::get('/pilih-booking', [pilihBookingController::class, 'create'])
        ->name('pilihBook_view');



Route::get('/booking-lapangan', [BookingController::class, 'create'])
    ->name('booking.create');

Route::post('/booking-lapangan', [BookingController::class, 'store'])
    ->name('booking.store');

// Ringkasan Pembayaran / Tagihan
    Route::get('/pembayaran', [RingkasanTagihanController::class, 'index'])->name('pembayaran.index');
    Route::post('/pembayaran', [RingkasanTagihanController::class, 'store'])->name('pembayaran.store');
