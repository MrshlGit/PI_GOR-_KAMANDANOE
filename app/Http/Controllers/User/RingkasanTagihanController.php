<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    public function index()
    {
        // Data item pesanan
        $items = [
            ['nama' => 'Turnamen PB Juara', 'harga' => '200.000'],
            ['nama' => 'Lap 1 (Karpet)',     'harga' => '120.000'],
            ['nama' => 'Mie Goreng',        'harga' => '10.000'],
            ['nama' => 'Raket',             'harga' => '45.000'],
        ];

        // Data ringkasan tagihan
        $summary = [
            'tanggal'        => '19 Agus 2026',
            'petugas'        => 'P1',
            'subtotal'       => 'Rp. 375.000',
            'potongan'       => 'Rp. 0',
            'ppn'            => 'Rp. 4.000',
            'nilai_transaksi'=> 'Rp. 379.000',
            'total'          => 'Rp. 379.000'
        ];

        return view('ringkasan_pembayaran_view', compact('items', 'summary'));
    }

    public function store(Request $request)
    {
        // Logika untuk menangani upload bukti pembayaran / proses bayar
        return redirect()->back()->with('success', 'Bukti pembayaran berhasil diunggah!');
    }
}