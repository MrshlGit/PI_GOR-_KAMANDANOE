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

        // Ambil detail item (alat & makanan) dari tabel detail_items
        $rawDetailItems = DB::table('detail_items as d')
            ->leftJoin('alats as a', 'd.id_alat', '=', 'a.id_alat')
            ->leftJoin('makanans as m', 'd.id_makanan', '=', 'm.id_makanan')
            ->select(
                'd.id_detail_item',
                'd.id_pesanan',
                'd.id_alat',
                'd.id_makanan',
                'd.nama_item',
                'd.jumlah',
                'd.harga_satuan',
                'd.subtotal',
                'a.nama as nama_alat',
                'a.harga_sewa as harga_alat',
                'm.nama as nama_makanan',
                'm.harga_sewa as harga_makanan'
            )
            ->orderBy('d.id_detail_item', 'asc')
            ->get();

        $detailItems = [];
        $totalDetail = 0;

        foreach ($rawDetailItems as $detail) {
            if (!is_null($detail->id_makanan)) {
                $namaItem  = $detail->nama_makanan ?? $detail->nama_item ?? 'Makanan';
                $jenisItem = 'Makanan';
                $hargaRef  = (float) ($detail->harga_makanan ?? 0);
            } elseif (!is_null($detail->id_alat)) {
                $namaItem  = $detail->nama_alat ?? $detail->nama_item ?? 'Alat';
                $jenisItem = 'Alat';
                $hargaRef  = (float) ($detail->harga_alat ?? 0);
            } else {
                $namaItem  = $detail->nama_item ?? 'Item';
                $jenisItem = 'Lainnya';
                $hargaRef  = 0;
            }

            $jumlahItem  = max(1, (int) ($detail->jumlah ?? 1));
            $subtotalDb  = (float) ($detail->subtotal ?? 0);
            $hargaSatuan = (float) ($detail->harga_satuan ?? 0);

            $hargaItem = $subtotalDb > 0
                ? $subtotalDb
                : ($hargaSatuan > 0 ? $hargaSatuan * $jumlahItem : $hargaRef * $jumlahItem);

            $detailItems[] = [
                'id_pesanan' => $detail->id_pesanan,
                'nama'       => $namaItem,
                'jenis'      => $jenisItem,
                'jumlah'     => $jumlahItem,
                'subtotal'   => $hargaItem,
            ];

            $totalDetail += $hargaItem;
        }

        $hargaDb = (float) ($pembayaran->subtotal ?? $pembayaran->total_bayar ?? 0);
        $hargaHitung = (float) ($pembayaran->harga_per_jam ?? 0) * (int) ($pembayaran->durasi ?? 1);
        $nominal = ($hargaDb >= 1000) ? $hargaDb : ($hargaHitung + $totalDetail);

        $totalFormatted = 'Rp ' . number_format($nominal, 0, ',', '.');

        return view('ViewMenungguVerifikasi', compact(
            'pembayaran',
            'totalFormatted',
            'detailItems',
            'user'
        ));
    }
}