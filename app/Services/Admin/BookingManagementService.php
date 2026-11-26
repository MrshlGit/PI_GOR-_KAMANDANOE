<?php

namespace App\Services\Admin;

use App\Models\Admin\Booking;
use App\Models\Admin\Lapangan;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingManagementService
{
    public function createOffline(array $data, User $admin): Booking
    {
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

        return DB::transaction(fn (): Booking => Booking::create([
            'id_user' => $admin->getAuthIdentifier(),
            'id_lapangan' => $lapangan->getKey(),
            'nama_pemesan' => $data['nama_pemesan'],
            'no_hp_pemesan' => $data['no_hp_pemesan'],
            'tgl_main' => $data['tgl_main'],
            'jam_mulai' => $data['jam_mulai'].':00',
            'durasi' => (int) $data['durasi'],
            'status_booking' => 'dikonfirmasi',
            'total_bayar' => round((float) $lapangan->harga_per_jam * (int) $data['durasi'], 2),
        ]));
    }

    public function updatePendingStatus(Booking $booking, string $status): bool
    {
        return DB::transaction(function () use ($booking, $status): bool {
            $lockedBooking = Booking::whereKey($booking->getKey())->lockForUpdate()->firstOrFail();

            if (strtolower((string) $lockedBooking->status_booking) !== 'menunggu') {
                return false;
            }

            $lockedBooking->update(['status_booking' => $status]);

            return true;
        });
    }
}
