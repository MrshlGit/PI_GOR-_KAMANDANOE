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
            ->leftJoin('detail_item_booking', 'pembayaran.id_detail_item_booking', '=', 'detail_item_booking.id_detail')
            ->leftJoin('booking', 'detail_item_booking.id_booking', '=', 'booking.id_booking')
            ->leftJoin('lapangan', 'booking.id_lapangan', '=', 'lapangan.id_lapangan')
            ->where('pembayaran.id_pembayaran', $id)
            ->where(function ($query) use ($user) {
                $query->where('booking.id_user', $user->id)
                    ->orWhereNull('booking.id_user');
            })
            ->select(
                'pembayaran.*',
                'detail_item_booking.id_booking',
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
        $hargaHitung = (float) ($pembayaran->harga_per_jam ?? 0) * max(1, (int) ($pembayaran->durasi ?? 1));

        if ($hargaDb >= 1000) {
            $nominal = $hargaDb;
        } else {
            $totalDetail = 0.0;
            $rawDetailItems = DB::table('detail_items as d')
                ->leftJoin('alats as a', 'd.id_alat', '=', 'a.id_alat')
                ->leftJoin('makanans as m', 'd.id_makanan', '=', 'm.id_makanan')
                ->select(
                    'd.jumlah',
                    'd.harga_satuan',
                    'd.subtotal',
                    'd.id_alat',
                    'd.id_makanan',
                    'a.harga_sewa as harga_alat',
                    'm.harga_sewa as harga_makanan'
                )
                ->get();

            foreach ($rawDetailItems as $detail) {
                $jumlahItem  = max(1, (int) ($detail->jumlah ?? 1));
                $subtotalDb  = (float) ($detail->subtotal ?? 0);
                $hargaSatuan = (float) ($detail->harga_satuan ?? 0);
                $hargaRef    = !is_null($detail->id_makanan)
                    ? (float) ($detail->harga_makanan ?? 0)
                    : (float) ($detail->harga_alat ?? 0);

                $totalDetail += $subtotalDb > 0
                    ? $subtotalDb
                    : ($hargaSatuan > 0 ? $hargaSatuan * $jumlahItem : $hargaRef * $jumlahItem);
            }

            $nominal = $hargaHitung + $totalDetail;
        }

        $totalFormatted = 'Rp ' . number_format($nominal, 0, ',', '.');

        return view('ViewMenungguVerifikasi', compact(
            'pembayaran',
            'totalFormatted',
            'user'
        ));
    }
}