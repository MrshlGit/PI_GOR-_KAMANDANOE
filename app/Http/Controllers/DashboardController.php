<?php

namespace App\Http\Controllers;

use App\Models\Dashboard;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('dashboard', [
            'dashboard' => Dashboard::emptyOverview(),
        ]);
    }
}
