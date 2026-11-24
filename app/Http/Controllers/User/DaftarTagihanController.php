<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

        $bookings = DB::table('booking')
            ->where('id_user', $user->id)
            ->orderBy('tgl_main', 'desc')
            ->orderBy('jam_mulai', 'desc')
            ->get();

        $paymentByBooking = [];
        $bookingIds = $bookings->pluck('id_booking')->all();
        if ($bookingIds !== [] && Schema::hasTable('pembayaran') && Schema::hasTable('detail_item_booking')) {
            $payments = DB::table('pembayaran as payment')
                ->join(
                    'detail_item_booking as booking_detail',
                    'booking_detail.id_detail',
                    '=',
                    'payment.id_detail_item_booking'
                )
                ->whereIn('booking_detail.id_booking', $bookingIds)
                ->orderByDesc('payment.tgl_bayar')
                ->orderByDesc('payment.id_pembayaran')
                ->get([
                    'booking_detail.id_booking',
                    'payment.status_pembayaran',
                ]);

            foreach ($payments as $payment) {
                $paymentByBooking[(string) $payment->id_booking] ??= $payment->status_pembayaran;
            }
        }

        $tagihans = [];
        $totalKumulatif = 0;
        $jumlahBelumDibayar = 0;

        foreach ($bookings as $item) {
            $lapangan = DB::table('lapangan')->where('id_lapangan', $item->id_lapangan)->first();

            // Definisi lengkap variabel dari tabel lapangan & booking
            $namaLapangan  = $lapangan->nama_lapangan ?? 'Lapangan';
            $tipeLantai    = $lapangan->tipe_lantai ?? '-';
            $hargaPerJam   = (float) ($lapangan->harga_per_jam ?? 0);
            $durasiBooking = (int) ($item->durasi ?? 1);

            // Ambil total_bayar; jika nilainya 0 atau < 1000, hitung ulang dari harga_per_jam * durasi
            $hargaDb = (float) ($item->total_bayar ?? 0);
            $hargaBooking = ($hargaDb >= 1000) ? $hargaDb : ($hargaPerJam * $durasiBooking);
            $statusBooking = strtolower((string) $item->status_booking);
            $statusPembayaran = strtolower((string) ($paymentByBooking[(string) $item->id_booking] ?? ''));
            $belumDibayar = $statusBooking === 'menunggu' && $statusPembayaran === '';
            $status = match (true) {
                in_array($statusBooking, ['batal', 'dibatalkan', 'cancelled'], true) => 'Dibatalkan',
                $statusPembayaran === 'pending' => 'Menunggu verifikasi admin',
                in_array($statusPembayaran, ['lunas', 'terverifikasi', 'disetujui', 'approved', 'paid', 'success', 'berhasil'], true) => 'Terverifikasi',
                $belumDibayar => 'Belum dibayar',
                default => ucfirst($item->status_booking),
            };

            $tagihans[] = [
                'nama'   => $namaLapangan . ' (' . $tipeLantai . ') - ' . $item->tgl_main,
                'jenis'  => 'Lapangan GOR',
                'jumlah' => 1,
                'durasi' => $durasiBooking,
                'harga'  => $hargaBooking,
                'status' => $status,
                'status_class' => $statusPembayaran === 'pending'
                    ? 'waiting'
                    : ($status === 'Terverifikasi' ? 'verified' : ($belumDibayar ? 'unpaid' : '')),
            ];

            if ($belumDibayar) {
                $jumlahBelumDibayar++;
                $totalKumulatif += $hargaBooking;
            }
        }

        return view('ViewDaftarTagihan', compact(
            'customer',
            'tagihans',
            'totalKumulatif',
            'jumlahBelumDibayar'
        ));
    }
}