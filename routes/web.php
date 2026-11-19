<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DokumenController;
use App\Http\Controllers\EksporController;
use App\Http\Controllers\KomitmenController;
use App\Http\Controllers\KontrakController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\MitraController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\PendaftaranController;
use App\Http\Controllers\PenetapanController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\PenilaianController;
use App\Http\Controllers\PenyaluranController;
use App\Http\Controllers\ProfilMahasiswaController;
use App\Http\Controllers\SkemaController;
use App\Http\Controllers\VerifikasiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rute SIM-BEASISWA UKRI - mengikuti SKPL OO (P-01 s.d. P-18)
|--------------------------------------------------------------------------
| Peran: admin_wr3 | staf_beasiswa | mahasiswa | mitra
| Admin WR3 otomatis lolos seluruh rute staf_beasiswa.
*/

require __DIR__.'/auth.php';

Route::get('/', fn () => redirect()->route('dashboard'))->name('home');

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'arahkan'])->name('dashboard');

    // Notifikasi (UC-18) - semua peran
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::post('/notifikasi/baca-semua', [NotifikasiController::class, 'bacaSemua'])->name('notifikasi.baca-semua');
    Route::post('/notifikasi/{id}/baca', [NotifikasiController::class, 'baca'])->whereNumber('id')->name('notifikasi.baca');

    // ---------------- Admin WR3 ----------------
    Route::middleware('role:admin_wr3')->group(function () {
        Route::get('/admin/dashboard', [DashboardController::class, 'ringkasan'])->name('dashboard.admin');   // P-16
        Route::get('/pengguna', [PenggunaController::class, 'index'])->name('pengguna.index');               // P-17
        Route::post('/pengguna', [PenggunaController::class, 'store'])->name('pengguna.store');
        Route::put('/pengguna/{pengguna}', [PenggunaController::class, 'update'])->name('pengguna.update');
    });

    // ---------------- Staf Unit Beasiswa (+ Admin WR3) ----------------
    Route::middleware('role:staf_beasiswa')->group(function () {
        Route::get('/beranda', [DashboardController::class, 'beranda'])->name('beranda');

        // P-02 Mitra & Donor
        Route::get('/mitra', [MitraController::class, 'index'])->name('mitra.index');
        Route::post('/mitra', [MitraController::class, 'store'])->name('mitra.store');
        Route::put('/mitra/{mitra}', [MitraController::class, 'update'])->name('mitra.update');
        Route::post('/mitra/{mitra}/verifikasi', [MitraController::class, 'verifikasi'])->name('mitra.verifikasi');

        // P-03 Kontrak
        Route::get('/kontrak', [KontrakController::class, 'index'])->name('kontrak.index');
        Route::post('/kontrak', [KontrakController::class, 'store'])->name('kontrak.store');
        Route::post('/kontrak/{kontrak}/aktifkan', [KontrakController::class, 'aktifkan'])->name('kontrak.aktifkan');
        Route::post('/kontrak/{kontrak}/perpanjang', [KontrakController::class, 'perpanjang'])->name('kontrak.perpanjang');
        Route::get('/kontrak/{kontrak}/berkas', [KontrakController::class, 'berkas'])->name('kontrak.berkas');

        // P-04 Komitmen (tambah & catat penerimaan)
        Route::post('/komitmen', [KomitmenController::class, 'store'])->name('komitmen.store');
        Route::post('/komitmen/{komitmen}/penerimaan', [KomitmenController::class, 'catatPenerimaan'])->name('komitmen.penerimaan');

        // P-05 Skema
        Route::get('/skema', [SkemaController::class, 'index'])->name('skema.index');
        Route::post('/skema', [SkemaController::class, 'store'])->name('skema.store');
        Route::put('/skema/{skema}', [SkemaController::class, 'update'])->name('skema.update');
        Route::post('/skema/{skema}/buka', [SkemaController::class, 'buka'])->name('skema.buka');
        Route::post('/skema/{skema}/tutup', [SkemaController::class, 'tutup'])->name('skema.tutup');
        Route::post('/skema/{skema}/selesai', [SkemaController::class, 'selesai'])->name('skema.selesai');

        // P-07 Verifikasi berkas
        Route::get('/seleksi/verifikasi', [VerifikasiController::class, 'index'])->name('verifikasi.index');
        Route::get('/seleksi/verifikasi/{pendaftaran}', [VerifikasiController::class, 'show'])->name('verifikasi.show');
        Route::post('/seleksi/verifikasi/{pendaftaran}', [VerifikasiController::class, 'simpanHasil'])->name('verifikasi.simpan');

        // P-08 Penilaian & ranking
        Route::get('/seleksi/penilaian', [PenilaianController::class, 'index'])->name('penilaian.index');
        Route::post('/seleksi/penilaian/{skema}', [PenilaianController::class, 'hitung'])->name('penilaian.hitung');

        // P-09 Penetapan & pengumuman
        Route::get('/seleksi/penetapan', [PenetapanController::class, 'index'])->name('penetapan.index');
        Route::post('/seleksi/penetapan/{skema}', [PenetapanController::class, 'tetapkan'])->name('penetapan.tetapkan');

        // P-11 Penyaluran
        Route::get('/penyaluran', [PenyaluranController::class, 'index'])->name('penyaluran.index');
        Route::post('/penyaluran/salurkan', [PenyaluranController::class, 'salurkan'])->name('penyaluran.salurkan');

        // P-14 Ekspor
        Route::get('/ekspor', [EksporController::class, 'index'])->name('ekspor.index');
        Route::post('/ekspor', [EksporController::class, 'proses'])->name('ekspor.proses');

        // P-15 Monitoring akademik
        Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
        Route::post('/monitoring', [MonitoringController::class, 'perbarui'])->name('monitoring.perbarui');
    });

    // Staf + Mitra: P-04 (lihat komitmen) & P-13 (laporan)
    Route::middleware('role:staf_beasiswa,mitra')->group(function () {
        Route::get('/komitmen', [KomitmenController::class, 'index'])->name('komitmen.index');
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::post('/laporan', [LaporanController::class, 'tampil'])->name('laporan.tampil');
        Route::get('/laporan/{laporan}/unduh', [LaporanController::class, 'unduh'])->name('laporan.unduh');
    });

    // ---------------- Mahasiswa ----------------
    Route::middleware('role:mahasiswa')->group(function () {
        Route::get('/pendaftaran', [PendaftaranController::class, 'form'])->name('pendaftaran.form');              // P-06
        Route::post('/pendaftaran', [PendaftaranController::class, 'kirim'])->name('pendaftaran.kirim');
        Route::get('/pendaftaran/status', [PendaftaranController::class, 'statusSaya'])->name('pendaftaran.status'); // P-10
        Route::post('/pendaftaran/{pendaftaran}/perbaiki', [PendaftaranController::class, 'perbaiki'])->name('pendaftaran.perbaiki');

        Route::get('/penyaluran/saya', [PenyaluranController::class, 'saya'])->name('penyaluran.saya');            // P-12
        Route::post('/penyaluran/{penyaluran}/konfirmasi', [PenyaluranController::class, 'konfirmasi'])->name('penyaluran.konfirmasi');

        Route::get('/profil', [ProfilMahasiswaController::class, 'edit'])->name('profil.edit');
        Route::put('/profil', [ProfilMahasiswaController::class, 'update'])->name('profil.update');
    });

    // Unduh dokumen pendaftaran (staf atau pemilik; otorisasi di controller)
    Route::get('/dokumen/{dokumen}', [DokumenController::class, 'unduh'])->name('dokumen.unduh');
});
