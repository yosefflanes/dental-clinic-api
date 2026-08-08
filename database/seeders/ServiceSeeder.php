<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
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
            [
                'name' => 'Pemutihan Gigi (Bleaching)',
                'description' => 'Mencerahkan warna gigi menggunakan bahan khusus yang aman untuk mengembalikan senyum cerah alami Anda.',
                'price' => 2500000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Pemasangan Kawat Gigi (Ortodonti)',
                'description' => 'Perawatan untuk merapikan susunan gigi yang tidak beraturan guna memperbaiki fungsi kunyah dan estetika.',
                'price' => 8500000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Perawatan Saluran Akar (Root Canal)',
                'description' => 'Menyelamatkan gigi yang terinfeksi atau rusak parah dengan membersihkan area dalam gigi (pulpa).',
                'price' => 2000000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Pembuatan Gigi Palsu (Dentures)',
                'description' => 'Gigi tiruan lepasan untuk menggantikan beberapa atau seluruh gigi yang hilang.',
                'price' => 3500000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Pemasangan Mahkota Gigi (Dental Crown)',
                'description' => 'Selubung yang dipasang di atas gigi yang rusak untuk mengembalikan bentuk, ukuran, dan kekuatannya.',
                'price' => 4000000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Pemasangan Implan Gigi',
                'description' => 'Akar gigi tiruan berbahan titanium yang ditanam di rahang untuk menopang gigi pengganti permanen.',
                'price' => 12000000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Konsultasi Dokter Gigi Anak',
                'description' => 'Pemeriksaan rutin dan penanganan masalah gigi khusus untuk anak-anak dengan pendekatan yang ramah.',
                'price' => 250000.00,
                'is_active' => true,
            ],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }
    }
}
