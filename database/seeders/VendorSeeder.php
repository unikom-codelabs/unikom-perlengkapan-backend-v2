<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vendor;

class VendorSeeder extends Seeder
{
    public function run(): void
    {

        Vendor::insert([

            [
                'nama' => 'Gramedia'
            ],

            [
                'nama' => 'Epson'
            ],

            [
                'nama' => 'Kenko'
            ],
            [
                'nama' => 'Standard Stationery'
            ]
        ]);
    }
}
