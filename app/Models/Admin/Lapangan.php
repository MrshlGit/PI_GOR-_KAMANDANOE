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
            ->where('jam_mulai', '<=', $sekarang->format('H:i:s'))
            ->where('jam_mulai', '>', $sekarang->copy()->subHour()->format('H:i:s'))
            ->whereRaw('LOWER(status_booking) != ?', ['batal'])
            ->exists();
    }

    public static function denganStatus(): Collection
    {
        return static::orderBy('id_lapangan')->get()->each(
            fn (self $lapangan) => $lapangan->setAttribute('dipakai', $lapangan->sedangDipakai())
        );
    }
}
