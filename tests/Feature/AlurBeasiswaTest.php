<?php

namespace Tests\Feature;

use App\Models\DokumenPendaftaran;
use App\Models\Laporan;
use App\Models\Mitra;
use App\Models\Notifikasi;
use App\Models\Pendaftaran;
use App\Models\Penerima;
use App\Models\Pengguna;
use App\Models\Penilaian;
use App\Models\Penyaluran;
use App\Models\SkemaBeasiswa;
use App\Models\StatusAkademik;
use App\Support\Kode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Support\Data;
use Tests\TestCase;

/**
 * Pengujian fungsional alur utama SKPL:
 * mitra -> kontrak -> komitmen -> skema -> pendaftaran -> verifikasi -> penilaian
 * -> penetapan -> penyaluran -> konfirmasi -> monitoring -> pelaporan -> ekspor.
 */
class AlurBeasiswaTest extends TestCase
{
    use Data, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    // ---------------------------------------------------------------- P-02 s.d. P-05

    public function test_mitra_duplikat_ditolak_dan_verifikasi_membuat_akun_mitra(): void
    {
        $staf = $this->staf();
        $data = ['nama_mitra' => 'PT Baru', 'jenis_mitra' => 'industri', 'nama_pic' => 'Ani', 'email_pic' => 'ani@baru.test'];

        $this->actingAs($staf)->post('/mitra', $data)->assertSessionHas('success', 'Data mitra berhasil disimpan.');
        $m = Mitra::first();
        $this->assertMatchesRegularExpression('/^MT\.\d{2}\.0001$/', $m->id_mitra);
        $this->assertSame('menunggu', $m->status_verifikasi);

        $this->actingAs($staf)->post('/mitra', $data)
            ->assertSessionHas('error', 'Mitra dengan nama dan email PIC tersebut sudah terdaftar.');
        $this->assertSame(1, Mitra::count());

        $this->actingAs($staf)->post("/mitra/{$m->id_mitra}/verifikasi", ['status' => 'terverifikasi'])->assertSessionHas('success');
        $m->refresh();
        $this->assertSame('terverifikasi', $m->status_verifikasi);
        $akun = Pengguna::find($m->id_pengguna);
        $this->assertSame('mitra', $akun->role);
        $this->assertSame('ani@baru.test', $akun->email);
    }

    public function test_kontrak_validasi_tanggal_dan_penyimpanan_berkas_terenkripsi(): void
    {
        $staf = $this->staf();
        $m = $this->mitraTerverifikasi();
        $dasar = ['id_mitra' => $m->id_mitra, 'no_dokumen' => 'MoU-1', 'judul' => 'Judul', 'jenis_dokumen' => 'MoU',
            'nilai_komitmen' => 50000000, 'status' => 'aktif'];

        $this->actingAs($staf)->post('/kontrak', $dasar + ['tgl_mulai' => '2026-06-01', 'tgl_berakhir' => '2026-05-01'])
            ->assertSessionHasErrors(['tgl_berakhir' => 'Tanggal berakhir harus setelah tanggal mulai.']);

        $this->actingAs($staf)->post('/kontrak', $dasar + [
            'tgl_mulai' => now()->toDateString(), 'tgl_berakhir' => now()->addYear()->toDateString(),
            'file_dokumen' => $this->berkas('mou.pdf'),
        ])->assertSessionHas('success', 'Kontrak berhasil disimpan.');

        $k = \App\Models\Kontrak::first();
        [$path] = explode('|', $k->file_dokumen);
        Storage::disk('local')->assertExists($path);
        $this->assertStringNotContainsString('%PDF', Storage::disk('local')->get($path));    // terenkripsi
        $this->actingAs($staf)->get("/kontrak/{$k->id_kontrak}/berkas")->assertOk();         // dapat didekripsi
    }

