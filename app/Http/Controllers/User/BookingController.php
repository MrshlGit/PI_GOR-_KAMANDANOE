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

        return view('lapangan_view', compact('lapangans'));
    }

    // public function create1()
    // {
    //     return view('booking_view');
    // }

    // public function create2()
    // {
    //     return view('alat_view');
    // }

    // public function create3()
    // {
    //     return view('makanan_view');
    // }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_lapangan' => ['required', 'string', 'exists:lapangan,id_lapangan'],
            'tanggal' => ['required', 'date'],
            'jam' => ['required', 'date_format:H:i'],
            'durasi' => ['required', 'integer', 'min:1', 'max:16'],
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

        $mulai = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $data['tanggal'].' '.$data['jam']);
        $selesai = $mulai->copy()->addHours((int) $data['durasi']);
        $bentrok = Booking::where('id_lapangan', $lapangan->getKey())
            ->whereDate('tgl_main', $data['tanggal'])
            ->whereRaw('LOWER(status_booking) NOT IN (?, ?, ?)', ['batal', 'dibatalkan', 'cancelled'])
            ->get(['jam_mulai', 'durasi'])
            ->contains(function (Booking $booking) use ($mulai, $selesai, $data): bool {
                $mulaiBooking = \Carbon\Carbon::parse($data['tanggal'].' '.$booking->jam_mulai);
                $selesaiBooking = $mulaiBooking->copy()->addHours(max(1, (int) $booking->durasi));

                return $mulai->lt($selesaiBooking) && $selesai->gt($mulaiBooking);
            });

        if ($bentrok) {
            throw ValidationException::withMessages([
                'jam' => 'Jadwal lapangan sudah terisi pada rentang waktu tersebut.',
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
