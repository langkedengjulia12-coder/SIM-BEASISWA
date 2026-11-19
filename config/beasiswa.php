<?php

/*
|--------------------------------------------------------------------------
| Konfigurasi SIKB-UKRI (SIM-BEASISWA)
|--------------------------------------------------------------------------
| Nilai di bawah mengikuti SKPL OO (ALGO-01 s.d. ALGO-18). Semua dapat
| diubah lewat .env tanpa mengubah kode.
*/

return [
    // ALGO-05 / ALGO-11: jumlah termin penyaluran per aturan_penyaluran
    'termin' => [
        'sekaligus'    => 1,
        'per_semester' => 2,
        'bulanan'      => 6,
    ],

    // ALGO-06: dokumen wajib per jenis beasiswa
    'dokumen_wajib' => [
        'prestasi'      => ['transkrip', 'ktm', 'sertifikat'],
        'bantuan_biaya' => ['transkrip', 'ktm', 'surat_keterangan', 'slip_penghasilan'],
        'riset'         => ['transkrip', 'ktm', 'proposal'],
    ],

    'dokumen_label' => [
        'transkrip'        => 'Transkrip Nilai',
        'ktm'              => 'Kartu Tanda Mahasiswa (KTM)',
        'surat_keterangan' => 'Surat Keterangan Tidak Mampu',
        'slip_penghasilan' => 'Slip Penghasilan Orang Tua',
        'proposal'         => 'Proposal Riset',
        'sertifikat'       => 'Sertifikat Prestasi',
    ],

    'maks_dokumen_kb' => 5120,

    // ALGO-01: 5 kali gagal berturut-turut -> kunci 15 menit
    'login_maks_gagal' => 5,
    'login_kunci_detik' => 15 * 60,

    // ALGO-03: peringatan kontrak <= 30 hari
    'kontrak_peringatan_hari' => 30,

    // ALGO-09: cadangan hingga 20% kuota
    'cadangan_persen' => 20,

    // ALGO-18: percobaan ulang pengiriman email
    'email_maks_percobaan' => 3,

    // KAK 7.1.B: backup otomatis harian & mingguan
    'backup' => [
        'mysqldump'       => env('MYSQLDUMP_PATH', 'mysqldump'),   // XAMPP: C:\xampp\mysql\bin\mysqldump.exe
        'simpan_harian'   => 7,
        'simpan_mingguan' => 4,
    ],

    // Integrasi REST API sistem keuangan universitas (ALGO-11).
    // Jika KEUANGAN_API_URL kosong, sistem berjalan dalam mode SIMULASI
    // (instruksi dianggap berhasil dan nomor referensi dibangkitkan lokal).
    'keuangan' => [
        'url'     => env('KEUANGAN_API_URL'),
        'token'   => env('KEUANGAN_API_TOKEN'),
        'timeout' => (int) env('KEUANGAN_API_TIMEOUT', 10),
    ],
];
