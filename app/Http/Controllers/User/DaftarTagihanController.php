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

        $excludedStatuses = ['lunas', 'selesai', 'batal', 'dibatalkan', 'cancelled', 'paid'];

        // 1. Ambil booking milik user yang belum lunas / belum selesai
        $bookings = DB::table('booking as b')
            ->leftJoin('lapangan as l', 'b.id_lapangan', '=', 'l.id_lapangan')
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
            ->where(function ($query) use ($excludedStatuses) {
                $query->whereNull('b.status_booking')
                    ->orWhereNotIn(DB::raw('LOWER(TRIM(b.status_booking))'), $excludedStatuses);
            })
            ->orderBy('b.tgl_main', 'desc')
            ->orderBy('b.jam_mulai', 'desc')
            ->get();

        // Fallback: jika booking milik $user->id kosong, cek apakah ada booking belum lunas yang berelasi ke id_pesanan di detail_items
        if ($bookings->isEmpty()) {
            $pesananIds = DB::table('detail_items')->pluck('id_pesanan')->filter()->unique()->values();
            if ($pesananIds->isNotEmpty()) {
                $bookings = DB::table('booking as b')
                    ->leftJoin('lapangan as l', 'b.id_lapangan', '=', 'l.id_lapangan')
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
                    ->whereIn('b.id_booking', $pesananIds)
                    ->where(function ($query) use ($excludedStatuses) {
                        $query->whereNull('b.status_booking')
                            ->orWhereNotIn(DB::raw('LOWER(TRIM(b.status_booking))'), $excludedStatuses);
                    })
                    ->orderBy('b.tgl_main', 'desc')
                    ->orderBy('b.jam_mulai', 'desc')
                    ->get();
            }
        }

        // 2. Susun data item tagihan (booking lapangan) dan hitung total kumulatif
        $tagihans = [];
        $totalKumulatif = 0;

        foreach ($bookings as $item) {
            $namaLapangan  = $item->nama_lapangan ?? 'Lapangan';
            $tipeLantai    = $item->tipe_lantai ?? '-';
            $hargaPerJam   = (float) ($item->harga_per_jam ?? 0);
            $durasiBooking = max(1, (int) ($item->durasi ?? 1));

            // Hitung harga sewa lapangan: utamakan harga_per_jam * durasi jika ada, lalu total_bayar
            $hargaHitung = $hargaPerJam * $durasiBooking;
            $hargaDb     = (float) ($item->total_bayar ?? 0);

            if ($hargaHitung > 0) {
                $hargaBooking = $hargaHitung;
            } elseif ($hargaDb > 0) {
                $hargaBooking = $hargaDb;
            } else {
                $hargaBooking = 0;
            }

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

        // 3. Ambil data dari detail_items lalu join ke alats & makanans
        $detailItems = DB::table('detail_items as d')
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

        foreach ($detailItems as $detail) {
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

            $tagihans[] = [
                'id_booking' => $detail->id_pesanan,
                'nama'       => $namaItem,
                'jenis'      => $jenisItem,
                'jumlah'     => $jumlahItem,
                'durasi'     => '-',
                'harga'      => $hargaItem,
                'tgl_main'   => null,
                'jam_mulai'  => null,
            ];

            $totalKumulatif += $hargaItem;
        }

        // 4. Ambil booking pertama (jika dibutuhkan di view) dan kirim ke ViewDaftarTagihan
        $booking = $bookings->first();

        return view('ViewDaftarTagihan', compact('customer', 'tagihans', 'booking', 'totalKumulatif'));
    }
}