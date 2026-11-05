<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BookingFilterRequest;
use App\Http\Requests\Admin\StoreOfflineBookingRequest;
use App\Http\Requests\Admin\UpdateBookingStatusRequest;
use App\Models\Admin\Booking;
use App\Models\Admin\Lapangan;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PemesananController extends Controller
{
    public function index(BookingFilterRequest $request): View
    {
        $filter = $request->validated();

        return view('admin.pemesanan.index', [
            'bookings' => Booking::daftarAdmin($filter)
                ->orderByDesc('tgl_main')
                ->orderByDesc('jam_mulai')
                ->paginate(10)
                ->withQueryString(),
            'lapangans' => Lapangan::orderBy('nama_lapangan')->get(),
            'filter' => $filter,
            'statistik' => Booking::statistikAdmin(),
            'statusList' => Booking::STATUS,
        ]);
    }

    public function store(StoreOfflineBookingRequest $request): RedirectResponse
    {
        $data = $request->validated();

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

        $mulai = Carbon::createFromFormat('Y-m-d H:i', $data['tgl_main'].' '.$data['jam_mulai']);
        $selesai = $mulai->copy()->addHours((int) $data['durasi']);
        if (Booking::adaBentrok($lapangan->getKey(), $data['tgl_main'], $mulai, $selesai)) {
            throw ValidationException::withMessages([
                'jam_mulai' => 'Jadwal lapangan sudah terisi pada rentang waktu tersebut.',
            ]);
        }

        DB::transaction(function () use ($request, $data, $lapangan): void {
            Booking::create([
                'id_user' => $request->user()->getAuthIdentifier(),
                'id_lapangan' => $lapangan->getKey(),
                'nama_pemesan' => $data['nama_pemesan'],
                'no_hp_pemesan' => $data['no_hp_pemesan'],
                'tgl_main' => $data['tgl_main'],
                'jam_mulai' => $data['jam_mulai'].':00',
                'durasi' => (int) $data['durasi'],
                'status_booking' => 'dikonfirmasi',
                'total_bayar' => round((float) $lapangan->harga_per_jam * (int) $data['durasi'], 2),
            ]);
        });

        return redirect()->route('admin.pemesanan.index')
            ->with('sukses', 'Pemesanan offline berhasil dicatat.');
    }

    public function updateStatus(UpdateBookingStatusRequest $request, Booking $booking): RedirectResponse
    {
        $data = $request->validated();

        $booking->update($data);

        return redirect()->route('admin.pemesanan.index', $request->only(['q', 'status', 'tanggal', 'id_lapangan']))
            ->with('sukses', 'Status pemesanan berhasil diperbarui.');
    }
}
