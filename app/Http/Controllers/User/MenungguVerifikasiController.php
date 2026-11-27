<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class MenungguVerifikasiController extends Controller
{
    public function index($id)
    {
        $user = auth()->user();

        $pembayaran = DB::table('pembayaran')
            ->join('detail_item_booking', 'pembayaran.id_detail_item_booking', '=', 'detail_item_booking.id_detail')
            ->join('booking', 'detail_item_booking.id_booking', '=', 'booking.id_booking')
            ->leftJoin('lapangan', 'booking.id_lapangan', '=', 'lapangan.id_lapangan')
            ->where('pembayaran.id_pembayaran', $id)
            ->where('booking.id_user', $user->id)
            ->select(
                'pembayaran.*',
                'detail_item_booking.subtotal',
                'booking.total_bayar',
                'booking.durasi',
                'lapangan.harga_per_jam'
            )
            ->first();

        if (!$pembayaran) {
            abort(404);
        }

        $hargaDb = (float) ($pembayaran->subtotal ?? $pembayaran->total_bayar ?? 0);
        $hargaHitung = (float) ($pembayaran->harga_per_jam ?? 0) * (int) ($pembayaran->durasi ?? 1);
        $nominal = ($hargaDb >= 1000) ? $hargaDb : $hargaHitung;

        $totalFormatted = 'Rp ' . number_format($nominal, 0, ',', '.');

        return view('ViewMenungguVerifikasi', compact('pembayaran', 'totalFormatted', 'user'));
    }
}