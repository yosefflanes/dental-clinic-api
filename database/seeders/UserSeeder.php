<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Akun Admin
        User::updateOrCreate(
            ['email'    => 'admin@example.com'],
            [
                'name'      => 'Admin Klinik',
                'password'  => Hash::make('admin123'),
                'phone'     => '08123456789',
                'role'      => 'admin',
            ]
        );

        // Akun User / Pasien
        User::updateOrCreate(
            ['email'    => 'user@example.com'],
            [
                'name'      => 'Pasien',
                'password'  => Hash::make('user123'),
                'phone'     => '081122334455',
                'role'      => 'user',
            ]
        );
    }
}
