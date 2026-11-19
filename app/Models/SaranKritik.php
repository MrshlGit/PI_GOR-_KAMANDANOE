<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaranKritik extends Model
{
    protected $table = 'saran_kritik';

    protected $primaryKey = 'id_saran';

    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'tgl_kirim',
        'isi_pesan',
    ];
}