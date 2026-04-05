<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable([
    'nip',
    'username',
    'email',
    'password',
    'jenis_kelamin',
    'foto',
    'jabatan_id',
    'unit_id',
    'role'
])]

#[Hidden([
    'password',
    'remember_token'
])]

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected function casts(): array
    {
        return [
            'password' => 'hashed'
        ];
    }

    public function position()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id');
    }

    public function unit()
    {
        return $this->belongsTo(UnitType::class, 'unit_id');
    }

    public function daftarPengajuan()
    {
        return $this->hasMany(DaftarPengajuan::class);
    }
}
