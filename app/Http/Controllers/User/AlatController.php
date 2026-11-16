<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Alat;

class AlatController extends Controller
{
    public function ambil()
    {
        $alats = Alat::where('aktif', true)->get();
        return view('alat_view', compact('alats'));
    }
}
