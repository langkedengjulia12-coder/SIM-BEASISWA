<?php

namespace Database\Seeders;

use App\Models\Mahasiswa;
use App\Models\Pengguna;
use Illuminate\Database\Seeder;

/**
 * Akun awal. Password seluruh akun demo: Ukri@2026  (WAJIB diganti di produksi).
 */
class PenggunaSeeder extends Seeder
{
    public const PASSWORD = 'Ukri@2026';

    public function run(): void
    {
        Pengguna::create(['username' => 'admin.wr3', 'password' => self::PASSWORD, 'nama' => 'Admin Wakil Rektor 3',
            'email' => 'wr3@ukri.ac.id', 'role' => Pengguna::ROLE_ADMIN]);
        Pengguna::create(['username' => 'staf.beasiswa', 'password' => self::PASSWORD, 'nama' => 'Staf Unit Beasiswa',
            'email' => 'beasiswa@ukri.ac.id', 'role' => Pengguna::ROLE_STAF]);

        $mahasiswa = [
            // npm, nama, prodi, fakultas, angkatan, ipk
            ['20241320001', 'Aulia Rahma', 'Sistem Informasi', 'Fakultas Ilmu Komputer dan Sistem Informasi', 2024, 3.85],
            ['20241320002', 'Bima Prakoso', 'Sistem Informasi', 'Fakultas Ilmu Komputer dan Sistem Informasi', 2024, 3.70],
            ['20241320003', 'Citra Lestari', 'Teknik Informatika', 'Fakultas Ilmu Komputer dan Sistem Informasi', 2024, 3.55],
            ['20241320004', 'Dimas Pratama', 'Teknik Informatika', 'Fakultas Ilmu Komputer dan Sistem Informasi', 2023, 3.40],
            ['20241320005', 'Eka Putri', 'Manajemen', 'Fakultas Ekonomi dan Bisnis', 2023, 3.60],
            ['20241320006', 'Fajar Nugroho', 'Akuntansi', 'Fakultas Ekonomi dan Bisnis', 2023, 3.20],
            ['20241320007', 'Gita Permata', 'Manajemen', 'Fakultas Ekonomi dan Bisnis', 2022, 2.40],
        ];

        foreach ($mahasiswa as $i => [$npm, $nama, $prodi, $fak, $angkatan, $ipk]) {
            $akun = Pengguna::create([
                'username' => 'mhs'.($i + 1), 'password' => self::PASSWORD, 'nama' => $nama,
                'email' => 'mhs'.($i + 1).'@student.ukri.ac.id', 'role' => Pengguna::ROLE_MAHASISWA,
            ]);
            Mahasiswa::create([
                'npm' => $npm, 'id_pengguna' => $akun->id_pengguna, 'nama_lengkap' => $nama,
                'program_studi' => $prodi, 'fakultas' => $fak, 'angkatan' => $angkatan, 'ipk' => $ipk,
                'status_akademik' => 'aktif',
            ]);
        }
    }
}
