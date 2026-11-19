<?php

namespace Database\Seeders;

use App\Models\DokumenPendaftaran;
use App\Models\KomitmenDana;
use App\Models\Kontrak;
use App\Models\Mahasiswa;
use App\Models\Mitra;
use App\Models\Pendaftaran;
use App\Models\Penerima;
use App\Models\Penilaian;
use App\Models\Pengguna;
use App\Models\Penyaluran;
use App\Models\SkemaBeasiswa;
use App\Services\DokumenService;
use App\Support\Kode;
use App\Support\SimplePdf;
use Illuminate\Database\Seeder;

/**
 * Data contoh yang memperlihatkan seluruh alur:
 * mitra -> kontrak -> komitmen -> skema -> pendaftaran -> verifikasi -> penilaian
 * -> penetapan -> penyaluran -> konfirmasi -> pelaporan.
 */
class DemoDataSeeder extends Seeder
{
    private DokumenService $dok;

    public function run(): void
    {
        $this->dok = new DokumenService();
        $staf = Pengguna::where('username', 'staf.beasiswa')->first();
        $hari = now();

        // ---------------- Mitra ----------------
        $m1 = $this->mitra('PT Maju Sejahtera', 'industri', 'Rina Kusuma', 'csr@majusejahtera.co.id', 'terverifikasi', true);
        $m2 = $this->mitra('Yayasan Budhi Dharma Pradesa', 'lembaga_donor', 'Hendra Wijaya', 'info@budhidharma.or.id', 'terverifikasi', true);
        $this->mitra('Bank Nusantara Foundation', 'industri', 'Sari Dewi', 'foundation@banknusantara.co.id', 'menunggu', false);

        // ---------------- Kontrak ----------------
        $k1 = $this->kontrak($m1, 'MoU/UKRI/2026/001', 'Kerja sama beasiswa prestasi', $hari->copy()->subMonths(2), $hari->copy()->addMonths(10), 100000000, 'aktif');
        $k2 = $this->kontrak($m2, 'MoA/UKRI/2026/002', 'Bantuan biaya pendidikan', $hari->copy()->subMonths(11), $hari->copy()->addDays(20), 60000000, 'aktif');
        $this->kontrak($m1, 'MoU/UKRI/2026/003', 'Beasiswa riset (rencana)', $hari->copy()->addMonth(), $hari->copy()->addMonths(13), 80000000, 'draf');

        // ---------------- Skema ----------------
        $prestasi = $this->skema('Beasiswa Prestasi Akademik 2026', 'prestasi', 2, 5000000, 3.50, [40, 30, 30], -10, 20);
        $bantuan = $this->skema('Bantuan Biaya Pendidikan 2026', 'bantuan_biaya', 2, 3000000, 3.00, [30, 10, 60], -5, 25);
        $riset = $this->skema('Beasiswa Riset Mahasiswa 2026', 'riset', 3, 6000000, 3.30, [30, 20, 50], 15, 60, 'draf');
        $lalu = $this->skema('Beasiswa Prestasi Semester Lalu', 'prestasi', 2, 4000000, 3.30, [50, 25, 25], -60, -30, 'ditutup');

        // ---------------- Komitmen ----------------
        $this->komitmen($k1, $prestasi, 40000000, 40000000);
        $this->komitmen($k1, $lalu, 16000000, 16000000);
        $this->komitmen($k2, $bantuan, 24000000, 12000000);
        $this->komitmen($k1, $riset, 20000000, 0);

        // ---------------- Pendaftaran pada skema prestasi (tahap berbeda-beda) ----------------
        $this->pendaftaran('20241320001', $prestasi, 'diajukan', 'Ingin mengembangkan riset sistem informasi.', 'prestasi');
        $this->pendaftaran('20241320002', $prestasi, 'diajukan', 'Aktif dalam lomba pemrograman nasional.', 'prestasi');
        $lolos = $this->pendaftaran('20241320005', $prestasi, 'lolos_administrasi', 'Juara 2 kompetisi bisnis.', 'prestasi', 'valid');
        $this->pendaftaran('20241320004', $prestasi, 'tidak_lengkap', 'Mohon pertimbangannya.', 'prestasi', 'tidak_valid', 'Sertifikat tidak terbaca, mohon unggah ulang.');
        $this->pendaftaran('20241320003', $bantuan, 'diajukan', 'Orang tua berpenghasilan terbatas.', 'bantuan_biaya');

        // ---------------- Skema semester lalu: sudah ditetapkan & disalurkan ----------------
        $p5 = $this->pendaftaran('20241320005', $lalu, 'diterima', 'Periode sebelumnya.', 'prestasi', 'valid', null, 1, 88.5);
        $p6 = $this->pendaftaran('20241320006', $lalu, 'diterima', 'Periode sebelumnya.', 'prestasi', 'valid', null, 2, 80.0);
        $n5 = $this->penerima($p5, $lalu, 'SK/WR3/2026/010');
        $n6 = $this->penerima($p6, $lalu, 'SK/WR3/2026/010');

        // Rekening: hanya mahasiswa 5 (agar contoh "rekening kosong" muncul untuk mahasiswa 6)
        Mahasiswa::where('npm', '20241320005')->first()->update(['nama_bank' => 'Bank Nusantara', 'no_rekening' => '1234567890']);

        Penyaluran::create([
            'id_penyaluran' => Kode::berikutnya('t_penyaluran', 'id_penyaluran', 'PY', 5),
            'id_penerima' => $n5->id_penerima, 'termin_ke' => 1, 'nominal' => 4000000,
            'tgl_penyaluran' => $hari->copy()->subDays(7)->toDateString(), 'no_ref_keuangan' => 'KEU-DEMO-0001',
            'status' => 'tersalurkan', 'id_petugas' => $staf->id_pengguna,
        ]);

        // Silakan lanjutkan alur lewat aplikasi: verifikasi, nilai, tetapkan, salurkan, konfirmasi.
    }

