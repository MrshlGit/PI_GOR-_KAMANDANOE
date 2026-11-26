<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
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

        // 1. Ambil booking milik user yang berstatus 'menunggu' beserta data lapangannya
        $bookings = DB::table('booking as b')
            ->join('lapangan as l', 'b.id_lapangan', '=', 'l.id_lapangan')
            ->select(
                'b.id_booking',
                'b.id_user',
                'b.id_lapangan',
                'b.tgl_main',
                'b.jam_mulai',
                'b.status_booking',
                'b.total_bayar',
                'b.durasi',
                'l.nama_lapangan',
                'l.tipe_lantai',
                'l.harga_per_jam'
            )
            ->where('b.id_user', $user->id)
            ->where('b.status_booking', 'menunggu')
            ->orderBy('b.tgl_main', 'desc')
            ->orderBy('b.jam_mulai', 'desc')
            ->get();

        // 2. Susun data item tagihan dan hitung total kumulatif
        $tagihans = [];
        $totalKumulatif = 0;

        foreach ($bookings as $item) {
            $namaLapangan  = $item->nama_lapangan ?? 'Lapangan';
            $tipeLantai    = $item->tipe_lantai ?? '-';
            $hargaPerJam   = (float) ($item->harga_per_jam ?? 0);
            $durasiBooking = (int) ($item->durasi ?? 1);

            $hargaDb = (float) ($item->total_bayar ?? 0);
            $hargaBooking = ($hargaDb >= 1000) ? $hargaDb : ($hargaPerJam * $durasiBooking);

            $tagihans[] = [
                'id_booking' => $item->id_booking,
                'nama'       => $namaLapangan,
                'jenis'      => $tipeLantai,
                'jumlah'     => 1,
                'durasi'     => $durasiBooking,
                'harga'      => $hargaBooking,
                'tgl_main'   => $item->tgl_main,
                'jam_mulai'  => $item->jam_mulai,
            ];

            $totalKumulatif += $hargaBooking;
        }

        // 3. Ambil booking pertama (jika dibutuhkan di view) dan kirim ke ViewDaftarTagihan
        $booking = $bookings->first();

        return view('ViewDaftarTagihan', compact('customer', 'tagihans', 'booking', 'totalKumulatif'));
    }
}