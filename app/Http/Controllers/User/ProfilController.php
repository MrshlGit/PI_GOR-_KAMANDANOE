<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Admin\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfilController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        // query dasar: semua booking milik user yang sedang login
        $milik = fn () => Booking::where('id_user', $user->id);
        $status = fn (array $daftar) => $milik()->whereIn(DB::raw('LOWER(status_booking)'), $daftar);

        $ringkasan = [
            'total' => $milik()->count(),
            'aktif' => $status(['menunggu', 'dikonfirmasi'])->count(),
            'selesai' => $status(['lunas', 'selesai'])->count(),
            'belanja' => (float) $status(['lunas', 'selesai'])->sum('total_bayar'),
        ];

        $terakhir = $milik()
            ->with('lapangan')
            ->orderByDesc('tgl_main')
            ->orderByDesc('jam_mulai')
            ->limit(5)
            ->get();

        return view('profil_view', [
            'user' => $user,
            'ringkasan' => $ringkasan,
            'terakhir' => $terakhir,
        ]);
    }
}