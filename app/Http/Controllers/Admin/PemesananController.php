<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Booking;
use App\Models\Admin\Lapangan;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PemesananController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(Booking::STATUS)],
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
            'id_lapangan' => ['nullable', 'string', 'exists:lapangan,id_lapangan'],
        ]);

        $query = Booking::with(['user:id,username', 'lapangan:id_lapangan,nama_lapangan']);

        if (! empty($filter['q'])) {
            $term = $filter['q'];
            $query->where(function ($bookings) use ($term) {
                $bookings->where('id_booking', 'like', "%{$term}%")
                    ->orWhere('nama_pemesan', 'like', "%{$term}%")
                    ->orWhere('no_hp_pemesan', 'like', "%{$term}%")
                    ->orWhereHas('user', fn ($users) => $users->where('username', 'like', "%{$term}%"));
            });
        }

        if (! empty($filter['status'])) {
            $query->whereRaw('LOWER(status_booking) = ?', [$filter['status']]);
        }

        if (! empty($filter['tanggal'])) {
            $query->whereDate('tgl_main', $filter['tanggal']);
        }

        if (! empty($filter['id_lapangan'])) {
            $query->where('id_lapangan', $filter['id_lapangan']);
        }

        $dibatalkan = ['batal', 'dibatalkan', 'cancelled'];

        return view('admin.pemesanan.index', [
            'bookings' => $query->orderByDesc('tgl_main')->orderByDesc('jam_mulai')->paginate(10)->withQueryString(),
            'lapangans' => Lapangan::orderBy('nama_lapangan')->get(),
            'filter' => $filter,
            'statistik' => [
                'total' => Booking::count(),
                'menunggu' => Booking::whereRaw('LOWER(status_booking) = ?', ['menunggu'])->count(),
                'hari_ini' => Booking::whereDate('tgl_main', today())->count(),
                'pendapatan' => (float) Booking::whereRaw('LOWER(status_booking) NOT IN (?, ?, ?)', $dibatalkan)
                    ->whereMonth('tgl_main', now()->month)
                    ->whereYear('tgl_main', now()->year)
                    ->sum('total_bayar'),
            ],
            'statusList' => Booking::STATUS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama_pemesan' => ['required', 'string', 'max:100'],
            'no_hp_pemesan' => ['required', 'string', 'max:20'],
            'id_lapangan' => ['required', 'string', 'exists:lapangan,id_lapangan'],
            'tgl_main' => ['required', 'date_format:Y-m-d'],
            'jam_mulai' => ['required', 'date_format:H:i'],
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

        $mulai = Carbon::createFromFormat('Y-m-d H:i', $data['tgl_main'].' '.$data['jam_mulai']);
        $selesai = $mulai->copy()->addHours((int) $data['durasi']);
        $bentrok = Booking::where('id_lapangan', $lapangan->getKey())
            ->whereDate('tgl_main', $data['tgl_main'])
            ->whereRaw('LOWER(status_booking) NOT IN (?, ?, ?)', ['batal', 'dibatalkan', 'cancelled'])
            ->get(['jam_mulai', 'durasi'])
            ->contains(function (Booking $booking) use ($mulai, $selesai, $data): bool {
                $mulaiBooking = Carbon::parse($data['tgl_main'].' '.$booking->jam_mulai);
                $selesaiBooking = $mulaiBooking->copy()->addHours(max(1, (int) $booking->durasi));

                return $mulai->lt($selesaiBooking) && $selesai->gt($mulaiBooking);
            });

        if ($bentrok) {
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

    public function updateStatus(Request $request, Booking $booking): RedirectResponse
    {
        $data = $request->validate([
            'status_booking' => ['required', Rule::in(Booking::STATUS)],
        ]);

        $booking->update($data);

        return redirect()->route('admin.pemesanan.index', $request->only(['q', 'status', 'tanggal', 'id_lapangan']))
            ->with('sukses', 'Status pemesanan berhasil diperbarui.');
    }
}
