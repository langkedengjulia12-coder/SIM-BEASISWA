<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_dokumen_pendaftaran', function (Blueprint $t) {
            $t->char('id_dokumen', 12)->primary();                // DK.yy.nnnnn
            $t->char('id_pendaftaran', 12);
            $t->string('jenis_dokumen', 25);                      // transkrip / ktm / surat_keterangan / slip_penghasilan / proposal / sertifikat
            $t->string('nama_file', 150);
            $t->string('path_file', 255);                         // berkas terenkripsi
            $t->integer('ukuran_kb');                             // maks. 5120 KB
            $t->string('status_verifikasi', 12)->default('menunggu'); // menunggu / valid / tidak_valid
            $t->dateTime('tgl_unggah');

            $t->foreign('id_pendaftaran')->references('id_pendaftaran')->on('t_pendaftaran')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_dokumen_pendaftaran');
    }
};
