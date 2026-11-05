<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Turnamen extends Model
{
    protected $table = 'turnamen';

    protected $primaryKey = 'id_turnamen';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    public static function jumlahMendatang(): int
    {
        return static::whereDate('tgl_pelaksanaan', '>=', today())->count();
    }
}
