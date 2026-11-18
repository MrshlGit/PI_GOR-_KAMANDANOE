<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RingkasanTagihanController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Ambil SEMUA data booking milik user yang berstatus 'menunggu'
        $bookings = DB::table('booking')
            ->where('id_user', $user->id)
            ->where('status_booking', 'menunggu')
            ->orderBy('tgl_main', 'desc')
            ->orderBy('jam_mulai', 'desc')
            ->get();

        $items = [];
        $subtotal = 0;

        foreach ($bookings as $item) {
            $lapangan = DB::table('lapangan')->where('id_lapangan', $item->id_lapangan)->first();

            $namaLapangan  = $lapangan->nama_lapangan ?? 'Lapangan';
            $tipeLantai    = $lapangan->tipe_lantai ?? '-';
            $hargaPerJam   = (float) ($lapangan->harga_per_jam ?? 0);
            $durasiBooking = (int) ($item->durasi ?? 1);

            // Ambil total_bayar dari database; jika nilainya 0 atau < 1000, hitung dari harga_per_jam * durasi
            $hargaDb = (float) ($item->total_bayar ?? 0);
            $hargaBooking = ($hargaDb >= 1000) ? $hargaDb : ($hargaPerJam * $durasiBooking);

            $items[] = [
                'nama'  => $namaLapangan . ' (' . $tipeLantai . ') - Tgl: ' . $item->tgl_main . ' (' . $durasiBooking . ' Jam)',
                'harga' => 'Rp ' . number_format($hargaBooking, 0, ',', '.')
            ];

            $subtotal += $hargaBooking;
        }

        $summary = [
            'tanggal'         => date('d M Y'),
            'petugas'         => 'System GOR',
            'subtotal'        => 'Rp ' . number_format($subtotal, 0, ',', '.'),
            'potongan'        => 'Rp 0',
            'ppn'             => 'Rp 0',
            'nilai_transaksi' => 'Rp ' . number_format($subtotal, 0, ',', '.'),
            'total'           => 'Rp ' . number_format($subtotal, 0, ',', '.')
        ];

        // Ambil 1 booking terbaru sebagai referensi ID jika view membutuhkannya
        $booking = $bookings->first();

        return view('ViewRingkasanTagihan', compact('items', 'summary', 'user', 'booking'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bukti_bayar' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $user = auth()->user();
        
        // Ambil booking terbaru yang statusnya 'menunggu' untuk referensi bukti bayar
        $booking = DB::table('booking')
            ->where('id_user', $user->id)
            ->where('status_booking', 'menunggu')
            ->latest('id_booking')
            ->first();

        if (!$booking) {
            return redirect()->back()->with('error', 'Tidak ada data pemesanan aktif.');
        }

        if ($request->hasFile('bukti_bayar')) {
            $file = $request->file('bukti_bayar');
            $namaFile = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/bukti_pembayaran'), $namaFile);

            DB::table('pembayaran')->insert([
                'tgl_bayar'            => now(),
                'id_detail_item_booking' => $booking->id_booking, 
                'metode_bayar'         => 'QRIS / Transfer Bank',
                'status_pembayaran'    => 'Pending',
                'bukti_transfer'       => $namaFile,
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);

            return redirect()->route('pembayaran.index')
                ->with('success', 'Bukti pembayaran berhasil dikirim! Menunggu verifikasi dari Admin.');
        }

        return redirect()->back()->with('error', 'Gagal mengunggah bukti pembayaran.');
    }
}