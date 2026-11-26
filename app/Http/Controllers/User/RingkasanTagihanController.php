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

        // Variabel $errors hanya tersedia di Blade view, bukan di dalam controller
        $showUpload = $request->boolean('show_upload')
            || session('show_upload', false);

        // Ambil semua booking milik user yang berstatus 'menunggu' beserta data lapangannya
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

        foreach ($bookings as $item) {
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
                'tgl_main'   => $item->tgl_main,
                'jam_mulai'  => $item->jam_mulai,
                'durasi'     => $durasiBooking,
                'harga_raw'  => $hargaBooking,
                'harga'      => 'Rp ' . number_format($hargaBooking, 0, ',', '.'),
            ];

            $subtotal += $hargaBooking;
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

        if ($bookings->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data pemesanan aktif.');
        }

        $file = $request->file('bukti_bayar');
        $directory = public_path('uploads/bukti_pembayaran');
        File::ensureDirectoryExists($directory);
        $namaFile = $file->hashName();
        $file->move($directory, $namaFile);

        $idPembayaran = DB::transaction(function () use ($bookings, $namaFile): string {
            $idBayar = (string) Str::uuid();
            $idDetailPertama = null;

            foreach ($bookings as $index => $booking) {
                $hargaPerJam = (float) ($booking->harga_per_jam ?? 0);
                $durasiBooking = max(1, (int) ($booking->durasi ?? 1));
                $hargaDb = (float) ($booking->total_bayar ?? 0);
                $subtotalItem = ($hargaDb >= 1000) ? $hargaDb : ($hargaPerJam * $durasiBooking);

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