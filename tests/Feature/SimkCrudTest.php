<?php

namespace Tests\Feature;

use App\Models\Mahasiswa;
use App\Models\Pengguna;
use App\Models\SkemaBeasiswa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Support\Data;
use Tests\TestCase;

/** P-17 Kelola Pengguna dan perubahan data master. */
class SimkCrudTest extends TestCase
{
    use Data, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_admin_menambah_staf_dan_mahasiswa(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/pengguna', ['username' => 'staf.baru', 'nama' => 'Staf Baru', 'email' => 'sb@ukri.test',
            'role' => 'staf_beasiswa', 'password' => 'password123'])->assertSessionHas('success', 'Data pengguna berhasil disimpan.');
        $this->assertMatchesRegularExpression('/^US\.\d{2}\.\d{5}$/', Pengguna::where('username', 'staf.baru')->value('id_pengguna'));

        $this->actingAs($admin)->post('/pengguna', ['username' => 'mhs.baru', 'nama' => 'Mhs Baru', 'email' => 'mb@ukri.test',
            'role' => 'mahasiswa', 'password' => 'password123', 'npm' => '20241320099', 'program_studi' => 'SI',
            'fakultas' => 'FIKSI', 'angkatan' => 2024, 'ipk' => 3.5])->assertSessionHas('success');
        $this->assertSame('3.50', number_format(Mahasiswa::find('20241320099')->ipk, 2));
    }

    public function test_pengguna_duplikat_dan_validasi_ditolak(): void
    {
        $admin = $this->admin();
        $dasar = ['nama' => 'X', 'role' => 'staf_beasiswa', 'password' => 'password123'];

        $this->actingAs($admin)->post('/pengguna', $dasar + ['username' => 'admin1', 'email' => 'lain@ukri.test'])
            ->assertSessionHas('error', 'Username atau email sudah digunakan.');
        $this->actingAs($admin)->post('/pengguna', $dasar + ['username' => 'baru1', 'email' => 'admin1@ukri.test'])
            ->assertSessionHas('error', 'Username atau email sudah digunakan.');
        $this->actingAs($admin)->post('/pengguna', $dasar + ['username' => 'x y', 'email' => 'q@ukri.test'])->assertSessionHasErrors('username');
        $this->actingAs($admin)->post('/pengguna', ['username' => 'abc', 'nama' => 'X', 'email' => 'q@ukri.test', 'role' => 'staf_beasiswa', 'password' => 'pendek'])
            ->assertSessionHasErrors('password');
        $this->actingAs($admin)->post('/pengguna', ['username' => 'abc', 'nama' => 'X', 'email' => 'q@ukri.test', 'role' => 'mahasiswa', 'password' => 'password123'])
            ->assertSessionHasErrors('npm');
    }

    public function test_admin_reset_password_dan_nonaktifkan_akun(): void
    {
        $admin = $this->admin();
        $staf = $this->staf();

        $this->actingAs($admin)->put("/pengguna/{$staf->id_pengguna}", ['nama' => 'Staf', 'email' => 'staf1@ukri.test',
            'password' => 'passwordbaru1', 'status_aktif' => 0])->assertSessionHas('success');
        $staf->refresh();
        $this->assertFalse($staf->status_aktif);
        $this->assertTrue(\Hash::check('passwordbaru1', $staf->password));

        $this->post('/logout');
        $this->post('/login', ['username' => 'staf1', 'password' => 'passwordbaru1'])->assertSessionHasErrors('username');

        // tidak dapat menonaktifkan diri sendiri
        $this->actingAs($admin)->put("/pengguna/{$admin->id_pengguna}", ['nama' => 'A', 'email' => 'admin1@ukri.test', 'status_aktif' => 0])
            ->assertSessionHas('error');
    }

    public function test_akun_dinonaktifkan_saat_sesi_berjalan_langsung_dikeluarkan(): void
    {
        $staf = $this->staf();
        $this->actingAs($staf)->get('/mitra')->assertOk();
        $staf->update(['status_aktif' => false]);
        $this->actingAs($staf->fresh())->get('/mitra')->assertRedirect(route('login'));
    }

    public function test_skema_tidak_dapat_diubah_setelah_memiliki_penerima(): void
    {
        $staf = $this->staf();
        $s = $this->skema();
        $m = $this->mahasiswa('m1', '20241320001');
        $p = \App\Models\Pendaftaran::create(['id_pendaftaran' => 'PD.26.00001', 'npm' => $m->npm, 'id_skema' => $s->id_skema,
            'tgl_daftar' => now(), 'ipk_saat_daftar' => 3.8, 'status' => 'diterima']);
        \App\Models\Penerima::create(['id_penerima' => 'PN.26.00001', 'id_pendaftaran' => $p->id_pendaftaran, 'id_skema' => $s->id_skema,
            'npm' => $m->npm, 'no_sk' => 'SK', 'tgl_penetapan' => now()->toDateString()]);

        $this->actingAs($staf)->put("/skema/{$s->id_skema}", [
            'nama_skema' => 'Ubah', 'jenis' => 'prestasi', 'kuota' => 5, 'nominal_per_penerima' => 1, 'ipk_minimum' => 3,
            'bobot_akademik' => 40, 'bobot_nonakademik' => 30, 'bobot_kebutuhan' => 30,
            'tgl_buka' => now()->toDateString(), 'tgl_tutup' => now()->addDay()->toDateString(), 'aturan_penyaluran' => 'sekaligus',
        ])->assertSessionHas('error', 'Skema yang sudah memiliki penerima tidak dapat diubah.');
        $this->assertSame('Skema Uji', $s->fresh()->nama_skema);
    }

    public function test_perpanjang_kontrak_harus_setelah_tanggal_berakhir_lama(): void
    {
        $staf = $this->staf();
        $k = $this->kontrakAktif($this->mitraTerverifikasi(), 20);

        $this->actingAs($staf)->post("/kontrak/{$k->id_kontrak}/perpanjang", ['tgl_berakhir' => now()->addDays(5)->toDateString()])
            ->assertSessionHasErrors('tgl_berakhir');
        $this->actingAs($staf)->post("/kontrak/{$k->id_kontrak}/perpanjang", ['tgl_berakhir' => now()->addYear()->toDateString()])
            ->assertSessionHas('success');
        $this->assertSame('diperpanjang', $k->fresh()->status);
    }
}
