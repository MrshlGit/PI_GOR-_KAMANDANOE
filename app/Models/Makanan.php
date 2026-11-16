<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Makanan extends Model
{
    protected $table = 'makanans';
    protected $primaryKey = 'id_makanan';

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
