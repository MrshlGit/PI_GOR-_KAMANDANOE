<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Booking extends Model
{
    protected $table = 'booking';

    protected $primaryKey = 'id_booking';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'id_lapangan',
        'tgl_main',
        'jam_mulai',
        'status_booking',
        'total_bayar',
    ];

    protected static function booted(): void
    {
        static::creating(function (Booking $booking) {
            if (empty($booking->id_booking)) {
                do {
                    $id = Str::upper(Str::random(20));
                } while (static::whereKey($id)->exists());

                $booking->id_booking = $id;
            }
        });
    }
}
