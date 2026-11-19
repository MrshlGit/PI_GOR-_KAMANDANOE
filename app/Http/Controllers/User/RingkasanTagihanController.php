<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class RingkasanTagihanController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Ambil data booking terakhir milik user
        $booking = DB::table('booking')->where('id_user', $user->id)->latest('id_booking')->first();

        // Ambil harga dari berbagai kemungkinan nama kolom di database agar tidak 0
        $totalHarga = 0;
        if ($booking) {
            $totalHarga = $booking->total_bayar ?? $booking->total ?? $booking->harga ?? $booking->biaya ?? 0;
        }

        $items = $booking ? [
            [
                'nama'  => 'Pemesanan Lapangan (ID: ' . ($booking->id_lapangan ?? '1') . ') - Tgl: ' . ($booking->tgl_main ?? date('Y-m-d')), 
                'harga' => 'Rp ' . number_format($totalHarga, 0, ',', '.')
            ]
        ] : [];

        $summary = [
            'tanggal'         => date('d M Y'),
            'petugas'         => 'System GOR',
            'subtotal'        => 'Rp ' . number_format($totalHarga, 0, ',', '.'),
            'potongan'        => 'Rp 0',
            'ppn'             => 'Rp 0',
            'nilai_transaksi' => 'Rp ' . number_format($totalHarga, 0, ',', '.'),
            'total'           => 'Rp ' . number_format($totalHarga, 0, ',', '.')
        ];

        return view('ViewRingkasanTagihan', compact('items', 'summary', 'user', 'booking'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bukti_bayar' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $user = auth()->user();
        $booking = DB::table('booking')->where('id_user', $user->id)->latest('id_booking')->first();

        if (!$booking) {
            return redirect()->back()->with('error', 'Tidak ada data pemesanan aktif.');
        }

        if ($request->hasFile('bukti_bayar')) {
            $file = $request->file('bukti_bayar');
            $directory = public_path('uploads/bukti_pembayaran');
            File::ensureDirectoryExists($directory);
            $namaFile = $file->hashName();
            $file->move($directory, $namaFile);

            DB::transaction(function () use ($booking, $namaFile): void {
                $idDetail = (string) Str::uuid();

                DB::table('detail_item_booking')->insert([
                    'id_detail' => $idDetail,
                    'id_booking' => $booking->id_booking,
                    'subtotal' => $booking->total_bayar,
                    'id_produk' => null,
                    'kuantitas' => max(1, (int) $booking->durasi),
                ]);

                DB::table('pembayaran')->insert([
                    'id_pembayaran' => (string) Str::uuid(),
                    'tgl_bayar' => now()->toDateString(),
                    'id_detail_item_booking' => $idDetail,
                    'metode_bayar' => 'QRIS / Transfer Bank',
                    'status_pembayaran' => 'Pending',
                    'bukti_transfer' => $namaFile,
                ]);
            });

            return redirect()->route('pembayaran.index')
                ->with('success', 'Bukti pembayaran berhasil dikirim! Menunggu verifikasi dari Admin.');
        }

        return redirect()->back()->with('error', 'Gagal mengunggah bukti pembayaran.');
    }
}