<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'nama',
    'parent_id',
     'type'
])]

class UnitType extends Model
{

    protected $table = 'unit_types';

    public function parent()
    {
        return $this->belongsTo(UnitType::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(UnitType::class, 'parent_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'unit_id');
    }
}
