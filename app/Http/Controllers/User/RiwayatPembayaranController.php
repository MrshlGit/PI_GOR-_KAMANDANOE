<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class RiwayatPembayaranController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $riwayats = DB::table('pembayaran')
            ->join('detail_item_booking', 'pembayaran.id_detail_item_booking', '=', 'detail_item_booking.id_detail')
            ->join('booking', 'detail_item_booking.id_booking', '=', 'booking.id_booking')
            ->leftJoin('lapangan', 'booking.id_lapangan', '=', 'lapangan.id_lapangan')
            ->where('booking.id_user', $user->id)
            ->select(
                'pembayaran.*',
                'detail_item_booking.subtotal',
                'booking.tgl_main',
                'booking.durasi',
                'booking.total_bayar',
                'lapangan.nama_lapangan',
                'lapangan.tipe_lantai',
                'lapangan.harga_per_jam'
            )
            ->orderBy('pembayaran.tgl_bayar', 'desc')
            ->get();

        return view('ViewRiwayatPembayaran', compact('riwayats', 'user'));
    }
}