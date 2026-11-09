<?php

namespace App\Http\Controllers\User; // <-- Tambahkan \User di sini

use App\Http\Controllers\Controller; // Jangan lupa import parent Controller-nya
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DaftarTagihanController extends Controller
{
    public function index()
    {
        // 1. Ambil data user yang sedang login
        $user = auth()->user();

        // 2. Ambil data customer untuk ditampilkan pada form
        $customer = [
            'nama'    => $user->username ?? '-',
            'telepon' => $user->noHp ?? '-',
            'email'   => $user->email ?? '-',
        ];

        // 3. Ambil data booking terbaru berdasarkan id_user dari tabel booking
        $booking = DB::table('booking')->where('id_user', $user->id)->latest('id_booking')->first();

        // 4. Masukkan ke tabel tagihan jika booking ditemukan
        $tagihans = [];
        if ($booking) {
            $tagihans = [
                [
                    'nama'   => 'Lapangan (ID: ' . $booking->id_lapangan . ')',
                    'jenis'  => 'Lapangan GOR',
                    'jumlah' => 1,
                    'durasi' => '1 Jam',
                    'harga'  => number_format($booking->total_bayar, 0, ',', '.')
                ],
            ];
        }

        return view('ViewDaftarTagihan', compact('customer', 'tagihans', 'booking'));
    }
}