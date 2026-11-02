<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Booking;
use App\Models\Admin\Lapangan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LapanganController extends Controller
{
    public function index(Request $request): View
    {
        $tanggal = $request->validate([
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
        ])['tanggal'] ?? today()->toDateString();

        $lapangans = Lapangan::orderBy('id_lapangan')->get();
        $jam = range(7, 22);
        $jadwal = [];
        $bookings = Booking::with('user:id,username')
            ->whereDate('tgl_main', $tanggal)
            ->whereRaw('LOWER(status_booking) NOT IN (?, ?, ?)', ['batal', 'dibatalkan', 'cancelled'])
            ->orderBy('jam_mulai')
            ->get();

        foreach ($bookings as $booking) {
            $nama = $booking->user?->username ?? 'User #' . $booking->id_user;
            $mulai = (int) substr((string) $booking->jam_mulai, 0, 2);

            for ($offset = 0; $offset < max(1, (int) $booking->durasi); $offset++) {
                $slot = $mulai + $offset;

                if ($slot >= 7 && $slot <= 22) {
                    $jadwal[$booking->id_lapangan][$slot][] = $nama;
                }
            }
        }

        $statistik = [
            'total' => $lapangans->count(),
            'aktif' => $lapangans->filter(fn (Lapangan $lapangan) => strtolower((string) $lapangan->status_lapangan) !== 'nonaktif')->count(),
            'terpesan' => collect($jadwal)->sum(
                fn (array $lapangan) => count($lapangan)
            ),
        ];

        return view('admin.lapangan.index', compact('tanggal', 'jam', 'jadwal', 'lapangans', 'statistik'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('tambah', [
            'id_lapangan' => ['required', 'string', 'max:11', Rule::unique('lapangan', 'id_lapangan')],
            'nama_lapangan' => ['required', 'string', 'max:20'],
            'tipe_lantai' => ['required', 'string', 'max:100'],
            'harga_per_jam' => ['required', 'numeric', 'min:0'],
            'kapasitas' => ['required', 'integer', 'min:1', 'max:32'],
            'status_lapangan' => ['required', Rule::in(['Aktif', 'Nonaktif'])],
        ]);

        Lapangan::create($data);

        return redirect()->route('admin.lapangan.index')
            ->with('sukses', 'Lapangan berhasil ditambahkan.');
    }

    public function update(Request $request, Lapangan $lapangan): RedirectResponse
    {
        $data = $request->validateWithBag('ubah', [
            'nama_lapangan' => ['required', 'string', 'max:20'],
            'tipe_lantai' => ['required', 'string', 'max:100'],
            'harga_per_jam' => ['required', 'numeric', 'min:0'],
            'kapasitas' => ['required', 'integer', 'min:1', 'max:32'],
            'status_lapangan' => ['required', Rule::in(['Aktif', 'Nonaktif'])],
        ]);

        $lapangan->update($data);

        return redirect()->route('admin.lapangan.index')
            ->with('sukses', 'Konfigurasi lapangan berhasil diperbarui.');
    }

    public function destroy(Lapangan $lapangan): RedirectResponse
    {
        if (Booking::where('id_lapangan', $lapangan->getKey())->exists()) {
            return redirect()->route('admin.lapangan.index')
                ->with('galat', 'Lapangan tidak dapat dihapus karena memiliki riwayat pemesanan. Nonaktifkan lapangan jika tidak digunakan lagi.');
        }

        DB::transaction(fn () => $lapangan->delete());

        return redirect()->route('admin.lapangan.index')
            ->with('sukses', 'Lapangan berhasil dihapus.');
    }
}