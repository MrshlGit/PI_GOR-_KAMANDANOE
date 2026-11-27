<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class RingkasanTagihanController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $showUpload = $request->boolean('show_upload')
            || session('show_upload', false);

        // 1. Ambil semua booking milik user yang berstatus 'menunggu' beserta data lapangannya
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

        $items = [];
        $subtotal = 0;
        $bookingIds = [];

        foreach ($bookings as $item) {
            $bookingIds[] = $item->id_booking;

            $namaLapangan  = $item->nama_lapangan ?? 'Lapangan';
            $tipeLantai    = $item->tipe_lantai ?? '-';
            $hargaPerJam   = (float) ($item->harga_per_jam ?? 0);
            $durasiBooking = (int) ($item->durasi ?? 1);

            // Ambil total_bayar dari database; jika nilainya 0 atau < 1000, hitung dari harga_per_jam * durasi
            $hargaDb = (float) ($item->total_bayar ?? 0);
            $hargaBooking = ($hargaDb >= 1000) ? $hargaDb : ($hargaPerJam * $durasiBooking);

            $items[] = [
                'id_booking' => $item->id_booking,
                'nama'       => $namaLapangan,
                'jenis'      => $tipeLantai,
                'jumlah'     => 1,
                'tgl_main'   => $item->tgl_main,
                'jam_mulai'  => $item->jam_mulai,
                'durasi'     => $durasiBooking,
                'harga_raw'  => $hargaBooking,
                'harga'      => 'Rp ' . number_format($hargaBooking, 0, ',', '.'),
            ];

            $subtotal += $hargaBooking;
        }

        // 2. Ambil detail_items + join ke alats & makanans
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

            $items[] = [
                'id_booking' => $detail->id_pesanan,
                'nama'       => $namaItem,
                'jenis'      => $jenisItem,
                'jumlah'     => $jumlahItem,
                'tgl_main'   => null,
                'jam_mulai'  => null,
                'durasi'     => '-',
                'harga_raw'  => $hargaItem,
                'harga'      => 'Rp ' . number_format($hargaItem, 0, ',', '.'),
            ];

            $subtotal += $hargaItem;
        }

        $formattedSubtotal = 'Rp ' . number_format($subtotal, 0, ',', '.');

        $summary = [
            'tanggal'         => now()->format('d M Y'),
            'petugas'         => 'System GOR',
            'subtotal'        => $formattedSubtotal,
            'potongan'        => 'Rp 0',
            'ppn'             => 'Rp 0',
            'nilai_transaksi' => $formattedSubtotal,
            'total'           => $formattedSubtotal,
        ];

        $booking = $bookings->first();

        return view('ViewRingkasanTagihan', compact(
            'items',
            'summary',
            'user',
            'booking',
            'showUpload'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bukti_bayar' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $user = auth()->user();

        $bookings = DB::table('booking as b')
            ->join('lapangan as l', 'b.id_lapangan', '=', 'l.id_lapangan')
            ->select(
                'b.id_booking',
                'b.total_bayar',
                'b.durasi',
                'l.harga_per_jam'
            )
            ->where('b.id_user', $user->id)
            ->where('b.status_booking', 'menunggu')
            ->orderByDesc('b.tgl_main')
            ->orderByDesc('b.jam_mulai')
            ->get();

        $detailItems = DB::table('detail_items as d')
            ->leftJoin('alats as a', 'd.id_alat', '=', 'a.id_alat')
            ->leftJoin('makanans as m', 'd.id_makanan', '=', 'm.id_makanan')
            ->select(
                'd.id_pesanan',
                'd.jumlah',
                'd.harga_satuan',
                'd.subtotal',
                'a.harga_sewa as harga_alat',
                'm.harga_sewa as harga_makanan',
                'd.id_alat',
                'd.id_makanan'
            )
            ->get();

        if ($bookings->isEmpty() && $detailItems->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data pemesanan aktif.');
        }

        // Hitung total detail_items per id_pesanan (jika ada)
        $subtotalDetailByPesanan = [];
        $totalSemuaDetail = 0;

        foreach ($detailItems as $detail) {
            $jumlahItem  = max(1, (int) ($detail->jumlah ?? 1));
            $subtotalDb  = (float) ($detail->subtotal ?? 0);
            $hargaSatuan = (float) ($detail->harga_satuan ?? 0);
            $hargaRef    = !is_null($detail->id_makanan)
                ? (float) ($detail->harga_makanan ?? 0)
                : (float) ($detail->harga_alat ?? 0);

            $hargaItem = $subtotalDb > 0
                ? $subtotalDb
                : ($hargaSatuan > 0 ? $hargaSatuan * $jumlahItem : $hargaRef * $jumlahItem);

            $idPesanan = $detail->id_pesanan;
            $subtotalDetailByPesanan[$idPesanan] = ($subtotalDetailByPesanan[$idPesanan] ?? 0) + $hargaItem;
            $totalSemuaDetail += $hargaItem;
        }

        $file = $request->file('bukti_bayar');
        $directory = public_path('uploads/bukti_pembayaran');
        File::ensureDirectoryExists($directory);
        $namaFile = $file->hashName();
        $file->move($directory, $namaFile);

        $idPembayaran = DB::transaction(function () use ($bookings, $subtotalDetailByPesanan, $totalSemuaDetail, $namaFile): string {
            $idBayar = (string) Str::uuid();
            $idDetailPertama = null;

            if ($bookings->isNotEmpty()) {
                foreach ($bookings as $index => $booking) {
                    $hargaPerJam   = (float) ($booking->harga_per_jam ?? 0);
                    $durasiBooking = max(1, (int) ($booking->durasi ?? 1));
                    $hargaDb       = (float) ($booking->total_bayar ?? 0);
                    $hargaBooking  = ($hargaDb >= 1000) ? $hargaDb : ($hargaPerJam * $durasiBooking);

                    $tambahanDetail = $subtotalDetailByPesanan[$booking->id_booking] ?? 0;
                    $subtotalItem   = $hargaBooking + $tambahanDetail;

                    $idDetail = (string) Str::uuid();
                    if ($index === 0) {
                        $idDetailPertama = $idDetail;
                    }

                    DB::table('detail_item_booking')->insert([
                        'id_detail'  => $idDetail,
                        'id_booking' => $booking->id_booking,
                        'subtotal'   => $subtotalItem,
                        'id_produk'  => null,
                        'kuantitas'  => $durasiBooking,
                    ]);

                    DB::table('booking')
                        ->where('id_booking', $booking->id_booking)
                        ->update([
                            'status_booking' => 'menunggu_verifikasi',
                            'total_bayar'    => $subtotalItem,
                        ]);
                }
            } else {
                // Jika tidak ada booking aktif, catat pembayaran dari detail_items
                $idDetailPertama = (string) Str::uuid();
                DB::table('detail_item_booking')->insert([
                    'id_detail'  => $idDetailPertama,
                    'id_booking' => null,
                    'subtotal'   => $totalSemuaDetail,
                    'id_produk'  => null,
                    'kuantitas'  => 1,
                ]);
            }

            DB::table('pembayaran')->insert([
                'id_pembayaran'          => $idBayar,
                'tgl_bayar'              => now()->toDateString(),
                'id_detail_item_booking' => $idDetailPertama,
                'metode_bayar'           => 'QRIS / Transfer Bank',
                'status_pembayaran'      => 'Pending',
                'bukti_transfer'         => $namaFile,
            ]);

            return $idBayar;
        });

        return redirect()->route('pembayaran.menunggu', ['id' => $idPembayaran])
            ->with('success', 'Bukti pembayaran berhasil dikirim! Menunggu verifikasi dari Admin.');
    }
}