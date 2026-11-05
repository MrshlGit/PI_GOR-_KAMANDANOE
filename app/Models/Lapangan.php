<?php

namespace App\Models;

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

    public function sedangDipakai(): bool
    {
        $sekarang = now();

        return $this->booking()
            ->whereDate('tgl_main', $sekarang->toDateString())
            ->whereRaw('LOWER(status_booking) NOT IN (?, ?, ?)', ['batal', 'dibatalkan', 'cancelled'])
            ->get(['jam_mulai', 'durasi'])
            ->contains(function (Booking $booking) use ($sekarang): bool {
                $mulai = $sekarang->copy()->setTimeFromTimeString((string) $booking->jam_mulai);
                $selesai = $mulai->copy()->addHours(max(1, (int) $booking->durasi));

                return $sekarang->greaterThanOrEqualTo($mulai) && $sekarang->lessThan($selesai);
            });
    }

    public static function denganStatus(): Collection
    {
        return static::orderBy('id_lapangan')->get()->each(
            fn (self $lapangan) => $lapangan->setAttribute('dipakai', $lapangan->sedangDipakai())
        );
    }
}
