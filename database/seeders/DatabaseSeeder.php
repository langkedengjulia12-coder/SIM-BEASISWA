<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PenggunaSeeder::class,   // akun admin WR3, staf, mahasiswa, mitra
            DemoDataSeeder::class,   // data contoh alur kemitraan -> seleksi -> penyaluran
        ]);
    }
}
