<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AkunController;
use App\Http\Controllers\Admin\LapanganController;
use App\Http\Controllers\Admin\LaporanKeuanganController;
use App\Http\Controllers\Admin\PemesananController;
use App\Http\Controllers\Admin\SaranKritikController;
use App\Http\Controllers\User\BookingController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\LoginController;
use App\Http\Controllers\User\RegisterController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\DaftarTagihanController; // <-- Tambahkan \User di sini
use App\Http\Controllers\PilihBookingController;
use App\Http\Controllers\User\RingkasanTagihanController;
use App\Http\Controllers\User\AlatController;
use App\Http\Controllers\User\MakananController;
use App\Http\Controllers\User\NotificationController;
// use App\Http\Controllers\User\RingkasanTagihanController;
use App\Http\Controllers\User\LayananInformasiController;
use App\Http\Controllers\User\TurnamenController;
use App\Http\Controllers\User\MenungguVerifikasiController;
use App\Http\Controllers\User\RiwayatPembayaranController;

Route::view('/', 'home_view')->name('home');

Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

Route::get('/register', fn() => view('register_view'))->name('register');
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
        Route::get('/saran-kritik', [SaranKritikController::class, 'index'])
            ->name('saran-kritik.index');
        Route::get('/laporan-keuangan', [LaporanKeuanganController::class, 'index'])
            ->name('laporan-keuangan.index');
        Route::get('/laporan-keuangan/bukti/{pembayaran}', [LaporanKeuanganController::class, 'buktiPembayaran'])
            ->name('laporan-keuangan.bukti');

    });

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/notifikasi/{notification}', [NotificationController::class, 'show'])
        ->name('notifications.show');

    Route::get('/layanan', [LayananInformasiController::class, 'index'])->name('layanan.index');
    Route::post('/layanan/saran', [LayananInformasiController::class, 'kirimSaran'])->name('layanan.saran');
    // Route::get('/booking', [BookingController::class, 'create'])
    //     ->name('booking.create');

    // Route::post('/booking', [BookingController::class, 'store'])
    //     ->name('booking.store');

    // ===== BAGIAN DAFTAR TAGIHAN / TRANSAKSI =====
    Route::get('/transaksi', [DaftarTagihanController::class, 'index'])
        ->name('tagihan.index');
    Route::get('/turnamen', [TurnamenController::class, 'index'])->name('turnamen.index');
    Route::post('/turnamen', [TurnamenController::class, 'store'])->name('turnamen.store');
    Route::post('/turnamen/{id}/daftar', [TurnamenController::class, 'daftar'])->name('turnamen.daftar');
    Route::delete('/turnamen/{id}/daftar', [TurnamenController::class, 'batalDaftar'])->name('turnamen.batal');
    Route::delete('/turnamen/{id}', [TurnamenController::class, 'destroy'])->name('turnamen.destroy');
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

Route::get('/booking-alat', [AlatController::class, 'ambil'])
    ->name('alat.create');
Route::post('/booking-alat/konfirmasi', [AlatController::class, 'konfirmasi'])
    ->name('alat.confirm');

Route::get('/booking-makanan', [MakananController::class, 'ambil'])
    ->name('makanan.create');
Route::post('/booking-makanan/konfirmasi', [MakananController::class, 'konfirmasi'])
    ->name('makanan.confirm');


// Ringkasan Pembayaran / Tagihan
Route::get('/pembayaran', [RingkasanTagihanController::class, 'index'])->name('pembayaran.index');
Route::post('/pembayaran', [RingkasanTagihanController::class, 'store'])->name('pembayaran.store');

Route::middleware('auth')->group(function () {
    // ... route lainnya ...
    Route::get('/pembayaran/{id}/menunggu-verifikasi', [MenungguVerifikasiController::class, 'index'])
        ->name('pembayaran.menunggu');

    Route::get('/pembayaran/riwayat', [RiwayatPembayaranController::class, 'index'])
        ->name('pembayaran.riwayat');
});
