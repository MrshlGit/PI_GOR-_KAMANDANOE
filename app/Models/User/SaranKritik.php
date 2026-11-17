<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaranKritik extends Model
{
    protected $table = 'saran_kritik';

    protected $fillable = ['id_user', 'nama', 'isi'];
}