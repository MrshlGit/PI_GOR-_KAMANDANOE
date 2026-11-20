<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SaranKritik;
use Illuminate\View\View;

class SaranKritikController extends Controller
{
    public function index(): View
    {
        return view('admin.saran-kritik.index', [
            'saranKritik' => SaranKritik::query()
                ->with('user:id,username')
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->paginate(15),
        ]);
    }
}
