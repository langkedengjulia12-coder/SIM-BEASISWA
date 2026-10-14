<?php

use Illuminate\Support\Facades\Route;

// ============================================
// Route Public
// ============================================
// Halaman awal diarahkan ke Dashboard Eksekutif SIM-BEASISWA UKRI
Route::get('/', function () {
    return redirect()->route('beasiswa.dashboard');
})->name('home');

// Serve storage files when `public/storage` symlink is not present
Route::get('/storage-files/{path}', function ($path) {
    $path = base64_decode($path);
    $full = storage_path('app/public/' . $path);
    if (!file_exists($full)) {
        abort(404);
    }
    return response()->file($full);
})->where('path', '.*')->name('public.storage.file');

// ============================================
// Route Auth (Laravel Breeze)
// ============================================
require __DIR__.'/auth.php';

// Profile (edit/update/delete)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('password.update');
    Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Shortcut /dashboard (dipakai oleh login redirect & Breeze)
Route::get('/dashboard', function () {
    return redirect()->route('beasiswa.dashboard');
})->name('dashboard');

// ============================================
// Route SIM-BEASISWA UKRI (DUMMY - Frontend Preview)
// ============================================
// Catatan: route ini sengaja TANPA middleware auth & memakai dummy data
// supaya tampilan bisa langsung ditinjau di browser (Laragon).
// Saat backend siap, ganti closure dengan Controller + middleware auth/role.
Route::prefix('beasiswa')->name('beasiswa.')->group(function () {

    // 1. Dashboard Eksekutif
    Route::get('/dashboard', function () {
        $stats = [
            ['label' => 'Total Mitra Aktif',         'value' => '24',        'note' => '+3 sepanjang 2026',      'tone' => 'emerald'],
            ['label' => 'Penerima Beasiswa Aktif',   'value' => '1.248',     'note' => '+86 dari semester lalu', 'tone' => 'amber'],
            ['label' => 'Total Dana Tersalur',       'value' => 'Rp 8,75 M', 'note' => '72% dari alokasi',       'tone' => 'emerald'],
            ['label' => 'Pendaftar Menunggu Seleksi','value' => '312',       'note' => '45 perlu verifikasi',    'tone' => 'amber'],
        ];

        $recentApplications = [
            ['nama' => 'Aulia Rahmawati',    'nim' => '2201010011', 'prodi' => 'Teknik Informatika', 'skema' => 'Beasiswa Prestasi Akademik', 'status' => 'Verifikasi'],
            ['nama' => 'Bagas Pratama',      'nim' => '2201020034', 'prodi' => 'Manajemen',          'skema' => 'Beasiswa KIP Kuliah',        'status' => 'Wawancara'],
            ['nama' => 'Citra Lestari',      'nim' => '2201030057', 'prodi' => 'Akuntansi',          'skema' => 'Beasiswa Mitra Industri',    'status' => 'Lolos'],
            ['nama' => 'Dimas Nugraha',      'nim' => '2201040078', 'prodi' => 'Teknik Sipil',       'skema' => 'Beasiswa Prestasi Akademik', 'status' => 'Ditolak'],
            ['nama' => 'Eka Putri Wulandari','nim' => '2201050092', 'prodi' => 'Ilmu Hukum',         'skema' => 'Beasiswa Yatim Piatu',       'status' => 'Verifikasi'],
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
    Route::get('/partners', function () {
        $partners = [
            ['nama' => 'PT Nusantara Energi',     'jenis' => 'Korporasi',  'mou' => 'MoU/UKRI/012/2024', 'berlaku' => '01 Jan 2024 - 31 Des 2026', 'dana' => 1500000000, 'status' => 'Aktif'],
            ['nama' => 'Bank Mandala Sejahtera',  'jenis' => 'Perbankan',  'mou' => 'MoU/UKRI/021/2025', 'berlaku' => '15 Mar 2025 - 14 Mar 2028', 'dana' => 2250000000, 'status' => 'Aktif'],
            ['nama' => 'Yayasan Cahaya Bangsa',   'jenis' => 'Yayasan',    'mou' => 'MoU/UKRI/033/2023', 'berlaku' => '10 Jul 2023 - 09 Nov 2026', 'dana' => 600000000,  'status' => 'Segera Berakhir'],
            ['nama' => 'Pemerintah Kota Bandung', 'jenis' => 'Pemerintah', 'mou' => 'MoU/UKRI/045/2025', 'berlaku' => '02 Feb 2025 - 01 Feb 2027', 'dana' => 1800000000, 'status' => 'Aktif'],
            ['nama' => 'PT Teknologi Kreasi',     'jenis' => 'Korporasi',  'mou' => 'MoU/UKRI/008/2022', 'berlaku' => '05 Mei 2022 - 04 Mei 2025', 'dana' => 400000000,  'status' => 'Kedaluwarsa'],
            ['nama' => 'Ikatan Alumni UKRI',      'jenis' => 'Alumni',     'mou' => 'MoU/UKRI/051/2026', 'berlaku' => '01 Feb 2026 - 31 Jan 2029', 'dana' => 350000000,  'status' => 'Aktif'],
        ];

        return view('beasiswa.partners', compact('partners'));
    })->name('partners');

    // 3. Skema Beasiswa
    Route::get('/schemes', function () {
        $schemes = [
            ['kode' => 'BSW-PRS', 'nama' => 'Beasiswa Prestasi Akademik', 'mitra' => 'APBU / Internal UKRI',  'nominal' => 6000000, 'kuota' => 200, 'terisi' => 164, 'cakupan' => 'UKT + Uang Saku',    'status' => 'Dibuka'],
            ['kode' => 'BSW-KIP', 'nama' => 'Beasiswa KIP Kuliah',        'mitra' => 'Pemerintah Pusat',      'nominal' => 4500000, 'kuota' => 350, 'terisi' => 350, 'cakupan' => 'UKT + Biaya Hidup',  'status' => 'Penuh'],
            ['kode' => 'BSW-MIT', 'nama' => 'Beasiswa Mitra Industri',    'mitra' => 'PT Nusantara Energi',   'nominal' => 7500000, 'kuota' => 80,  'terisi' => 41,  'cakupan' => 'UKT + Magang',       'status' => 'Dibuka'],
            ['kode' => 'BSW-YTM', 'nama' => 'Beasiswa Yatim Piatu',       'mitra' => 'Yayasan Cahaya Bangsa', 'nominal' => 3000000, 'kuota' => 60,  'terisi' => 22,  'cakupan' => 'UKT Penuh',         'status' => 'Dibuka'],
            ['kode' => 'BSW-HFZ', 'nama' => 'Beasiswa Tahfidz Qur\'an',   'mitra' => 'Ikatan Alumni UKRI',    'nominal' => 3500000, 'kuota' => 40,  'terisi' => 40,  'cakupan' => 'UKT + Uang Saku',    'status' => 'Ditutup'],
        ];

        return view('beasiswa.schemes', compact('schemes'));
    })->name('schemes');

    // 4. Seleksi & Pendaftaran
    Route::get('/applications', function () {
        $pipeline = [
            ['tahap' => 'Pendaftaran',       'jumlah' => 312],
            ['tahap' => 'Verifikasi Berkas', 'jumlah' => 248],
            ['tahap' => 'Wawancara',         'jumlah' => 134],
            ['tahap' => 'Penetapan',         'jumlah' => 86],
        ];

        $applications = [
            ['no' => 'REG-2026-0001', 'nama' => 'Aulia Rahmawati',     'nim' => '2201010011', 'prodi' => 'Teknik Informatika', 'ipk' => 3.82, 'skema' => 'Prestasi Akademik', 'tahap' => 'Verifikasi Berkas', 'status' => 'Proses'],
            ['no' => 'REG-2026-0002', 'nama' => 'Bagas Pratama',       'nim' => '2201020034', 'prodi' => 'Manajemen',          'ipk' => 3.45, 'skema' => 'KIP Kuliah',        'tahap' => 'Wawancara',         'status' => 'Proses'],
            ['no' => 'REG-2026-0003', 'nama' => 'Citra Lestari',       'nim' => '2201030057', 'prodi' => 'Akuntansi',          'ipk' => 3.91, 'skema' => 'Mitra Industri',    'tahap' => 'Penetapan',         'status' => 'Lolos'],
            ['no' => 'REG-2026-0004', 'nama' => 'Dimas Nugraha',       'nim' => '2201040078', 'prodi' => 'Teknik Sipil',       'ipk' => 2.98, 'skema' => 'Prestasi Akademik', 'tahap' => 'Verifikasi Berkas', 'status' => 'Ditolak'],
            ['no' => 'REG-2026-0005', 'nama' => 'Eka Putri Wulandari', 'nim' => '2201050092', 'prodi' => 'Ilmu Hukum',         'ipk' => 3.67, 'skema' => 'Yatim Piatu',       'tahap' => 'Pendaftaran',       'status' => 'Baru'],
            ['no' => 'REG-2026-0006', 'nama' => 'Fajar Hidayat',       'nim' => '2201060113', 'prodi' => 'Teknik Elektro',     'ipk' => 3.54, 'skema' => 'Mitra Industri',    'tahap' => 'Wawancara',         'status' => 'Proses'],
        ];

        return view('beasiswa.applications', compact('pipeline', 'applications'));
    })->name('applications');

    // 5. Penyaluran & Rekonsiliasi
    Route::get('/disbursements', function () {
        $summary = [
            ['label' => 'Total Dana Dicairkan',    'value' => 'Rp 3.072.500.000', 'tone' => 'emerald'],
            ['label' => 'Menunggu Rekonsiliasi',   'value' => 'Rp 307.500.000',   'tone' => 'amber'],
            ['label' => 'Selisih / Retur Transaksi','value' => 'Rp 66.000.000',   'tone' => 'rose'],
        ];

        $disbursements = [
            ['batch' => 'DSB-2026-G-001', 'tanggal' => '05 Okt 2026', 'skema' => 'Prestasi Akademik', 'penerima' => 164, 'nominal' => 984000000,  'bank' => 'Bank Mandala', 'status' => 'Terekonsiliasi'],
            ['batch' => 'DSB-2026-G-002', 'tanggal' => '05 Okt 2026', 'skema' => 'KIP Kuliah',        'penerima' => 350, 'nominal' => 1575000000, 'bank' => 'BNI',          'status' => 'Terekonsiliasi'],
            ['batch' => 'DSB-2026-G-003', 'tanggal' => '12 Okt 2026', 'skema' => 'Mitra Industri',    'penerima' => 41,  'nominal' => 307500000,  'bank' => 'Bank Mandala', 'status' => 'Menunggu'],
            ['batch' => 'DSB-2026-G-004', 'tanggal' => '12 Okt 2026', 'skema' => 'Yatim Piatu',       'penerima' => 22,  'nominal' => 66000000,   'bank' => 'BRI',          'status' => 'Selisih'],
            ['batch' => 'DSB-2026-G-005', 'tanggal' => '13 Okt 2026', 'skema' => 'Tahfidz Qur\'an',   'penerima' => 40,  'nominal' => 140000000,  'bank' => 'BSI',          'status' => 'Terekonsiliasi'],
        ];

        return view('beasiswa.disbursements', compact('summary', 'disbursements'));
    })->name('disbursements');

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
