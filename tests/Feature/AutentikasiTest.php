<?php

namespace Tests\Feature;

use App\Models\LogAktivitas;
use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Data;
use Tests\TestCase;

/** P-01 / ALGO-01 Login dan Autentikasi. */
class AutentikasiTest extends TestCase
{
    use Data, RefreshDatabase;

    public function test_halaman_login_tampil_untuk_tamu(): void
    {
        $this->get('/login')->assertOk()->assertSee('Username');
    }

    public function test_login_berhasil_mengarahkan_sesuai_role_dan_mencatat_log(): void
    {
        $this->admin();
        $this->mahasiswa('mhs1', '20241320001');

        $this->post('/login', ['username' => 'admin1', 'password' => 'rahasia123'])->assertRedirect(route('dashboard'));
        $this->get('/dashboard')->assertRedirect(route('dashboard.admin'));
        $this->post('/logout');

        $this->post('/login', ['username' => 'mhs1', 'password' => 'rahasia123']);
        $this->get('/dashboard')->assertRedirect(route('pendaftaran.status'));

        $this->assertNotNull(Pengguna::where('username', 'admin1')->first()->last_login);
        $this->assertTrue(LogAktivitas::where('aksi', 'login')->exists());
    }

    public function test_password_disimpan_bcrypt_bukan_plaintext(): void
    {
        $p = $this->admin();
        $this->assertStringStartsWith('$2y$', $p->password);
        $this->assertNotSame('rahasia123', $p->password);
    }

    public function test_login_gagal_menampilkan_pesan_dan_mencatat_log(): void
    {
        $this->admin();
        $this->from('/login')->post('/login', ['username' => 'admin1', 'password' => 'salah'])
            ->assertRedirect('/login')->assertSessionHasErrors(['username' => 'Username atau password salah.']);
        $this->assertGuest();
        $this->assertTrue(LogAktivitas::where('aksi', 'login_gagal')->exists());
    }

    public function test_akun_nonaktif_ditolak(): void
    {
        $this->akun(Pengguna::ROLE_STAF, 'nonaktif', false);
        $this->post('/login', ['username' => 'nonaktif', 'password' => 'rahasia123'])
            ->assertSessionHasErrors(['username' => 'Akun Anda dinonaktifkan. Hubungi admin.']);
        $this->assertGuest();
    }

    public function test_akun_terkunci_setelah_lima_kali_gagal(): void
    {
        $this->admin();
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['username' => 'admin1', 'password' => 'salah']);
        }
        // Percobaan ke-6 dengan password BENAR tetap ditolak karena terkunci
        $this->post('/login', ['username' => 'admin1', 'password' => 'rahasia123'])
            ->assertSessionHasErrors('username');
        $this->assertGuest();
        $this->assertStringContainsString('terkunci', session('errors')->first('username'));
    }

    public function test_tamu_dialihkan_ke_login_dan_role_dibatasi(): void
    {
        $this->get('/mitra')->assertRedirect('/login');

        $mhs = $this->mahasiswa('mhs1', '20241320001');
        $this->actingAs($mhs->pengguna)->get('/mitra')->assertRedirect(route('dashboard'));
        $this->actingAs($mhs->pengguna)->get('/pengguna')->assertRedirect(route('dashboard'));

        $staf = $this->staf();
        $this->actingAs($staf)->get('/mitra')->assertOk();
        $this->actingAs($staf)->get('/pengguna')->assertRedirect(route('dashboard'));   // khusus admin

        $this->actingAs($this->akun(Pengguna::ROLE_ADMIN, 'adminx'))->get('/mitra')->assertOk();   // admin mewarisi staf
    }
}
