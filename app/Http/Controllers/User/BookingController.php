<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Lapangan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function create(Request $request)
    {
        $data = $request->validate([
            'tanggal' => ['nullable', 'date'],
        ]);

        $tanggal = Carbon::parse($data['tanggal'] ?? today()->toDateString())->startOfDay();
        $lapangans = Lapangan::orderBy('id_lapangan')->get();
        $lapanganBooking = $lapangans->filter(
            fn (Lapangan $lapangan) => strtolower((string) $lapangan->status_lapangan) !== 'nonaktif'
        );
        $jadwal = [];
        $prioritas = [
            'kosong' => 0,
            'menunggu' => 1,
            'terisi' => 2,
            'saya' => 3,
        ];

        for ($jam = 8; $jam < 22; $jam++) {
            foreach ($lapangans as $lapangan) {
                $jadwal[$jam][$lapangan->id_lapangan] =
                    strtolower((string) $lapangan->status_lapangan) === 'nonaktif'
                    ? 'maintenance'
                    : 'kosong';
            }
        }

        $bookings = Booking::whereDate('tgl_main', $tanggal->toDateString())
            ->whereRaw('LOWER(status_booking) NOT IN (?, ?, ?)', ['batal', 'dibatalkan', 'cancelled'])
            ->get(['id_user', 'id_lapangan', 'jam_mulai', 'durasi', 'status_booking']);

        $idUser = $request->user()?->getAuthIdentifier();

        foreach ($bookings as $booking) {
            $jamMulai = (int) substr((string) $booking->jam_mulai, 0, 2);
            $status = $idUser !== null && $booking->id_user == $idUser
                ? 'saya'
                : (strtolower((string) $booking->status_booking) === 'menunggu' ? 'menunggu' : 'terisi');

            for ($jam = $jamMulai; $jam < $jamMulai + max(1, (int) $booking->durasi); $jam++) {
                if (! isset($jadwal[$jam][$booking->id_lapangan])) {
                    continue;
                }

                $statusLama = $jadwal[$jam][$booking->id_lapangan];
                if ($statusLama !== 'maintenance' && $prioritas[$status] > $prioritas[$statusLama]) {
                    $jadwal[$jam][$booking->id_lapangan] = $status;
                }
            }
        }

        $labelStatus = [
            'kosong' => 'Tersedia',
            'menunggu' => 'Menunggu',
            'terisi' => 'Terisi',
            'saya' => 'Booking saya',
            'maintenance' => 'Maintenance',
        ];

        return view('lapangan_view', [
            'lapangans' => $lapangans,
            'lapanganBooking' => $lapanganBooking,
            'tanggal' => $tanggal,
            'labelTanggal' => $tanggal->copy()->locale('id')->translatedFormat('l, j F Y'),
            'jadwal' => $jadwal,
            'labelStatus' => $labelStatus,
        ]);
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
            'tanggal' => ['required', 'date', 'after_or_equal:today'],
            'jam' => ['required', 'date_format:H:i'],
            'durasi' => ['required', 'integer', 'min:1', 'max:16'],
        ]);

        $mulaiBaru = Carbon::createFromFormat( #memberi tahu cara membaca format yang dibuat sendiri, mirip seperti perse
            'Y-m-d H:i',
            $data['tanggal'].' '.$data['jam']
        );

        if ($mulaiBaru->lessThanOrEqualTo(now())) {
            throw ValidationException::withMessages([
                'jam' => 'Jam mulai booking harus setelah waktu sekarang.',
            ]);
        }

        $berhasil = DB::transaction(function () use ($data, $mulaiBaru, $request): bool {
            $lapangan = Lapangan::query()
                ->whereKey($data['id_lapangan'])
                ->lockForUpdate()
                ->firstOrFail();

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

            $selesaiBaru = $mulaiBaru->copy()->addHours((int) $data['durasi']);
            $bookingLama = Booking::where('id_lapangan', $lapangan->getKey())
                ->whereDate('tgl_main', $data['tanggal'])
                ->whereRaw('LOWER(status_booking) NOT IN (?, ?, ?)', [
                    'batal', 'dibatalkan', 'cancelled',
                ])
                ->get(['jam_mulai', 'durasi']);

            foreach ($bookingLama as $booking) {
                $mulaiLama = Carbon::parse($data['tanggal'].' '.$booking->jam_mulai);
                $selesaiLama = $mulaiLama->copy()
                    ->addHours(max(1, (int) $booking->durasi));

                if ($mulaiBaru < $selesaiLama && $selesaiBaru > $mulaiLama) {
                    return false;
                }
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

            return true;
        });

        if (! $berhasil) {
            return redirect()->route('booking.create', ['tanggal' => $data['tanggal']])
                ->withInput()
                ->with('booking_result', [
                    'type' => 'full',
                    'title' => 'Jadwal sudah penuh',
                    'message' => 'Lapangan sudah memiliki booking pada waktu yang dipilih. Silakan pilih jam atau tanggal lain.',
                ]);
        }

        return redirect()->route('booking.create', ['tanggal' => $data['tanggal']])
            ->with('booking_result', [
                'type' => 'success',
                'title' => 'Booking berhasil!',
                'message' => 'Booking Anda berhasil dikirim dan sedang menunggu konfirmasi admin.',
            ]);
    }
}
