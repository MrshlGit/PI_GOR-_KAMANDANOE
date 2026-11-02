<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Lapangan;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function create()
    {
        $lapangans = Lapangan::whereRaw("LOWER(COALESCE(status_lapangan, 'aktif')) != ?", ['nonaktif'])->get();

        return view('booking_view', compact('lapangans'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_lapangan' => ['required', 'string', 'exists:lapangan,id_lapangan'],
            'tanggal' => ['required', 'date'],
            'jam' => ['required', 'date_format:H:i'],
            'durasi' => ['required', 'integer', 'min:1'],
        ]);

        $lapangan = Lapangan::findOrFail($data['id_lapangan']);

        if (strtolower((string) $lapangan->status_lapangan) === 'nonaktif') {
            throw ValidationException::withMessages([
                'id_lapangan' => 'Lapangan ini sedang tidak tersedia untuk pemesanan.',
            ]);
        }

        if ($lapangan->harga_per_jam === null) {
            throw ValidationException::withMessages([
                'id_lapangan' => 'Harga lapangan ini belum diatur.',
            ]);
        }

        $totalBayar = round((float) $lapangan->harga_per_jam * (int) $data['durasi'], 2);

        Booking::create([
            'id_user' => $request->user()->getAuthIdentifier(),
            'id_lapangan' => $lapangan->getKey(),
            'tgl_main' => $data['tanggal'],
            'jam_mulai' => $data['jam'].':00',
            'durasi' => (int) $data['durasi'],
            'status_booking' => 'menunggu',
            'total_bayar' => $totalBayar,
        ]);

        return redirect()->route('booking.create')
            ->with('success', 'Booking berhasil disimpan.');
    }
}
