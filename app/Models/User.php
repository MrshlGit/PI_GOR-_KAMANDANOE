<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'user';

    public $timestamps = false;

    protected $fillable = [
        'username',
        'noHp',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    protected function inisial(): Attribute
    {
        return Attribute::get(fn () => strtoupper(mb_substr($this->username ?? '?', 0, 2)));
    }

    public static function ringkasan(): array
    {
        return [
            'total' => static::count(),
            'admin' => static::where('role', 'admin')->count(),
            'user'  => static::where('role', 'user')->count(),
        ];
    }
}
