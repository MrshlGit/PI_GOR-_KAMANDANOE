<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DaftarTagihanController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $customer = [
            'nama'    => $user->username ?? '-',
            'telepon' => $user->noHp ?? '-',
            'email'   => $user->email ?? '-',
        ];

        $booking = DB::table('booking')->where('id_user', $user->id)->latest('id_booking')->first();

        $tagihans = [];
        $totalKumulatif = 0;

        if ($booking) {
            // Ambil harga dengan opsi beberapa nama kolom di database
            $hargaBooking = $booking->total_bayar ?? $booking->total ?? $booking->harga ?? $booking->biaya ?? 0;

            $tagihans = [
                [
                    'nama'   => 'Lapangan (ID: ' . ($booking->id_lapangan ?? '-') . ')',
                    'jenis'  => 'Lapangan GOR',
                    'jumlah' => 1,
                    'durasi' => '1 Jam',
                    'harga'  => $hargaBooking
                ],
            ];
            
            $totalKumulatif += $hargaBooking;
        }

        return view('ViewDaftarTagihan', compact('customer', 'tagihans', 'booking', 'totalKumulatif'));
    }
}