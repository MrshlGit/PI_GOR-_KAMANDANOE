<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Makanan;

class MakananController extends Controller
{
    public function ambil(){
      
        $makanans = Makanan::where('aktif', true)->get();
        return view('makanan_view', compact('makanans'));
    
    }
}