    public function test_komitmen_tidak_boleh_melebihi_dan_skema_butuh_dana_serta_bobot_100(): void
    {
        $staf = $this->staf();
        $k = $this->kontrakAktif($this->mitraTerverifikasi());
        $bobot = ['bobot_akademik' => 40, 'bobot_nonakademik' => 30, 'bobot_kebutuhan' => 30];
        $skemaData = ['nama_skema' => 'Prestasi', 'jenis' => 'prestasi', 'kuota' => 2, 'nominal_per_penerima' => 1000000,
            'ipk_minimum' => 3.5, 'tgl_buka' => now()->toDateString(), 'tgl_tutup' => now()->addDays(10)->toDateString(),
            'aturan_penyaluran' => 'per_semester'];

        // bobot salah
        $this->actingAs($staf)->post('/skema', $skemaData + ['bobot_akademik' => 50, 'bobot_nonakademik' => 30, 'bobot_kebutuhan' => 30])
            ->assertSessionHas('error', 'Total bobot kriteria harus 100%.');

        $this->actingAs($staf)->post('/skema', $skemaData + $bobot)->assertSessionHas('success', 'Skema berhasil disimpan.');
        $s = SkemaBeasiswa::first();
        $this->assertSame('draf', $s->status);
        $this->assertSame(4000000.0, $s->kebutuhan_dana);   // 2 x 1jt x 2 termin

        // dana kurang: belum ada komitmen
        $this->actingAs($staf)->post("/skema/{$s->id_skema}/buka")
            ->assertSessionHas('error', 'Kuota x nominal melebihi komitmen dana yang tersedia.');

        // komitmen terkait skema (3jt < 4jt) -> tetap kurang
        $this->actingAs($staf)->post('/komitmen', ['id_kontrak' => $k->id_kontrak, 'id_skema' => $s->id_skema,
            'tgl_komitmen' => now()->toDateString(), 'nominal_komitmen' => 3000000])->assertSessionHas('success');
        $this->actingAs($staf)->post("/skema/{$s->id_skema}/buka")->assertSessionHas('error');

        // tambah komitmen -> cukup
        $this->actingAs($staf)->post('/komitmen', ['id_kontrak' => $k->id_kontrak, 'id_skema' => $s->id_skema,
            'tgl_komitmen' => now()->toDateString(), 'nominal_komitmen' => 2000000]);
        $this->actingAs($staf)->post("/skema/{$s->id_skema}/buka")->assertSessionHas('success');
        $this->assertSame('dibuka', $s->fresh()->status);

        // catat penerimaan dana (ALGO-04)
        $kd = \App\Models\KomitmenDana::orderBy('id_komitmen')->first();       // komitmen 3jt
        $this->actingAs($staf)->post("/komitmen/{$kd->id_komitmen}/penerimaan", ['jumlah' => 3500000])
            ->assertSessionHas('error', 'Nominal diterima melebihi nominal komitmen.');
        $this->actingAs($staf)->post("/komitmen/{$kd->id_komitmen}/penerimaan", ['jumlah' => 3000000])
            ->assertSessionHas('success', 'Penerimaan dana berhasil dicatat.');
        $this->assertSame(3000000.0, $kd->fresh()->nominal_diterima);
    }

    // ---------------------------------------------------------------- P-06 s.d. P-10

    public function test_pendaftaran_validasi_ipk_dokumen_dan_duplikat(): void
    {
        $s = $this->skema();                                       // ipk min 3.5, prestasi
        $rendah = $this->mahasiswa('rendah', '20241320010', 3.2);
        $baik = $this->mahasiswa('baik', '20241320011', 3.8);

        $this->actingAs($rendah->pengguna)->post('/pendaftaran', ['id_skema' => $s->id_skema, 'dokumen' => $this->dokumenPrestasi()])
            ->assertSessionHas('error', 'IPK Anda belum memenuhi syarat minimum skema.');

        // dokumen kurang (tanpa sertifikat)
        $r = $this->actingAs($baik->pengguna)->post('/pendaftaran', ['id_skema' => $s->id_skema,
            'dokumen' => ['transkrip' => $this->berkas(), 'ktm' => $this->berkas()]]);
        $this->assertStringContainsString('Dokumen wajib belum lengkap', session('error'));
        $this->assertSame(0, Pendaftaran::count());

        // sukses
        $this->actingAs($baik->pengguna)->post('/pendaftaran', ['id_skema' => $s->id_skema, 'motivasi' => 'Saya ingin', 'dokumen' => $this->dokumenPrestasi()])
            ->assertRedirect(route('pendaftaran.status'));
        $this->assertStringContainsString('Pendaftaran berhasil dikirim. Nomor: PD.', session('success'));
        $p = Pendaftaran::first();
        $this->assertSame('diajukan', $p->status);
        $this->assertSame(3, $p->dokumen()->count());
        $this->assertTrue(Notifikasi::where('id_pengguna', $baik->id_pengguna)->exists());

        // duplikat
        $this->actingAs($baik->pengguna)->post('/pendaftaran', ['id_skema' => $s->id_skema, 'dokumen' => $this->dokumenPrestasi()])
            ->assertSessionHas('error', 'Anda sudah mendaftar pada skema ini.');
        $this->assertSame(1, Pendaftaran::count());

        // skema ditutup
        $s->update(['status' => 'ditutup']);
        $lain = $this->mahasiswa('lain', '20241320012', 3.9);
        $this->actingAs($lain->pengguna)->post('/pendaftaran', ['id_skema' => $s->id_skema, 'dokumen' => $this->dokumenPrestasi()])
            ->assertSessionHas('error', 'Skema ini tidak sedang membuka pendaftaran.');
    }