    // ===================================================================== helper

    private function mitra(string $nama, string $jenis, string $pic, string $email, string $status, bool $akun): Mitra
    {
        $m = Mitra::create([
            'id_mitra' => Kode::berikutnya('t_mitra', 'id_mitra', 'MT', 4),
            'nama_mitra' => $nama, 'jenis_mitra' => $jenis, 'alamat' => 'Jakarta, Indonesia',
            'nama_pic' => $pic, 'email_pic' => $email, 'telepon_pic' => '0812-0000-'.random_int(1000, 9999),
            'status_verifikasi' => $status, 'tgl_daftar' => now()->subMonths(3)->toDateString(),
        ]);

        if ($akun) {
            $p = Pengguna::create([
                'username' => strtolower(str_replace('.', '', $m->id_mitra)), 'password' => PenggunaSeeder::PASSWORD,
                'nama' => $pic, 'email' => $email, 'role' => Pengguna::ROLE_MITRA,
            ]);
            $m->update(['id_pengguna' => $p->id_pengguna]);
        }

        return $m;
    }

    private function kontrak(Mitra $m, string $no, string $judul, $mulai, $akhir, float $nilai, string $status): Kontrak
    {
        return Kontrak::create([
            'id_kontrak' => Kode::berikutnya('t_kontrak', 'id_kontrak', 'KT', 4),
            'id_mitra' => $m->id_mitra, 'no_dokumen' => $no, 'judul' => $judul,
            'jenis_dokumen' => str_starts_with($no, 'MoA') ? 'MoA' : 'MoU',
            'tgl_mulai' => $mulai->toDateString(), 'tgl_berakhir' => $akhir->toDateString(),
            'nilai_komitmen' => $nilai, 'status' => $status,
        ]);
    }

    private function skema(string $nama, string $jenis, int $kuota, float $nominal, float $ipk, array $bobot, int $bukaHari, int $tutupHari, string $status = 'dibuka'): SkemaBeasiswa
    {
        return SkemaBeasiswa::create([
            'id_skema' => Kode::berikutnya('t_skema_beasiswa', 'id_skema', 'SB', 3),
            'nama_skema' => $nama, 'jenis' => $jenis, 'kuota' => $kuota, 'nominal_per_penerima' => $nominal,
            'ipk_minimum' => $ipk,
            'kriteria_seleksi' => json_encode(['akademik' => $bobot[0], 'nonakademik' => $bobot[1], 'kebutuhan' => $bobot[2]]),
            'tgl_buka' => now()->addDays($bukaHari)->toDateString(), 'tgl_tutup' => now()->addDays($tutupHari)->toDateString(),
            'aturan_penyaluran' => 'per_semester', 'status' => $status,
        ]);
    }

