<?php

namespace App\Http\Controllers;

use App\Models\Dashboard;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(): View
    {
        if (Auth::check() && Auth::user()->role !== 'admin') {
            return redirect()->route('user.dashboard');
        }

        return view('dashboard', [
            'dashboard' => Dashboard::emptyOverview(),
        ]);
    }
}
