<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        $bulanIni = DB::table('booking')
            ->whereMonth('tgl_main', now()->month)
            ->whereYear('tgl_main', now()->year)
            ->sum('total_bayar');

        $stat = [
            'pengguna' => User::count(),
            'turnamen' => 0, // belum ada tabel turnamen
            'pendapatan' => $bulanIni >= 1000000
                ? 'Rp ' . number_format($bulanIni / 1000000, 1, ',', '.') . ' jt'
                : 'Rp ' . number_format($bulanIni, 0, ',', '.'),
            'baru' => DB::table('booking')
                ->whereRaw('LOWER(status_booking) = ?', ['menunggu'])
                ->count(),
        ];

        $booking = DB::table('booking')
            ->leftJoin('user', 'user.id', '=', 'booking.id_user')
            ->select('booking.*', 'user.username')
            ->orderByDesc('booking.tgl_main')
            ->limit(6)
            ->get();

        // sementara data contoh (nanti diganti dari database)
        $lapangan = [['Lapangan A', true], ['Lapangan B', true], ['Lapangan C', true], ['Lapangan D', true]];
        $trafik = [
            ['Senin', [90, 60, 28]],
            ['Selasa', [90, 60, 28]],
            ['Rabu', [90, 60, 28]],
            ['Kamis', [90, 60, 28]],
            ['Jumat', [90, 60, 28]],
            ['Sabtu', [90, 60, 28]],
            ['Minggu', [90, 60, 28]],
        ];

        return view('admin.dashboard', compact('stat', 'booking', 'lapangan', 'trafik'));
    }
}