<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Penyewa;
use App\Models\TarifUtilitas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SimkCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'pengelola']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_admin_can_create_kamar(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->post(route('simk.kamar.store'), [
            'nomor_kamar' => 'A-101',
            'nama_kamar' => 'Kamar Utama',
            'tipe' => 'standar',
            'harga_sewa' => 800000,
            'luas_m2' => 24,
            'lantai' => 1,
            'fasilitas' => 'AC, Kamar Mandi',
            'deskripsi' => 'Kamar nyaman',
            'status' => 'tersedia',
        ]);

        $response->assertRedirect(route('simk.kamar.index'));
        $this->assertDatabaseHas('kamar', [
            'nomor_kamar' => 'A-101',
            'nama_kamar' => 'Kamar Utama',
            'status' => 'tersedia',
        ]);
    }

    public function test_admin_can_create_penyewa(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $kamar = Kamar::create([
            'nomor_kamar' => 'A-102',
            'nama_kamar' => 'Kamar Deluxe',
            'tipe' => 'deluxe',
            'harga_sewa' => 1200000,
            'luas_m2' => 30,
            'lantai' => 2,
            'status' => 'tersedia',
        ]);

        $response = $this->actingAs($user)->post(route('simk.penyewa.store'), [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'no_hp' => '081234567890',
            'nik' => '3201010101010001',
            'kamar_id' => $kamar->id,
            'tanggal_masuk' => now()->toDateString(),
            'durasi_kontrak' => 6,
            'harga_sewa_kontrak' => 1200000,
            'alamat_asal' => 'Bandung',
            'nama_penjamin' => 'Sari',
            'no_hp_penjamin' => '081111222333',
        ]);

        $response->assertRedirect(route('simk.penyewa.index'));
        $this->assertDatabaseHas('users', ['email' => 'budi@example.com']);
        $this->assertDatabaseHas('penyewa', ['nik' => '3201010101010001', 'kamar_id' => $kamar->id]);
    }

    public function test_admin_can_create_transaksi(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $kamar = Kamar::create([
            'nomor_kamar' => 'A-103',
            'nama_kamar' => 'Kamar Transaksi',
            'tipe' => 'standar',
            'harga_sewa' => 900000,
            'luas_m2' => 20,
            'lantai' => 1,
            'status' => 'terisi',
        ]);

        $penyewa = Penyewa::create([
            'kamar_id' => $kamar->id,
            'user_id' => $user->id,
            'nama_lengkap' => 'Citra',
            'jenis_kelamin' => 'P',
            'no_hp' => '081122334455',
            'email' => 'citra@example.com',
            'nik' => '3201010101010002',
            'tanggal_masuk' => now()->subMonth()->toDateString(),
            'kontrak_mulai' => now()->subMonth()->toDateString(),
            'kontrak_selesai' => now()->addMonths(6)->toDateString(),
            'durasi_kontrak_bulan' => 6,
            'harga_sewa_kontrak' => 900000,
            'alamat_asal' => 'Bogor',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($user)->post(route('simk.transaksi.store'), [
            'penyewa_id' => $penyewa->id,
            'kamar_id' => $kamar->id,
            'jenis' => 'sewa',
            'keterangan' => 'Tagihan bulan ini',
            'jumlah' => 900000,
            'tanggal_tagihan' => now()->toDateString(),
            'jatuh_tempo' => now()->addDays(7)->toDateString(),
            'periode_bulan' => now()->month,
            'periode_tahun' => now()->year,
            'catatan' => 'Otomatis',
        ]);

        $response->assertRedirect(route('simk.transaksi.index'));
        $this->assertDatabaseHas('transaksi', ['penyewa_id' => $penyewa->id, 'kamar_id' => $kamar->id, 'jenis' => 'sewa']);
    }

    public function test_admin_can_create_utilitas(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $kamar = Kamar::create([
            'nomor_kamar' => 'A-104',
            'nama_kamar' => 'Kamar Utilitas',
            'tipe' => 'premium',
            'harga_sewa' => 1400000,
            'luas_m2' => 35,
            'lantai' => 3,
            'status' => 'terisi',
        ]);

        TarifUtilitas::create([
            'jenis' => 'listrik',
            'harga_per_unit' => 1500,
            'satuan' => 'kWh',
            'berlaku_mulai' => now()->subMonth()->toDateString(),
            'is_active' => true,
            'keterangan' => 'Tarif listrik',
        ]);

        $response = $this->actingAs($user)->post(route('simk.utilitas.store'), [
            'kamar_id' => $kamar->id,
            'penyewa_id' => null,
            'jenis' => 'listrik',
            'periode_bulan' => now()->month,
            'periode_tahun' => now()->year,
            'meteran_awal' => 100,
            'meteran_akhir' => 120,
            'tanggal_catat' => now()->toDateString(),
            'catatan' => 'Tagihan utilitas',
        ]);

        $response->assertRedirect(route('simk.utilitas.index'));
        $this->assertDatabaseHas('utilitas', ['kamar_id' => $kamar->id, 'jenis' => 'listrik']);
    }
}
