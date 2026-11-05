<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Lapangan extends Model
{
    protected $table = 'lapangan';

    protected $primaryKey = 'id_lapangan';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'id_lapangan',
        'tipe_lantai',
        'harga_per_jam',
        'status_lapangan',
        'nama_lapangan',
        'kapasitas',
    ];

    public function booking(): HasMany
    {
        return $this->hasMany(Booking::class, 'id_lapangan', 'id_lapangan');
    }

    public static function jadwalUntukTanggal(string $tanggal, array $jam): array
    {
        $jadwal = [];
        $jamTersedia = array_fill_keys($jam, true);

        $bookings = Booking::with('user:id,username')
            ->whereDate('tgl_main', $tanggal)
            ->tidakDibatalkan()
            ->orderBy('jam_mulai')
            ->get();

        foreach ($bookings as $booking) {
            $mulai = (int) substr((string) $booking->jam_mulai, 0, 2);

            for ($offset = 0; $offset < max(1, (int) $booking->durasi); $offset++) {
                $slot = $mulai + $offset;

                if (isset($jamTersedia[$slot])) {
                    $jadwal[$booking->id_lapangan][$slot][] = $booking;
                }
            }
        }

        return $jadwal;
    }

    public static function denganStatus(): Collection
    {
        $sekarang = now();
        $idLapanganDipakai = Booking::whereDate('tgl_main', $sekarang->toDateString())
            ->tidakDibatalkan()
            ->get(['id_lapangan', 'jam_mulai', 'durasi'])
            ->filter(function (Booking $booking) use ($sekarang): bool {
                $mulai = $sekarang->copy()->setTimeFromTimeString((string) $booking->jam_mulai);
                $selesai = $mulai->copy()->addHours(max(1, (int) $booking->durasi));

                return $sekarang->greaterThanOrEqualTo($mulai) && $sekarang->lessThan($selesai);
            })
            ->pluck('id_lapangan')
            ->unique();

        return static::orderBy('id_lapangan')->get()->each(
            fn (self $lapangan) => $lapangan->setAttribute(
                'dipakai',
                $idLapanganDipakai->contains($lapangan->getKey())
            )
        );
    }
}
