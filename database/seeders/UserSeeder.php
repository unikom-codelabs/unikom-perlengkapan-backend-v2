<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {

        User::create([
            'nip' => 'ADM001',
            'username' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'jenis_kelamin' => 'Pria',
            'jabatan_id' => 1,
            'unit_id' => 1,
            'role' => 'admin'
        ]);

        User::create([
            'nip' => 'USR001',
            'username' => 'budi',
            'email' => 'user@gmail.com',
            'password' => Hash::make('password'),
            'jenis_kelamin' => 'Wanita',
            'jabatan_id' => 2,
            'unit_id' => 2,
            'role' => 'user'
        ]);
    }
}