    public function test_dokumen_mahasiswa_lain_tidak_dapat_diakses(): void
    {
        $s = $this->skema();
        $a = $this->mahasiswa('a', '20241320021');
        $b = $this->mahasiswa('b', '20241320022');
        $this->actingAs($a->pengguna)->post('/pendaftaran', ['id_skema' => $s->id_skema, 'dokumen' => $this->dokumenPrestasi()]);
        $dok = DokumenPendaftaran::first();

        $this->actingAs($a->pengguna)->get("/dokumen/{$dok->id_dokumen}")->assertOk();
        $this->actingAs($b->pengguna)->get("/dokumen/{$dok->id_dokumen}")->assertForbidden();
        $this->actingAs($this->staf())->get("/dokumen/{$dok->id_dokumen}")->assertOk();
    }

    // ---------------------------------------------------------------- Alur penuh

    public function test_alur_lengkap_verifikasi_hingga_penyaluran_dan_pelaporan(): void
    {
        $staf = $this->staf();
        $admin = $this->admin();

        // ---------- data dasar ----------
        $mitra = $this->mitraTerverifikasi('PT Donor');
        $akunMitra = $this->akun(Pengguna::ROLE_MITRA, 'mitra1');
        $mitra->update(['id_pengguna' => $akunMitra->id_pengguna]);
        $kontrak = $this->kontrakAktif($mitra);
        $s = $this->skema(['kuota' => 2, 'nominal_per_penerima' => 1000000]);
        $this->komitmen($kontrak, $s, 10000000, 10000000);

        $mA = $this->mahasiswa('anisa', '20241320031', 3.90);
        $mB = $this->mahasiswa('budi', '20241320032', 3.60);
        $mC = $this->mahasiswa('citra', '20241320033', 3.70);
        $mD = $this->mahasiswa('dodi', '20241320034', 3.55);
        foreach ([$mA, $mB, $mC, $mD] as $m) {
            $this->actingAs($m->pengguna)->post('/pendaftaran', ['id_skema' => $s->id_skema, 'dokumen' => $this->dokumenPrestasi()]);
        }
        $this->assertSame(4, Pendaftaran::where('status', 'diajukan')->count());
        $pd = fn ($m) => Pendaftaran::where('npm', $m->npm)->first();

        // ---------- P-07 verifikasi ----------
        $this->actingAs($staf)->get('/seleksi/verifikasi')->assertOk()->assertSee('Anisa');

        // belum semua dokumen diperiksa
        $a = $pd($mA);
        $this->actingAs($staf)->post("/seleksi/verifikasi/{$a->id_pendaftaran}", ['hasil' => [Kode::kunci($a->dokumen[0]->id_dokumen) => 'valid']])
            ->assertSessionHas('error', 'Seluruh dokumen harus diperiksa sebelum menetapkan hasil.');

        $semuaValid = fn ($p) => ['hasil' => $p->dokumen->mapWithKeys(fn ($d) => [Kode::kunci($d->id_dokumen) => 'valid'])->all()];
        foreach ([$mA, $mB, $mC] as $m) {
            $p = $pd($m)->load('dokumen');
            $this->actingAs($staf)->post("/seleksi/verifikasi/{$p->id_pendaftaran}", $semuaValid($p))->assertSessionHas('success');
            $this->assertSame('lolos_administrasi', $p->fresh()->status);
        }

        // dokumen tidak valid tanpa catatan -> ditolak sistem
        $d = $pd($mD)->load('dokumen');
        $hasil = $semuaValid($d);
        $hasil['hasil'][Kode::kunci($d->dokumen[0]->id_dokumen)] = 'tidak_valid';
        $this->actingAs($staf)->post("/seleksi/verifikasi/{$d->id_pendaftaran}", $hasil)->assertSessionHas('error');
        $this->assertSame('diajukan', $d->fresh()->status);
        $this->actingAs($staf)->post("/seleksi/verifikasi/{$d->id_pendaftaran}", $hasil + ['catatan' => 'Transkrip buram'])->assertSessionHas('success');
        $this->assertSame('tidak_lengkap', $d->fresh()->status);

        // mahasiswa D memperbaiki berkas -> diajukan -> lolos
        $salah = $d->fresh()->dokumen()->where('status_verifikasi', 'tidak_valid')->first();
        $this->actingAs($mD->pengguna)->post("/pendaftaran/{$d->id_pendaftaran}/perbaiki", ['perbaikan' => [Kode::kunci($salah->id_dokumen) => $this->berkas('baru.pdf')]])
            ->assertSessionHas('success');
        $this->assertSame('diajukan', $d->fresh()->status);
        $d = $d->fresh()->load('dokumen');
        $this->actingAs($staf)->post("/seleksi/verifikasi/{$d->id_pendaftaran}", $semuaValid($d))->assertSessionHas('success');

        // ---------- P-08 penilaian ----------
        // skor tidak valid
        $skor = [];
        foreach ([[$mA, 90, 80, 70], [$mB, 60, 60, 60], [$mC, 85, 85, 85], [$mD, 70, 70, 70]] as [$m, $ak, $non, $keb]) {
            $skor[Kode::kunci($pd($m)->id_pendaftaran)] = ['akademik' => $ak, 'nonakademik' => $non, 'kebutuhan' => $keb];
        }
        $salahSkor = $skor;
        $salahSkor[Kode::kunci($pd($mA)->id_pendaftaran)]['akademik'] = 150;
        $this->actingAs($staf)->post("/seleksi/penilaian/{$s->id_skema}", ['skor' => $salahSkor])
            ->assertSessionHasErrors('skor.'.Kode::kunci($pd($mA)->id_pendaftaran).'.akademik');

        $this->actingAs($staf)->post("/seleksi/penilaian/{$s->id_skema}", ['skor' => $skor])->assertSessionHas('success', 'Peringkat berhasil dihitung.');
        $rank = fn ($m) => Penilaian::where('id_pendaftaran', $pd($m)->id_pendaftaran)->first();
        // A: .4*90+.3*80+.3*70 = 81 ; C: 85 ; D: 70 ; B: 60  -> C, A, D, B
        $this->assertSame(1, $rank($mC)->peringkat);
        $this->assertEquals(85.00, $rank($mC)->nilai_total);
        $this->assertSame(2, $rank($mA)->peringkat);
        $this->assertEquals(81.00, $rank($mA)->nilai_total);
        $this->assertSame(3, $rank($mD)->peringkat);
        $this->assertSame(4, $rank($mB)->peringkat);
        $this->assertSame('dinilai', $pd($mA)->status);

        // ---------- P-09 penetapan ----------
        // skema masih dibuka -> ditolak
        $this->actingAs($staf)->post("/seleksi/penetapan/{$s->id_skema}", ['no_sk' => 'SK-1', 'tgl_penetapan' => now()->toDateString()])
            ->assertSessionHas('error');
        $this->assertSame(0, Penerima::count());

        $this->actingAs($staf)->post("/skema/{$s->id_skema}/tutup");
        $this->actingAs($admin)->post("/seleksi/penetapan/{$s->id_skema}", ['no_sk' => 'SK/WR3/001', 'tgl_penetapan' => now()->toDateString()])
            ->assertSessionHas('success', 'Hasil seleksi berhasil ditetapkan.');

        $this->assertSame(2, Penerima::count());                                   // = kuota
        $this->assertSame('diterima', $pd($mC)->status);
        $this->assertSame('diterima', $pd($mA)->status);
        $this->assertSame('cadangan', $pd($mD)->status);                           // ceil(20% x 2) = 1 cadangan
        $this->assertSame('ditolak', $pd($mB)->status);
        $this->assertTrue(Notifikasi::where('id_pengguna', $mC->id_pengguna)->where('judul', 'like', 'Selamat%')->exists());
        $this->assertTrue(Notifikasi::where('id_pengguna', $mB->id_pengguna)->where('judul', 'Hasil seleksi beasiswa')->exists());

        // penetapan ulang ditolak
        $this->actingAs($staf)->post("/seleksi/penetapan/{$s->id_skema}", ['no_sk' => 'SK-2', 'tgl_penetapan' => now()->toDateString()])->assertSessionHas('error');
        $this->assertSame(2, Penerima::count());

        // mahasiswa melihat status (ALGO-10)
        $this->actingAs($mC->pengguna)->get('/pendaftaran/status')->assertOk()->assertSee('SK/WR3/001');

        // ---------- P-11 penyaluran ----------
        // hanya mahasiswa C yang punya rekening
        $this->actingAs($mC->pengguna)->put('/profil', ['nama_bank' => 'Bank Uji', 'no_rekening' => '9876543210'])->assertSessionHas('success');
        $this->assertSame('9876543210', $mC->fresh()->no_rekening);
        $this->assertNotSame('9876543210', \DB::table('t_mahasiswa')->where('npm', $mC->npm)->value('no_rekening'));   // terenkripsi di DB

        $this->actingAs($staf)->post('/penyaluran/salurkan', ['id_skema' => $s->id_skema, 'termin_ke' => 1])
            ->assertSessionHas('dilewati', fn ($l) => count($l) === 1 && str_contains($l[0], 'Data rekening penerima belum lengkap.'));
        $this->assertSame(1, Penyaluran::count());
        $y = Penyaluran::first();
        $this->assertSame('tersalurkan', $y->status);
        $this->assertNotEmpty($y->no_ref_keuangan);
        $this->assertSame(1000000.0, $y->nominal);
        $this->assertMatchesRegularExpression('/^PY\.\d{2}\.00001$/', $y->id_penyaluran);

        // salurkan ulang termin yang sama tidak menggandakan
        $this->actingAs($staf)->post('/penyaluran/salurkan', ['id_skema' => $s->id_skema, 'termin_ke' => 1]);
        $this->assertSame(1, Penyaluran::count());
        // termin melebihi aturan
        $this->actingAs($staf)->post('/penyaluran/salurkan', ['id_skema' => $s->id_skema, 'termin_ke' => 3])->assertSessionHas('error');

        // ---------- P-12 konfirmasi ----------
        $this->actingAs($mA->pengguna)->post("/penyaluran/{$y->id_penyaluran}/konfirmasi")->assertForbidden();      // bukan miliknya
        $this->actingAs($mC->pengguna)->post("/penyaluran/{$y->id_penyaluran}/konfirmasi")->assertSessionHas('success', 'Terima kasih, konfirmasi Anda tersimpan.');
        $this->assertSame('dikonfirmasi', $y->fresh()->status);
        $this->assertNotNull($y->fresh()->tgl_konfirmasi);
        $this->actingAs($mC->pengguna)->post("/penyaluran/{$y->id_penyaluran}/konfirmasi")->assertSessionHas('error', 'Dana belum berstatus tersalurkan.');

        // ---------- P-15 monitoring ----------
        $pn = Penerima::where('npm', $mC->npm)->first();
        $this->actingAs($staf)->post('/monitoring', ['semester' => '2026/1', 'data' => [Kode::kunci($pn->id_penerima) => ['ips' => 3.0, 'ipk' => 3.2]]])
            ->assertSessionHas('success');
        $this->assertSame('peringatan', StatusAkademik::where('id_penerima', $pn->id_penerima)->first()->status);
        $this->assertTrue(Notifikasi::where('id_pengguna', $staf->id_pengguna)->where('judul', 'Peringatan status akademik penerima')->exists());

        $this->actingAs($staf)->post('/monitoring', ['semester' => '2026/2', 'data' => [Kode::kunci($pn->id_penerima) => ['ips' => 2.9, 'ipk' => 3.1]]]);
        $this->assertSame('tidak_memenuhi', StatusAkademik::where('id_penerima', $pn->id_penerima)->where('semester', '2026/2')->first()->status);

        // penerima tidak memenuhi dilewati pada termin 2
        $this->actingAs($staf)->post('/penyaluran/salurkan', ['id_skema' => $s->id_skema, 'termin_ke' => 2])
            ->assertSessionHas('dilewati', fn ($l) => collect($l)->contains(fn ($x) => str_contains($x, 'Status akademik tidak memenuhi')));
        $this->assertSame(1, Penyaluran::count());

        // ---------- P-13 laporan ----------
        $awal = now()->startOfMonth()->toDateString();
        $akhir = now()->endOfMonth()->toDateString();
        $this->actingAs($staf)->post('/laporan', ['jenis' => 'realisasi', 'periode_awal' => $akhir, 'periode_akhir' => $awal])
            ->assertSessionHasErrors(['periode_akhir' => 'Periode akhir harus setelah periode awal.']);
        $this->actingAs($staf)->post('/laporan', ['jenis' => 'realisasi', 'periode_awal' => '2020-01-01', 'periode_akhir' => '2020-12-31'])
            ->assertSessionHas('error', 'Tidak ada data pada periode terpilih.');

        $r = $this->actingAs($staf)->post('/laporan', ['jenis' => 'realisasi', 'periode_awal' => $awal, 'periode_akhir' => $akhir]);
        $r->assertOk()->assertSee('Skema Uji')->assertSee('Rp 1.000.000');
        $this->assertSame(1, Laporan::where('jenis_laporan', 'realisasi')->count());

        // mitra hanya melihat skema yang didanainya
        $skemaLain = $this->skema(['nama_skema' => 'Skema Milik Mitra Lain']);
        $this->actingAs($akunMitra)->post('/laporan', ['periode_awal' => $awal, 'periode_akhir' => $akhir])
            ->assertOk()->assertSee('Skema Uji')->assertDontSee('Skema Milik Mitra Lain');
        $lap = Laporan::where('jenis_laporan', 'mitra')->first();
        $this->assertSame($mitra->id_mitra, $lap->id_mitra);
        $this->actingAs($akunMitra)->get("/laporan/{$lap->id_laporan}/unduh")->assertOk();
        $lapStaf = Laporan::where('jenis_laporan', 'realisasi')->first();
        $this->actingAs($akunMitra)->get("/laporan/{$lapStaf->id_laporan}/unduh")->assertForbidden();

        // ---------- P-14 ekspor ----------
        foreach (['CSV' => 'text/csv', 'XLSX' => 'spreadsheetml', 'PDF' => 'application/pdf'] as $fmt => $mime) {
            foreach (['realisasi', 'komprehensif'] as $jenis) {
                $res = $this->actingAs($staf)->post('/ekspor', ['jenis' => $jenis, 'format' => $fmt, 'periode_awal' => $awal, 'periode_akhir' => $akhir]);
                $res->assertOk();
                $this->assertStringContainsString($mime, $res->headers->get('Content-Type'));
                $isi = $res->getContent();
                match ($fmt) {
                    'PDF' => $this->assertStringStartsWith('%PDF-1.4', $isi),
                    'XLSX' => $this->assertStringStartsWith('PK', $isi),
                    default => $this->assertStringContainsString('Skema', $isi),
                };
            }
        }
        $this->actingAs($staf)->post('/ekspor', ['jenis' => 'akademik', 'format' => 'CSV', 'periode_awal' => $awal, 'periode_akhir' => $akhir])->assertOk();
        $this->actingAs($staf)->post('/ekspor', ['jenis' => 'mitra', 'format' => 'CSV', 'periode_awal' => $awal, 'periode_akhir' => $akhir])
            ->assertSessionHasErrors('id_mitra');

        // ---------- P-16 dashboard ----------
        $this->actingAs($admin)->get('/admin/dashboard')->assertOk()->assertSee('Rp 1.000.000');
        $this->actingAs($admin)->get('/admin/dashboard?tahun=2019')->assertOk()->assertSee('Belum ada data untuk tahun terpilih.');
    }
}
