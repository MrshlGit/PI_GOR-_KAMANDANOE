<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alat extends Model
{
    protected $table = 'alats';
    protected $primaryKey = 'id_alat';

    protected $fillable = [
        'nama',
        'deskripsi',
        'harga_sewa',
        'stok',
        'satuan',
        'gambar',
        'aktif',
    ];
}
