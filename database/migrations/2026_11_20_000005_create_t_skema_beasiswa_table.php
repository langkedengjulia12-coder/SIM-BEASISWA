<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_skema_beasiswa', function (Blueprint $t) {
            $t->char('id_skema', 9)->primary();                   // SB.yy.nnn
            $t->string('nama_skema', 100);
            $t->string('jenis', 15);                              // prestasi / bantuan_biaya / riset
            $t->unsignedSmallInteger('kuota');
            $t->decimal('nominal_per_penerima', 15, 2);
            $t->decimal('ipk_minimum', 3, 2)->default(0);
            $t->text('kriteria_seleksi');                         // JSON bobot kriteria
            $t->date('tgl_buka');
            $t->date('tgl_tutup');
            $t->string('aturan_penyaluran', 15)->default('per_semester'); // sekaligus / per_semester / bulanan
            $t->string('status', 10)->default('draf');            // draf / dibuka / ditutup / selesai
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_skema_beasiswa');
    }
};
