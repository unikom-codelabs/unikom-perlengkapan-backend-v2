<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UnitType;

class UnitTypeSeeder extends Seeder
{
    public function run(): void
    {

        $fakultas = UnitType::create([
            'nama' => 'Fakultas Teknik',
            'parent_id' => null
        ]);

        $prodi = UnitType::create([
            'nama' => 'Teknik Informatika',
            'parent_id' => $fakultas->id
        ]);

        UnitType::create([
            'nama' => 'Lab Programming',
            'parent_id' => $prodi->id
        ]);
    }
}
