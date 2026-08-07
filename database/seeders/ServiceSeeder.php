<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'name' => 'Cabut Gigi',
                'description' => 'Tindakan ini bertujuan untuk mengangkat gigi yang sudah tidak dapat dipertahankan lagi guna menjaga kesehatan rongga mulut Anda secara keseluruhan.',
                'price' => 1200000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Pembersihan Karang Gigi (Scaling)',
                'description' => 'Pembersihan plak dan karang gigi secara menyeluruh untuk mencegah radang gusi dan bau mulut.',
                'price' => 300000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Tambal Gigi Estetik',
                'description' => 'Perawatan penambalan gigi yang berlubang menggunakan bahan yang senada dengan warna gigi asli.',
                'price' => 1500000.00,
                'is_active' => true,
            ],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }
    }
}