    private function komitmen(Kontrak $k, SkemaBeasiswa $s, float $nominal, float $diterima): void
    {
        KomitmenDana::create([
            'id_komitmen' => Kode::berikutnya('t_komitmen_dana', 'id_komitmen', 'KD', 4),
            'id_kontrak' => $k->id_kontrak, 'id_skema' => $s->id_skema, 'tgl_komitmen' => now()->subMonth()->toDateString(),
            'nominal_komitmen' => $nominal, 'nominal_diterima' => $diterima,
            'tgl_terima_terakhir' => $diterima > 0 ? now()->subDays(15)->toDateString() : null,
            'keterangan' => 'Komitmen untuk '.$s->nama_skema,
        ]);
    }

    private function pendaftaran(string $npm, SkemaBeasiswa $s, string $status, string $motivasi, string $jenisDok,
        string $statusDok = 'menunggu', ?string $catatan = null, ?int $peringkat = null, ?float $total = null): Pendaftaran
    {
        $mhs = Mahasiswa::find($npm);
        $p = Pendaftaran::create([
            'id_pendaftaran' => Kode::berikutnya('t_pendaftaran', 'id_pendaftaran', 'PD', 5),
            'npm' => $npm, 'id_skema' => $s->id_skema, 'tgl_daftar' => now()->subDays(random_int(2, 6)),
            'ipk_saat_daftar' => $mhs->ipk, 'motivasi' => $motivasi, 'status' => $status, 'catatan_verifikasi' => $catatan,
        ]);

        foreach (config('beasiswa.dokumen_wajib')[$jenisDok] as $i => $jenis) {
            $isi = SimplePdf::tabel('Dokumen contoh: '.($jenis), $mhs->nama_lengkap.' / '.$npm, ['Keterangan'], [['Berkas contoh untuk keperluan demo sistem.']]);
            $f = $this->dok->simpanKonten($isi, $jenis.'_'.$npm.'.pdf', 'pendaftaran');

            $sv = $statusDok;
            if ($statusDok === 'tidak_valid' && $i > 0) {
                $sv = 'valid';   // hanya dokumen pertama yang bermasalah
            }
            DokumenPendaftaran::create([
                'id_dokumen' => Kode::berikutnya('t_dokumen_pendaftaran', 'id_dokumen', 'DK', 5),
                'id_pendaftaran' => $p->id_pendaftaran, 'jenis_dokumen' => $jenis, 'nama_file' => $jenis.'_'.$npm.'.pdf',
                'path_file' => $f['path'], 'ukuran_kb' => $f['ukuran_kb'], 'status_verifikasi' => $sv, 'tgl_unggah' => now(),
            ]);
        }

        if ($peringkat) {
            Penilaian::create([
                'id_penilaian' => Kode::berikutnya('t_penilaian', 'id_penilaian', 'NL', 5),
                'id_pendaftaran' => $p->id_pendaftaran, 'nilai_akademik' => $total, 'nilai_nonakademik' => $total,
                'nilai_kebutuhan' => $total, 'nilai_total' => $total, 'peringkat' => $peringkat,
                'id_penilai' => Pengguna::where('username', 'staf.beasiswa')->value('id_pengguna'), 'tgl_penilaian' => now()->subDays(40),
            ]);
        }

        return $p;
    }

    private function penerima(Pendaftaran $p, SkemaBeasiswa $s, string $sk): Penerima
    {
        return Penerima::create([
            'id_penerima' => Kode::berikutnya('t_penerima', 'id_penerima', 'PN', 5),
            'id_pendaftaran' => $p->id_pendaftaran, 'id_skema' => $s->id_skema, 'npm' => $p->npm,
            'no_sk' => $sk, 'tgl_penetapan' => now()->subDays(28)->toDateString(), 'status_penerima' => 'aktif',
        ]);
    }
}
