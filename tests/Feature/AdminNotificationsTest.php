<?php

namespace Tests\Feature;

use App\Models\Kontrak;
use App\Models\Notifikasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\Support\Data;
use Tests\TestCase;

/** UC-18 Kirim Notifikasi dan penjadwal harian (ALGO-03, ALGO-05b). */
class AdminNotificationsTest extends TestCase
{
    use Data, RefreshDatabase;

    public function test_penjadwal_mengirim_notifikasi_kontrak_hampir_berakhir_tanpa_duplikat(): void
    {
        $staf = $this->staf();
        $mitra = $this->mitraTerverifikasi();
        $akun = $this->akun('mitra', 'mitraku');
        $mitra->update(['id_pengguna' => $akun->id_pengguna]);
        $this->kontrakAktif($mitra, 20);                 // berakhir 20 hari lagi
        $this->kontrakAktif($this->mitraTerverifikasi('PT Lama'), 200);   // masih lama

        Artisan::call('beasiswa:cek-kontrak');

        $n = Notifikasi::where('id_pengguna', $staf->id_pengguna)->get();
        $this->assertCount(1, $n);
        $this->assertSame('masa_berlaku', $n[0]->jenis);
        $this->assertStringContainsString('Kontrak akan berakhir dalam 30 hari', $n[0]->isi);
        $this->assertSame(1, Notifikasi::where('id_pengguna', $akun->id_pengguna)->count());   // PIC mitra juga diberi tahu

        Artisan::call('beasiswa:cek-kontrak');           // dijalankan lagi hari yang sama
        $this->assertSame(1, Notifikasi::where('id_pengguna', $staf->id_pengguna)->count());
    }

    public function test_kontrak_lewat_tanggal_ditandai_berakhir(): void
    {
        $k = $this->kontrakAktif($this->mitraTerverifikasi(), -3);
        Artisan::call('beasiswa:cek-kontrak');
        $this->assertSame('berakhir', $k->fresh()->status);
    }

    public function test_skema_dibuka_dan_ditutup_otomatis_sesuai_tanggal(): void
    {
        $k = $this->kontrakAktif($this->mitraTerverifikasi());
        $siap = $this->skema(['status' => 'draf', 'tgl_buka' => now()->toDateString()]);
        $this->komitmen($k, $siap, 10000000);
        $tanpaDana = $this->skema(['nama_skema' => 'Tanpa Dana', 'status' => 'draf', 'tgl_buka' => now()->toDateString()]);
        $lewat = $this->skema(['nama_skema' => 'Lewat', 'status' => 'dibuka', 'tgl_buka' => now()->subDays(20)->toDateString(), 'tgl_tutup' => now()->subDay()->toDateString()]);

        Artisan::call('beasiswa:perbarui-skema');

        $this->assertSame('dibuka', $siap->fresh()->status);
        $this->assertSame('draf', $tanpaDana->fresh()->status);   // dana belum cukup
        $this->assertSame('ditutup', $lewat->fresh()->status);
    }

    public function test_kotak_notifikasi_dan_tandai_dibaca(): void
    {
        $m = $this->mahasiswa('mhs1', '20241320001');
        $n = Notifikasi::create(['id_pengguna' => $m->id_pengguna, 'jenis' => 'sistem', 'judul' => 'Halo', 'isi' => 'Isi pesan', 'dibaca' => false, 'tgl_kirim' => now()]);

        $this->actingAs($m->pengguna)->get('/notifikasi')->assertOk()->assertSee('Halo');
        $this->actingAs($m->pengguna)->post("/notifikasi/{$n->id_notifikasi}/baca");
        $this->assertTrue($n->fresh()->dibaca);

        // tidak dapat menandai notifikasi milik orang lain
        $lain = $this->mahasiswa('mhs2', '20241320002');
        $n2 = Notifikasi::create(['id_pengguna' => $m->id_pengguna, 'jenis' => 'sistem', 'judul' => 'Rahasia', 'isi' => 'x', 'dibaca' => false, 'tgl_kirim' => now()]);
        $this->actingAs($lain->pengguna)->post("/notifikasi/{$n2->id_notifikasi}/baca");
        $this->assertFalse($n2->fresh()->dibaca);
    }
}
