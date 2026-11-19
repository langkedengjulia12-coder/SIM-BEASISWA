<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_penerima', function (Blueprint $t) {
            $t->char('id_penerima', 12)->primary();               // PN.yy.nnnnn
            $t->char('id_pendaftaran', 12)->unique();
            $t->char('id_skema', 9);
            $t->char('npm', 11);
            $t->string('no_sk', 50);
            $t->date('tgl_penetapan');
            $t->string('status_penerima', 10)->default('aktif');  // aktif / dihentikan / selesai

            $t->foreign('id_pendaftaran')->references('id_pendaftaran')->on('t_pendaftaran');
            $t->foreign('id_skema')->references('id_skema')->on('t_skema_beasiswa');
            $t->foreign('npm')->references('npm')->on('t_mahasiswa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_penerima');
    }
};
