<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_pendaftaran', function (Blueprint $t) {
            $t->char('id_pendaftaran', 12)->primary();            // PD.yy.nnnnn
            $t->char('npm', 11);
            $t->char('id_skema', 9);
            $t->dateTime('tgl_daftar');
            $t->decimal('ipk_saat_daftar', 3, 2);
            $t->text('motivasi')->nullable();
            // diajukan / tidak_lengkap / lolos_administrasi / dinilai / diterima / cadangan / ditolak
            $t->string('status', 20)->default('diajukan');
            $t->text('catatan_verifikasi')->nullable();

            $t->unique(['npm', 'id_skema']);                      // satu mahasiswa satu pendaftaran per skema
            $t->foreign('npm')->references('npm')->on('t_mahasiswa');
            $t->foreign('id_skema')->references('id_skema')->on('t_skema_beasiswa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_pendaftaran');
    }
};
