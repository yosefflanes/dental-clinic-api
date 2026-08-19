<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DoctorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Doctor::create([
            'name' => 'Drg. Puspita Indriani',
            'specialization' => 'Dokter Gigi Umum',
            'phone' => '081234567890'
        ]);

        Doctor::create([
            'name' => 'Drg. Budi Santoso, Sp. BM',
            'specialization' => 'Spesialis Bedah Mulut',
            'phone' => '089876543210'
        ]);
    }
}
