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
        $bulanIni = Booking::pendapatanBulanIni();

        $stat = [
            'pengguna' => User::query()->count(),
            'turnamen' => Turnamen::jumlahMendatang(),
            'pendapatan' => $bulanIni >= 1000000
                ? 'Rp '.number_format($bulanIni / 1000000, 1, ',', '.').' jt'
                : 'Rp '.number_format($bulanIni, 0, ',', '.'),
            'baru' => Booking::jumlahMenunggu(),
        ];

        return view('admin.dashboard', [
            'stat' => $stat,
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
