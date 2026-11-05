<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class pilihBookingController extends Controller
{
    public function create()
    {
        return view('pilihBook_view');
    }

    public function create1()
    {
        return view('lapangan_view');
    }

    public function create2()
    {
        return view('alat_view');
    }

    public function create3()
    {
        return view('makanan_view');
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'tujuan' => ['required', 'in:lapangan,alat,makanan']
        ]);
        
        if($data['tujuan']){
            if($data['tujuan'] === 'lapangan'){
                // return redirect()->route('lapangan_view');
                return redirect()->route('booking.create');

            } else if($data['tujuan'] === 'alat'){
                return redirect()->route('alat_view');

            } else if($data['tujuan'] === 'makanan'){
                return redirect()->route('makanan_view');
            }
        }
    }
}
