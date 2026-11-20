<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TurnamenPeserta extends Model
{
    protected $table = 'turnamen_peserta';

    protected $fillable = ['id_turnamen', 'id_user', 'nama_tim', 'kontak'];
}