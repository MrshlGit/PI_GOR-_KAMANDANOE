<?php

namespace App\Models\User;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;
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

    protected static function newFactory(): Factory
    {
        return UserFactory::new();
    }

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
}
