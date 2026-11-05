<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RingkasanTagihanController extends Controller
{
    public function index()
    {
        $items = [
            ['nama' => 'Turnamen PB Juara', 'harga' => '200.000'],
            ['nama' => 'Lap 1 (Karpet)',     'harga' => '120.000'],
            ['nama' => 'Mie Goreng',        'harga' => '10.000'],
            ['nama' => 'Raket',             'harga' => '45.000'],
        ];

        $summary = [
            'tanggal'        => '19 Agus 2026',
            'petugas'        => 'P1',
            'subtotal'       => 'Rp. 375.000',
            'potongan'       => 'Rp. 0',
            'ppn'            => 'Rp. 4.000',
            'nilai_transaksi'=> 'Rp. 379.000',
            'total'          => 'Rp. 379.000'
        ];

        return view('ViewRingkasanTagihan', compact('items', 'summary'));
    }

    public function store(Request $request)
    {
        // Validasi file yang diunggah
        $request->validate([
            'bukti_bayar' => 'required|image|mimes:jpeg,png,jpg,pdf|max:5000',
        ]);

        if ($request->hasFile('bukti_bayar')) {
            // Menyimpan file bukti pembayaran secara permanen ke folder storage/app/public/bukti_pembayaran
            $filePath = $request->file('bukti_bayar')->store('bukti_pembayaran', 'public');

            // (Opsional) Di sini Anda bisa menyimpan $filePath ke database bersama data tagihan pengguna

            return redirect()->route('dashboard')->with('success', 'Pembayaran berhasil dikonfirmasi dan bukti telah diunggah!');
        }
        return redirect()->back()->with('success', 'Bukti pembayaran berhasil diunggah!');
    }
}