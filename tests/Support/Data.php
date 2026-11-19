<?php

namespace Tests\Support;

use App\Models\KomitmenDana;
use App\Models\Kontrak;
use App\Models\Mahasiswa;
use App\Models\Mitra;
use App\Models\Pengguna;
use App\Models\SkemaBeasiswa;
use App\Support\Kode;
use Illuminate\Http\UploadedFile;

/** Pembuat data uji ringkas. */
trait Data
{
    protected function akun(string $role, string $username, bool $aktif = true): Pengguna
    {
        return Pengguna::create([
            'username' => $username, 'password' => 'rahasia123', 'nama' => ucfirst($username),
            'email' => $username.'@ukri.test', 'role' => $role, 'status_aktif' => $aktif,
        ]);
    }

    protected function admin(): Pengguna
    {
        return $this->akun(Pengguna::ROLE_ADMIN, 'admin1');
    }

    protected function staf(): Pengguna
    {
        return $this->akun(Pengguna::ROLE_STAF, 'staf1');
    }

    protected function mahasiswa(string $username, string $npm, float $ipk = 3.8): Mahasiswa
    {
        $a = $this->akun(Pengguna::ROLE_MAHASISWA, $username);

        return Mahasiswa::create([
            'npm' => $npm, 'id_pengguna' => $a->id_pengguna, 'nama_lengkap' => ucfirst($username),
            'program_studi' => 'Sistem Informasi', 'fakultas' => 'FIKSI', 'angkatan' => 2024, 'ipk' => $ipk,
        ]);
    }

    protected function mitraTerverifikasi(string $nama = 'PT Contoh'): Mitra
    {
        return Mitra::create([
            'id_mitra' => Kode::berikutnya('t_mitra', 'id_mitra', 'MT', 4), 'nama_mitra' => $nama,
            'jenis_mitra' => 'industri', 'nama_pic' => 'Budi', 'email_pic' => strtolower(str_replace(' ', '', $nama)).'@mitra.test',
            'status_verifikasi' => 'terverifikasi', 'tgl_daftar' => now()->toDateString(),
        ]);
    }

    protected function kontrakAktif(Mitra $m, int $hariBerakhir = 300, float $nilai = 100000000): Kontrak
    {
        return Kontrak::create([
            'id_kontrak' => Kode::berikutnya('t_kontrak', 'id_kontrak', 'KT', 4), 'id_mitra' => $m->id_mitra,
            'no_dokumen' => 'DOK-'.uniqid(), 'judul' => 'Kerja sama uji', 'jenis_dokumen' => 'MoU',
            'tgl_mulai' => now()->subDays(10)->toDateString(), 'tgl_berakhir' => now()->addDays($hariBerakhir)->toDateString(),
            'nilai_komitmen' => $nilai, 'status' => 'aktif',
        ]);
    }

    protected function skema(array $o = []): SkemaBeasiswa
    {
        return SkemaBeasiswa::create($o + [
            'id_skema' => Kode::berikutnya('t_skema_beasiswa', 'id_skema', 'SB', 3), 'nama_skema' => 'Skema Uji',
            'jenis' => 'prestasi', 'kuota' => 2, 'nominal_per_penerima' => 1000000, 'ipk_minimum' => 3.5,
            'kriteria_seleksi' => json_encode(['akademik' => 40, 'nonakademik' => 30, 'kebutuhan' => 30]),
            'tgl_buka' => now()->subDay()->toDateString(), 'tgl_tutup' => now()->addDays(10)->toDateString(),
            'aturan_penyaluran' => 'per_semester', 'status' => 'dibuka',
        ]);
    }

    protected function komitmen(Kontrak $k, ?SkemaBeasiswa $s, float $nominal, float $diterima = 0): KomitmenDana
    {
        return KomitmenDana::create([
            'id_komitmen' => Kode::berikutnya('t_komitmen_dana', 'id_komitmen', 'KD', 4), 'id_kontrak' => $k->id_kontrak,
            'id_skema' => $s?->id_skema, 'tgl_komitmen' => now()->toDateString(),
            'nominal_komitmen' => $nominal, 'nominal_diterima' => $diterima,
        ]);
    }

    protected function berkas(string $nama = 'berkas.pdf'): UploadedFile
    {
        return UploadedFile::fake()->create($nama, 100, 'application/pdf');
    }

    /** Seluruh dokumen wajib skema prestasi (transkrip, ktm, sertifikat). */
    protected function dokumenPrestasi(): array
    {
        return ['transkrip' => $this->berkas('t.pdf'), 'ktm' => $this->berkas('k.pdf'), 'sertifikat' => $this->berkas('s.pdf')];
    }
}
