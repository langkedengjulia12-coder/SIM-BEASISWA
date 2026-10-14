<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Penyewa;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SimkPagesTest extends TestCase
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

    public function test_admin_can_view_kamar_index_and_create_pages(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)->get(route('simk.kamar.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('simk.kamar.create'))->assertStatus(200);
    }

    public function test_admin_can_view_penyewa_index_and_create_pages(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        Kamar::create([
            'nomor_kamar' => 'B-01',
            'nama_kamar' => 'Kamar B',
            'tipe' => 'standar',
            'harga_sewa' => 900000,
            'luas_m2' => 20,
            'lantai' => 1,
            'status' => 'tersedia',
        ]);

        $this->actingAs($user)->get(route('simk.penyewa.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('simk.penyewa.create'))->assertStatus(200);
    }

    public function test_admin_role_user_can_access_admin_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)->get(route('admin.dashboard'))->assertStatus(200);
    }

    public function test_penyewa_dashboard_shows_bill_and_available_room_info(): void
    {
        $user = User::factory()->create();
        $user->assignRole('penyewa');

        $kamar = Kamar::create([
            'nomor_kamar' => 'A-01',
            'nama_kamar' => 'Kamar A',
            'tipe' => 'standar',
            'harga_sewa' => 800000,
            'luas_m2' => 18,
            'lantai' => 1,
            'status' => 'tersedia',
        ]);

        $penyewa = Penyewa::create([
            'kamar_id' => $kamar->id,
            'user_id' => $user->id,
            'nama_lengkap' => 'Penyewa Uji',
            'nik' => '1234567890123456',
            'no_hp' => '081234567890',
            'email' => 'penyewa@example.com',
            'jenis_kelamin' => 'L',
            'alamat_asal' => 'Alamat test',
            'tanggal_masuk' => now()->toDateString(),
            'kontrak_mulai' => now()->toDateString(),
            'kontrak_selesai' => now()->addMonths(3)->toDateString(),
            'status' => 'aktif',
            'durasi_kontrak_bulan' => 3,
        ]);

        Transaksi::create([
            'penyewa_id' => $penyewa->id,
            'kamar_id' => $kamar->id,
            'dibuat_oleh' => $user->id,
            'jenis' => 'sewa',
            'keterangan' => 'Tagihan bulan ini',
            'jumlah' => 800000,
            'tanggal_tagihan' => now()->toDateString(),
            'jatuh_tempo' => now()->addDays(7)->toDateString(),
            'status' => 'belum_bayar',
            'periode_bulan' => now()->month,
            'periode_tahun' => now()->year,
        ]);

        $response = $this->actingAs($user)->get(route('penyewa.dashboard'));

        $response->assertStatus(200);
        $response->assertSeeText('Tagihan Bulan Ini');
        $response->assertSeeText('Kamar Kosong');
        $response->assertSeeText('Tunggakan');
    }

    public function test_tenant_is_redirected_to_dashboard_from_home_page(): void
    {
        $user = User::factory()->create();
        $user->assignRole('penyewa');

        $this->actingAs($user)->get(route('home'))->assertRedirect(route('penyewa.dashboard'));
    }

    public function test_tenant_can_select_available_room_from_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole('penyewa');

        $kamar = Kamar::create([
            'nomor_kamar' => 'C-01',
            'nama_kamar' => 'Kamar C',
            'tipe' => 'standar',
            'harga_sewa' => 850000,
            'luas_m2' => 20,
            'lantai' => 2,
            'status' => 'tersedia',
        ]);

        $penyewa = Penyewa::create([
            'kamar_id' => null,
            'user_id' => $user->id,
            'nama_lengkap' => 'Penyewa Pilih Kamar',
            'nik' => '1234567890123456',
            'no_hp' => '081234567890',
            'email' => 'pilihkamar@example.com',
            'jenis_kelamin' => 'L',
            'alamat_asal' => 'Alamat test',
            'tanggal_masuk' => now()->toDateString(),
            'kontrak_mulai' => now()->toDateString(),
            'kontrak_selesai' => now()->addMonths(3)->toDateString(),
            'status' => 'aktif',
            'durasi_kontrak_bulan' => 3,
        ]);

        $this->actingAs($user)
            ->post(route('penyewa.kamar.pilih', $kamar))
            ->assertRedirect(route('penyewa.dashboard'));

        $penyewa->refresh();
        $this->assertSame($kamar->id, $penyewa->kamar_id);
        $this->assertSame('terisi', $kamar->fresh()->status);
    }

    public function test_dashboard_and_penyewa_pages_render_when_penyewa_has_no_user(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $kamar = Kamar::create([
            'nomor_kamar' => 'B-02',
            'nama_kamar' => 'Kamar B 2',
            'tipe' => 'standar',
            'harga_sewa' => 950000,
            'luas_m2' => 22,
            'lantai' => 1,
            'status' => 'tersedia',
        ]);

        Penyewa::create([
            'kamar_id' => $kamar->id,
            'user_id' => null,
            'nama_lengkap' => 'Tanpa User',
            'nik' => '1234567890123456',
            'no_hp' => '081234567890',
            'email' => 'tanpauser@example.com',
            'jenis_kelamin' => 'L',
            'alamat_asal' => 'Alamat test',
            'tanggal_masuk' => now()->toDateString(),
            'kontrak_mulai' => now()->toDateString(),
            'kontrak_selesai' => now()->addMonths(3)->toDateString(),
            'status' => 'aktif',
            'durasi_kontrak_bulan' => 3,
        ]);

        $this->actingAs($user)->get(route('simk.dashboard'))->assertStatus(200);
        $this->actingAs($user)->get(route('simk.penyewa.index'))->assertStatus(200);
    }
}
