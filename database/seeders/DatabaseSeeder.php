<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        // 1. Akun Admin/Pengelola Beasiswa
        $admin = User::updateOrCreate(
            ['email' => 'admin.beasiswa@ukri.ac.id'],
            [
                'name' => 'Admin Beasiswa',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );
        $admin->assignRole('admin');

        // 2. Akun Mahasiswa (Pendaftar) sesuai data uji
        $mahasiswa = User::updateOrCreate(
            ['email' => 'mahasiswa@student.ukri.ac.id'],
            [
                'name' => 'Dimas Pratama Yudha',
                'password' => Hash::make('password'),
                'role' => 'mahasiswa',
            ]
        );
        $mahasiswa->assignRole('mahasiswa');

        // Memanggil seeder modul beasiswa lainnya
        $this->call([
            PartnerSeeder::class,
            ScholarshipSchemeSeeder::class,
            ApplicationSeeder::class,
            DisbursementSeeder::class,
        ]);
    }
}