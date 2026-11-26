<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Booking;
use App\Models\Admin\Lapangan;
use App\Models\Admin\Turnamen;
use App\Models\User\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        $statistik = [
            'total_pengguna' => User::query()->count(),
            'turnamen_aktif' => Turnamen::jumlahMendatang(),
            'pendapatan_bulan_ini' => Booking::pendapatanBulanIni(),
            'pemesanan_baru' => Booking::jumlahMenunggu(),
        ];

        return view('admin.dashboard', [
            'statistik' => $statistik,
            'booking' => Booking::terbaru(6),
            'lapangan' => Lapangan::denganStatus(),
            'grafik' => Booking::grafik('hari'),
        ]);
    }

    public function grafik(Request $request): JsonResponse
    {
        return response()->json(Booking::grafik((string) $request->query('periode', 'hari')));
    }
}
