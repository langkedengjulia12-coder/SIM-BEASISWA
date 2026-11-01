<?php

use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\DisbursementController;
use App\Http\Controllers\Mahasiswa\DashboardController as MahasiswaDashboardController;
use App\Http\Controllers\Admin\PartnerController;
use App\Http\Controllers\Admin\SchemeController;
use Illuminate\Support\Facades\Route;

// ============================================
// Route Public
// ============================================
// Halaman awal diarahkan ke Dashboard Eksekutif SIM-BEASISWA UKRI
Route::get('/', function () {
    return redirect()->route('beasiswa.dashboard');
})->name('home');

// ============================================
// Route Auth (Laravel Breeze)
// ============================================
require __DIR__.'/auth.php';

// ============================================
// Portal Mahasiswa UKRI
// ============================================
Route::middleware(['auth', 'role:mahasiswa'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
    Route::get('/dashboard', [MahasiswaDashboardController::class, 'index'])->name('dashboard');
    Route::get('/panduan-sk', [MahasiswaDashboardController::class, 'downloadGuide'])->name('guide.download');
    Route::post('/konfirmasi-mutasi', [MahasiswaDashboardController::class, 'storeConfirmation'])->name('confirmation.store');
    Route::post('/skema/{scheme}/daftar', [MahasiswaDashboardController::class, 'applyScheme'])->name('daftar');
});

// ============================================
// Route Beasiswa UKRI
// ============================================
Route::middleware(['auth', 'role:admin'])->prefix('beasiswa')->name('beasiswa.')->group(function () {

    // 1. Dashboard Eksekutif
    Route::get('/dashboard', function () {
        $stats = [
            ['label' => 'Total Mitra Aktif',         'value' => '24',        'note' => '+3 sepanjang 2026',      'tone' => 'emerald'],
            ['label' => 'Penerima Beasiswa Aktif',   'value' => '1.248',     'note' => '+86 dari semester lalu', 'tone' => 'amber'],
            ['label' => 'Total Dana Tersalur',       'value' => 'Rp 8,75 M', 'note' => '72% dari alokasi',       'tone' => 'emerald'],
            ['label' => 'Pendaftar Menunggu Seleksi', 'value' => '312',       'note' => '45 perlu verifikasi',    'tone' => 'amber'],
        ];

        $recentApplications = [
            ['nama' => 'Aulia Rahmawati',    'nim' => '2201010011', 'prodi' => 'Teknik Informatika', 'skema' => 'Beasiswa Prestasi Akademik', 'status' => 'Verifikasi'],
            ['nama' => 'Bagas Pratama',      'nim' => '2201020034', 'prodi' => 'Manajemen',          'skema' => 'Beasiswa KIP Kuliah',        'status' => 'Wawancara'],
            ['nama' => 'Citra Lestari',      'nim' => '2201030057', 'prodi' => 'Akuntansi',          'skema' => 'Beasiswa Mitra Industri',    'status' => 'Lolos'],
            ['nama' => 'Dimas Nugraha',      'nim' => '2201040078', 'prodi' => 'Teknik Sipil',       'skema' => 'Beasiswa Prestasi Akademik', 'status' => 'Ditolak'],
            ['nama' => 'Eka Putri Wulandari', 'nim' => '2201050092', 'prodi' => 'Ilmu Hukum',         'skema' => 'Beasiswa Yatim Piatu',       'status' => 'Verifikasi'],
        ];

        $fundBySource = [
            ['sumber' => 'APBU / Internal UKRI', 'persen' => 45],
            ['sumber' => 'Mitra Korporasi',      'persen' => 35],
            ['sumber' => 'Pemerintah / KIP-K',   'persen' => 15],
            ['sumber' => 'Donasi Alumni',        'persen' => 5],
        ];

        $alerts = [
            ['text' => 'MoU dengan Yayasan Cahaya Bangsa berakhir dalam 28 hari.', 'level' => 'warning'],
            ['text' => '12 data rekening penerima belum tervalidasi.',             'level' => 'danger'],
            ['text' => 'Batas verifikasi berkas gelombang II: 30 hari lagi.',      'level' => 'info'],
        ];

        return view('beasiswa.dashboard', compact('stats', 'recentApplications', 'fundBySource', 'alerts'));
    })->name('dashboard');

    // 2. Manajemen Kemitraan & MoU
    Route::get('/partners', [PartnerController::class, 'index'])->name('partners');

    // 3. Skema Beasiswa
    Route::get('/schemes', [SchemeController::class, 'index'])->name('schemes');

    // 4. Seleksi & Pendaftaran
    Route::get('/applications', [ApplicationController::class, 'index'])->name('applications');

    // 5. Penyaluran & Rekonsiliasi
    Route::get('/disbursements', [DisbursementController::class, 'index'])->name('disbursements');

    // 6. Pelaporan & Audit
    Route::get('/reports', function () {
        $reports = [
            ['nama' => 'Laporan Realisasi Dana per Skema', 'periode' => 'T.A. 2026/2027 Genap', 'format' => 'PDF / XLSX', 'update' => '14 Okt 2026', 'icon' => 'chart'],
            ['nama' => 'Laporan Pertanggungjawaban Mitra', 'periode' => 'Semester Berjalan',    'format' => 'PDF',        'update' => '10 Okt 2026', 'icon' => 'handshake'],
            ['nama' => 'Rekapitulasi Penerima Beasiswa',   'periode' => 'T.A. 2026/2027 Genap', 'format' => 'XLSX',       'update' => '12 Okt 2026', 'icon' => 'users'],
            ['nama' => 'Laporan Rekonsiliasi Bank',        'periode' => 'Bulanan',              'format' => 'PDF / XLSX', 'update' => '01 Okt 2026', 'icon' => 'bank'],
        ];

        $auditLogs = [
            ['waktu' => '14 Okt 2026 09:12', 'user' => 'admin.beasiswa',   'aksi' => 'Menyetujui batch penyaluran DSB-2026-G-003',    'modul' => 'Penyaluran'],
            ['waktu' => '14 Okt 2026 08:47', 'user' => 'verifikator01',    'aksi' => 'Mengubah status REG-2026-0004 menjadi Ditolak', 'modul' => 'Seleksi'],
            ['waktu' => '13 Okt 2026 16:30', 'user' => 'admin.beasiswa',   'aksi' => 'Memperbarui kuota skema BSW-MIT',               'modul' => 'Skema'],
            ['waktu' => '13 Okt 2026 11:05', 'user' => 'kemitraan01',      'aksi' => 'Mengunggah addendum MoU/UKRI/033/2023',         'modul' => 'Kemitraan'],
            ['waktu' => '12 Okt 2026 14:21', 'user' => 'auditor.internal', 'aksi' => 'Mengunduh Laporan Realisasi Dana',              'modul' => 'Pelaporan'],
        ];

        return view('beasiswa.reports', compact('reports', 'auditLogs'));
    })->name('reports');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('partners', PartnerController::class)->except(['create', 'show', 'edit']);
    Route::resource('schemes', SchemeController::class)->except(['create', 'show', 'edit']);
});
