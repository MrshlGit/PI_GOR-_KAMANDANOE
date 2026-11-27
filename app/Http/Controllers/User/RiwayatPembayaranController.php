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
            ->leftJoin('detail_item_booking', 'pembayaran.id_detail_item_booking', '=', 'detail_item_booking.id_detail')
            ->leftJoin('booking', 'detail_item_booking.id_booking', '=', 'booking.id_booking')
            ->leftJoin('lapangan', 'booking.id_lapangan', '=', 'lapangan.id_lapangan')
            ->where(function ($query) use ($user) {
                $query->where('booking.id_user', $user->id)
                    ->orWhereNull('booking.id_user');
            })
            ->select(
                'pembayaran.*',
                'detail_item_booking.id_booking',
                'detail_item_booking.subtotal',
                'booking.id_user',
                'booking.tgl_main',
                'booking.durasi',
                'booking.total_bayar',
                'lapangan.nama_lapangan',
                'lapangan.tipe_lantai',
                'lapangan.harga_per_jam'
            )
            ->orderBy('pembayaran.tgl_bayar', 'desc')
            ->get();

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

        $itemsByPesanan = [];
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

            $idPesanan = (string) $detail->id_pesanan;
            $itemsByPesanan[$idPesanan][] = [
                'nama'     => $namaItem,
                'jenis'    => $jenisItem,
                'jumlah'   => $jumlahItem,
                'subtotal' => $hargaItem,
            ];
        }

        // Gabungkan informasi item ke tiap baris riwayat pembayaran
        foreach ($riwayats as $riwayat) {
            $idKey = (string) ($riwayat->id_booking ?? '');
            $matchedItems = $itemsByPesanan[$idKey] ?? [];

            $riwayat->detail_items_list = $matchedItems;
            $riwayat->daftar_item = collect($matchedItems)
                ->map(fn ($i) => "{$i['nama']} ({$i['jenis']}) x{$i['jumlah']}")
                ->implode(', ');

            $subtotalDetail = collect($matchedItems)->sum('subtotal');
            $baseNominal = (float) ($riwayat->subtotal ?? $riwayat->total_bayar ?? 0);
            $riwayat->total_tagihan = $baseNominal > 0 ? $baseNominal : $subtotalDetail;
        }

        return view('ViewRiwayatPembayaran', compact('riwayats', 'user'));
    }
}