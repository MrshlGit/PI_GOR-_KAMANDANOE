<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Lapangan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if (Auth::user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        $lapangans = Lapangan::whereRaw("LOWER(COALESCE(status_lapangan, 'aktif')) != ?", ['nonaktif'])
            ->orderBy('id_lapangan')
            ->get();

        return view('dashboardUser', compact('lapangans'));
    }
}
