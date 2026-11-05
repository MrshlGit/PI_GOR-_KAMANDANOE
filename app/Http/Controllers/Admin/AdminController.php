<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Booking;
use App\Models\Admin\Lapangan;
use App\Models\User\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        $bulanIni = Booking::pendapatanBulanIni();

        $stat = [
            'pengguna' => User::count(),
            'turnamen' => DB::table('turnamen')
                ->whereDate('tgl_pelaksanaan', '>=', today())
                ->count(),
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
