<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ControllerDaftarTagihan extends Controller
{
    public function index()
    {
        // Data customer sesuai dengan tampilan HTML Anda
        $customer = [
            'nama' => 'Damai',
            'telepon' => '082212346541',
            'email' => 'Damai@gmail.com',
        ];

        // Data daftar tagihan/pesanan
        $tagihans = [
            ['nama' => 'Turnamen PB Juara', 'jenis' => 'Turnamen', 'jumlah' => 1, 'durasi' => '-', 'harga' => '200.000'],
            ['nama' => 'Lap 1 (Karpet)', 'jenis' => 'Lapangan', 'jumlah' => 1, 'durasi' => '3', 'harga' => '120.000'],
            ['nama' => 'Mie Goreng', 'jenis' => 'Makanan', 'jumlah' => 2, 'durasi' => '-', 'harga' => '10.000'],
            ['nama' => 'Raket', 'jenis' => 'Sewa Alat', 'jumlah' => 1, 'durasi' => '3', 'harga' => '45.000'],
        ];

        return view('ViewDaftarTagihan', compact('customer', 'tagihans'));
    }
}