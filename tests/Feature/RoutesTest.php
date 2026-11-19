<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Seluruh rute terlindungi login; halaman publik hanya login. */
class RoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_login_dapat_dibuka_tanpa_error(): void
    {
        $this->get('/login')->assertOk()->assertSee('SIM-BEASISWA');
    }

    public function test_beranda_publik_mengarah_ke_login(): void
    {
        $this->get('/')->assertRedirect(route('dashboard'));
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_seluruh_halaman_terlindungi_mengalihkan_tamu_ke_login(): void
    {
        foreach ([
            '/admin/dashboard', '/beranda', '/pengguna', '/mitra', '/kontrak', '/komitmen', '/skema',
            '/seleksi/verifikasi', '/seleksi/penilaian', '/seleksi/penetapan', '/penyaluran', '/laporan',
            '/ekspor', '/monitoring', '/pendaftaran', '/pendaftaran/status', '/penyaluran/saya', '/profil', '/notifikasi',
        ] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
    }

    public function test_form_login_mengarah_ke_rute_autentikasi(): void
    {
        $this->get('/login')->assertSee(route('login.store'), false);
    }
}
