<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Admin\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function create()
    {
        return view('booking_view');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'lapangan' => ['required', 'string'],
            'tanggal' => ['required', 'date'],
            'jam' => ['required', 'integer'],
            'durasi' => ['required', 'integer', 'min:1'],
        ]);

        $hargaPerJam = 50000;

        Booking::create([
            'lapangan' => $data['lapangan'],
            'tanggal' => $data['tanggal'],
            'jam' => $data['jam'],
            'durasi' => $data['durasi'],
            'harga' => $hargaPerJam,
            'total_bayar' => $hargaPerJam * $data['durasi'],
        ]);

        return redirect()->route('booking.create')
            ->with('success', 'Booking berhasil disimpan.');
    }
}
