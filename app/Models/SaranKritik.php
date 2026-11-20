<?php

namespace App\Models;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SaranKritik extends Model
{
    protected $table = 'saran_kritik';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_user',
        'nama',
        'isi',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    protected static function booted(): void
    {
        static::creating(function (SaranKritik $saran): void {
            if (empty($saran->getKey())) {
                do {
                    $id = Str::upper(Str::random(20));
                } while (static::whereKey($id)->exists());

                $saran->setAttribute($saran->getKeyName(), $id);
            }
        });
    }
}