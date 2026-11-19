<?php

namespace Tests\Feature;

use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Smoke test: setiap halaman tiap peran dapat dirender dengan data demo (seeder). */
class SimkPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->seed();
    }

    private function user(string $username): Pengguna
    {
        return Pengguna::where('username', $username)->firstOrFail();
    }

    public function test_halaman_admin_wr3(): void
    {
        $a = $this->user('admin.wr3');
        foreach (['/admin/dashboard', '/pengguna', '/mitra', '/kontrak', '/komitmen', '/skema', '/seleksi/verifikasi',
            '/seleksi/penilaian', '/seleksi/penetapan', '/penyaluran', '/laporan', '/ekspor', '/monitoring', '/notifikasi'] as $url) {
            $this->actingAs($a)->get($url)->assertOk();
        }
    }

    public function test_halaman_staf_beasiswa_dan_detail(): void
    {
        $s = $this->user('staf.beasiswa');
        $this->actingAs($s)->get('/beranda')->assertOk();

        $skema = \App\Models\SkemaBeasiswa::where('status', 'dibuka')->first();
        $this->actingAs($s)->get('/seleksi/penilaian?skema='.$skema->id_skema)->assertOk();
        $this->actingAs($s)->get('/seleksi/penetapan?skema='.$skema->id_skema)->assertOk();

        $lalu = \App\Models\SkemaBeasiswa::where('nama_skema', 'like', '%Semester Lalu%')->first();
        $this->actingAs($s)->get('/penyaluran?skema='.$lalu->id_skema.'&termin=1')->assertOk()->assertSee('Data rekening penerima belum lengkap.');

        $p = \App\Models\Pendaftaran::where('status', 'diajukan')->first();
        $this->actingAs($s)->get('/seleksi/verifikasi/'.$p->id_pendaftaran)->assertOk()->assertSee('Buka berkas');
    }

    public function test_halaman_mahasiswa(): void
    {
        $m = $this->user('mhs5');   // penerima semester lalu dengan penyaluran tersalurkan
        $this->actingAs($m)->get('/pendaftaran/status')->assertOk();
        $this->actingAs($m)->get('/pendaftaran')->assertOk()->assertSee('Beasiswa Prestasi Akademik 2026');
        $this->actingAs($m)->get('/penyaluran/saya')->assertOk()->assertSee('Saya sudah menerima');
        $this->actingAs($m)->get('/profil')->assertOk()->assertSee('******7890');
    }

    public function test_halaman_mahasiswa_tanpa_pendaftaran_menampilkan_pesan_kosong(): void
    {
        $this->actingAs($this->user('mhs7'))->get('/pendaftaran/status')->assertOk()->assertSee('Anda belum memiliki pendaftaran');
    }

    public function test_halaman_mitra(): void
    {
        $mitra = Pengguna::where('role', 'mitra')->first();
        $this->actingAs($mitra)->get('/laporan')->assertOk();
        $this->actingAs($mitra)->get('/komitmen')->assertOk()->assertSee('Total Komitmen');
        $this->actingAs($mitra)->get('/mitra')->assertRedirect(route('dashboard'));
    }
}
