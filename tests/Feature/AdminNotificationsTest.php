<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Notifikasi;
use App\Models\Penyewa;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AdminNotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'pengelola']);
        Role::firstOrCreate(['name' => 'penyewa']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_registration_creates_admin_notification(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Utama',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);
        $admin->assignRole('admin');

        $response = $this->post(route('register.store'), [
            'name' => 'Budi Baru',
            'email' => 'budi-baru@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nik' => '3201010101010003',
            'no_hp' => '081122334455',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('notifikasi', [
            'user_id' => $admin->id,
            'tipe' => 'registrasi',
            'judul' => 'Registrasi Penyewa Baru',
        ]);
    }

    public function test_payment_creates_admin_notification(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Bayar',
            'email' => 'admin-bayar@example.com',
            'role' => 'admin',
        ]);
        $admin->assignRole('admin');

        $tenantUser = User::factory()->create([
            'name' => 'Penyewa Bayar',
            'email' => 'tenant@example.com',
            'role' => 'penyewa',
        ]);
        $tenantUser->assignRole('penyewa');

        $kamar = Kamar::create([
            'nomor_kamar' => 'B-201',
            'nama_kamar' => 'Kamar Bayar',
            'tipe' => 'standar',
            'harga_sewa' => 900000,
            'luas_m2' => 24,
            'lantai' => 2,
            'status' => 'terisi',
        ]);

        $penyewa = Penyewa::create([
            'user_id' => $tenantUser->id,
            'kamar_id' => $kamar->id,
            'nama_lengkap' => 'Penyewa Bayar',
            'nik' => '3201010101010004',
            'no_hp' => '081122334466',
            'email' => $tenantUser->email,
            'jenis_kelamin' => 'L',
            'tanggal_masuk' => now()->toDateString(),
            'kontrak_mulai' => now()->toDateString(),
            'kontrak_selesai' => now()->addMonth()->toDateString(),
            'status' => 'aktif',
        ]);

        $transaksi = Transaksi::create([
            'penyewa_id' => $penyewa->id,
            'kamar_id' => $kamar->id,
            'jenis' => 'sewa',
            'jumlah' => 900000,
            'tanggal_tagihan' => now()->toDateString(),
            'jatuh_tempo' => now()->addDays(7)->toDateString(),
            'status' => 'belum_bayar',
            'dibuat_oleh' => $admin->id,
        ]);

        $response = $this->actingAs($tenantUser)->post(route('penyewa.tagihan.bayar', ['transaksi' => $transaksi->id]), [
            'metode_bayar' => 'transfer',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('notifikasi', [
            'user_id' => $admin->id,
            'tipe' => 'pembayaran',
            'judul' => 'Pembayaran Dikonfirmasi',
        ]);
    }
}
