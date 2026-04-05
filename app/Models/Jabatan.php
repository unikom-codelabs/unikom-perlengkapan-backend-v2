<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'nama'
])]

class Jabatan extends Model
{

    protected $table = 'jabatans';

    public function users()
    {
        return $this->hasMany(User::class, 'jabatan_id');
    }
}
