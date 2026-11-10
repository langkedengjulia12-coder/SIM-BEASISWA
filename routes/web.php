<?php

use App\Http\Controllers\Admin\PartnerController;
use App\Http\Controllers\Admin\SchemeController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\DisbursementController;
use App\Http\Controllers\Mahasiswa\DashboardController as MahasiswaDashboardController;
use App\Http\Controllers\ReportExportController;
use App\Models\AuditLog;
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
    Route::patch('/applications/{application}/review', [ApplicationController::class, 'review'])->name('applications.review');
    // 5. Penyaluran & Rekonsiliasi
    Route::get('/disbursements', [DisbursementController::class, 'index'])->name('disbursements');
    Route::post('/disbursements/batches', [DisbursementController::class, 'createBatch'])->name('disbursements.batches.store');
    Route::post('/disbursements/reconcile', [DisbursementController::class, 'importMutations'])->name('disbursements.reconcile');
    Route::get('/disbursements/batch-template', [DisbursementController::class, 'batchTemplate'])->name('disbursements.batch-template');
    Route::get('/disbursements/mutation-template', [DisbursementController::class, 'mutationTemplate'])->name('disbursements.mutation-template');

    // 6. Pelaporan & Audit
    Route::get('/reports', function () {
        $reports = [
            ['slug' => 'realization', 'nama' => 'Laporan Realisasi Dana per Skema', 'periode' => 'T.A. 2026/2027 Genap', 'format' => 'PDF / Excel', 'update' => now()->translatedFormat('d M Y'), 'icon' => 'chart'],
            ['slug' => 'partners', 'nama' => 'Laporan Pertanggungjawaban Mitra', 'periode' => 'Semester Berjalan', 'format' => 'PDF / Excel', 'update' => now()->translatedFormat('d M Y'), 'icon' => 'handshake'],
            ['slug' => 'recipients', 'nama' => 'Rekapitulasi Penerima Beasiswa', 'periode' => 'T.A. 2026/2027 Genap', 'format' => 'PDF / Excel', 'update' => now()->translatedFormat('d M Y'), 'icon' => 'users'],
            ['slug' => 'reconciliation', 'nama' => 'Laporan Rekonsiliasi Bank', 'periode' => 'Bulanan', 'format' => 'PDF / Excel', 'update' => now()->translatedFormat('d M Y'), 'icon' => 'bank'],
            ['slug' => 'audit', 'nama' => 'Laporan Audit Trail', 'periode' => 'Aktivitas sistem', 'format' => 'PDF / Excel', 'update' => now()->translatedFormat('d M Y'), 'icon' => 'audit'],
        ];

        $auditQuery = AuditLog::query()->latest()->limit(50);
        if (in_array(request('module'), ['Kemitraan', 'Skema', 'Seleksi', 'Penyaluran', 'Pelaporan'], true)) {
            $auditQuery->where('module', request('module'));
        }
        $auditLogs = $auditQuery->get();

        return view('beasiswa.reports', compact('reports', 'auditLogs'));
    })->name('reports');
    Route::get('/reports/{report}/{format}', [ReportExportController::class, 'export'])
        ->where(['report' => 'realization|partners|recipients|reconciliation|audit', 'format' => 'pdf|xlsx'])
        ->name('reports.export');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('partners', PartnerController::class)->except(['create', 'show', 'edit']);
    Route::resource('schemes', SchemeController::class)->except(['create', 'show', 'edit']);
});
