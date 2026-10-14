<?php

namespace Database\Seeders;

use App\Models\Kamar;
use App\Models\Penyewa;
use App\Models\Transaksi;
use App\Models\TarifUtilitas;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        // =============================================
        // USERS
        // =============================================
        $admin = User::create([
            'name'      => 'Admin SIMK',
            'email'     => 'admin@simk.app',
            'password'  => Hash::make('password123'),
            'role'      => 'admin',
            'no_hp'     => '08123456789',
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        $pengelola = User::create([
            'name'      => 'Bapak Emip',
            'email'     => 'emip@simk.app',
            'password'  => Hash::make('password123'),
            'role'      => 'pengelola',
            'no_hp'     => '08129999888',
            'is_active' => true,
        ]);
        $pengelola->assignRole('pengelola');

        $penyewaUser = User::create([
            'name'      => 'Penyewa Sample',
            'email'     => 'penyewa@simk.app',
            'password'  => Hash::make('penyewa123'),
            'role'      => 'penyewa',
            'no_hp'     => '081234567890',
            'is_active' => true,
        ]);
        $penyewaUser->assignRole('penyewa');

        // =============================================
        // TARIF UTILITAS
        // =============================================
        TarifUtilitas::create([
            'jenis'          => 'listrik',
            'harga_per_unit' => 1444.70,
            'satuan'         => 'kWh',
            'berlaku_mulai'  => '2024-01-01',
            'is_active'      => true,
            'keterangan'     => 'Tarif R1/900VA PLN 2024',
        ]);

        TarifUtilitas::create([
            'jenis'          => 'air',
            'harga_per_unit' => 4500.00,
            'satuan'         => 'm3',
            'berlaku_mulai'  => '2024-01-01',
            'is_active'      => true,
            'keterangan'     => 'Tarif PDAM 2024',
        ]);

        // =============================================
        // KAMAR
        // =============================================
        $kamarData = [
            ['nomor_kamar' => 'A01', 'tipe' => 'standar',  'harga_sewa' => 700000,  'lantai' => 1, 'luas_m2' => 12, 'fasilitas' => ['Kasur', 'Lemari', 'Kipas Angin']],
            ['nomor_kamar' => 'A02', 'tipe' => 'standar',  'harga_sewa' => 700000,  'lantai' => 1, 'luas_m2' => 12, 'fasilitas' => ['Kasur', 'Lemari', 'Kipas Angin']],
            ['nomor_kamar' => 'A03', 'tipe' => 'standar',  'harga_sewa' => 700000,  'lantai' => 1, 'luas_m2' => 12, 'fasilitas' => ['Kasur', 'Lemari', 'Kipas Angin']],
            ['nomor_kamar' => 'B01', 'tipe' => 'deluxe',   'harga_sewa' => 1000000, 'lantai' => 1, 'luas_m2' => 16, 'fasilitas' => ['Kasur', 'Lemari', 'AC', 'Kamar Mandi Dalam']],
            ['nomor_kamar' => 'B02', 'tipe' => 'deluxe',   'harga_sewa' => 1000000, 'lantai' => 1, 'luas_m2' => 16, 'fasilitas' => ['Kasur', 'Lemari', 'AC', 'Kamar Mandi Dalam']],
            ['nomor_kamar' => 'C01', 'tipe' => 'premium',  'harga_sewa' => 1500000, 'lantai' => 2, 'luas_m2' => 20, 'fasilitas' => ['Kasur Spring Bed', 'Lemari 2 Pintu', 'AC', 'Kamar Mandi Dalam', 'Water Heater', 'Meja Kerja']],
            ['nomor_kamar' => 'C02', 'tipe' => 'premium',  'harga_sewa' => 1500000, 'lantai' => 2, 'luas_m2' => 20, 'fasilitas' => ['Kasur Spring Bed', 'Lemari 2 Pintu', 'AC', 'Kamar Mandi Dalam', 'Water Heater', 'Meja Kerja']],
            ['nomor_kamar' => 'D01', 'tipe' => 'standar',  'harga_sewa' => 750000,  'lantai' => 2, 'luas_m2' => 12, 'fasilitas' => ['Kasur', 'Lemari', 'Kipas Angin']],
        ];

        $kamars = [];
        foreach ($kamarData as $data) {
            $kamars[] = Kamar::create(array_merge($data, ['status' => 'tersedia']));
        }

        // =============================================
        // PENYEWA SAMPLE
        // =============================================
        $penyewaData = [
            [
                'kamar'          => $kamars[0],
                'user_id'        => $penyewaUser->id,
                'nama_lengkap'   => 'Budi Santoso',
                'email'          => 'penyewa@simk.app',
                'nik'            => '3201010101800001',
                'no_hp'          => '081234567890',
                'jenis_kelamin'  => 'L',
                'tanggal_masuk'  => '2024-01-01',
                'kontrak_mulai'  => '2024-01-01',
                'kontrak_selesai'=> '2024-12-31',
                'durasi_kontrak_bulan' => 12,
                'harga_sewa_kontrak' => 700000,
                'pekerjaan'      => 'Karyawan Swasta',
            ],
            [
                'kamar'          => $kamars[3],
                'nama_lengkap'   => 'Siti Rahayu',
                'nik'            => '3201010101900002',
                'no_hp'          => '082345678901',
                'jenis_kelamin'  => 'P',
                'tanggal_masuk'  => '2024-02-01',
                'kontrak_mulai'  => '2024-02-01',
                'kontrak_selesai'=> '2025-01-31',
                'durasi_kontrak_bulan' => 12,
                'harga_sewa_kontrak' => 1000000,
                'pekerjaan'      => 'Mahasiswa',
            ],
            [
                'kamar'          => $kamars[5],
                'nama_lengkap'   => 'Andi Wijaya',
                'nik'            => '3201010101850003',
                'no_hp'          => '083456789012',
                'jenis_kelamin'  => 'L',
                'tanggal_masuk'  => '2024-03-01',
                'kontrak_mulai'  => '2024-03-01',
                'kontrak_selesai'=> '2025-02-28',
                'durasi_kontrak_bulan' => 12,
                'harga_sewa_kontrak' => 1500000,
                'pekerjaan'      => 'Wiraswasta',
            ],
        ];

        foreach ($penyewaData as $data) {
            $kamar = $data['kamar'];
            unset($data['kamar']);
            $penyewa = Penyewa::create(array_merge($data, ['kamar_id' => $kamar->id, 'status' => 'aktif', 'deposit' => 0]));
            $kamar->update(['status' => 'terisi']);

            // Buat 3 bulan tagihan sewa
            for ($i = 1; $i <= 3; $i++) {
                Transaksi::create([
                    'kode_transaksi'  => 'TRX-' . now()->format('Ym') . '-' . str_pad(($penyewa->id * 100) + $i, 5, '0', STR_PAD_LEFT),
                    'penyewa_id'      => $penyewa->id,
                    'kamar_id'        => $kamar->id,
                    'jenis'           => 'sewa',
                    'keterangan'      => "Sewa bulan ke-{$i}",
                    'jumlah'          => $data['harga_sewa_kontrak'],
                    'tanggal_tagihan' => now()->startOfMonth()->subMonths(3 - $i)->toDateString(),
                    'jatuh_tempo'     => now()->startOfMonth()->subMonths(3 - $i)->addDays(9)->toDateString(),
                    'status'          => $i <= 2 ? 'lunas' : 'belum_bayar',
                    'tanggal_bayar'   => $i <= 2 ? now()->startOfMonth()->subMonths(3 - $i)->addDays(5)->toDateString() : null,
                    'metode_bayar'    => $i <= 2 ? 'transfer' : null,
                    'periode_bulan'   => now()->subMonths(3 - $i)->month,
                    'periode_tahun'   => now()->subMonths(3 - $i)->year,
                    'dibuat_oleh'     => $admin->id,
                ]);
            }
        }

        $this->command->info('✅ Seeder selesai! Data awal SIMK berhasil dibuat.');
        $this->command->info('   Admin: admin@simk.app / password123');
        $this->command->info('   Pengelola: emip@simk.app / password123');
    }
}