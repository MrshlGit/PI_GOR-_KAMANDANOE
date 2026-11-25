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

        // Ambil SEMUA data booking milik user yang berstatus 'menunggu'
        $bookings = DB::table('booking')
            ->where('id_user', $user->id)
            ->where('status_booking', 'menunggu')
            ->orderBy('tgl_main', 'desc')
            ->orderBy('jam_mulai', 'desc')
            ->get();

        $tagihans = [];
        $totalKumulatif = 0;

        foreach ($bookings as $item) {
            $lapangan = DB::table('lapangan')->where('id_lapangan', $item->id_lapangan)->first();

            // Definisi lengkap variabel dari tabel lapangan & booking
            $namaLapangan  = $lapangan->nama_lapangan ?? 'Lapangan';
            $tipeLantai    = $lapangan->tipe_lantai ?? '-';
            $hargaPerJam   = (float) ($lapangan->harga_per_jam ?? 0);
            $durasiBooking = (int) ($item->durasi ?? 1);

            // Ambil total_bayar; jika nilainya 0 atau < 1000, hitung ulang dari harga_per_jam * durasi
            $hargaDb = (float) ($item->total_bayar ?? 0);
            $hargaBooking = ($hargaDb >= 1000) ? $hargaDb : ($hargaPerJam * $durasiBooking);

            $tagihans[] = [
                'nama'   => $namaLapangan . ' (' . $tipeLantai . ') - ' . $item->tgl_main,
                'jenis'  => 'Lapangan GOR',
                'jumlah' => 1,
                'durasi' => $durasiBooking,
                'harga'  => $hargaBooking, // Angka murni (misal: 120000)
            ];

            $totalKumulatif += $hargaBooking;
        }

        $booking = $bookings->first();

        return view('ViewDaftarTagihan', compact('customer', 'tagihans', 'booking', 'totalKumulatif'));
    }
}