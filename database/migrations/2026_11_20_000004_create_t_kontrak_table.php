<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_kontrak', function (Blueprint $t) {
            $t->char('id_kontrak', 10)->primary();                // KT.yy.nnnn
            $t->char('id_mitra', 10);
            $t->string('no_dokumen', 50)->unique();
            $t->string('judul', 150);
            $t->string('jenis_dokumen', 5)->default('MoU');       // MoU / MoA
            $t->date('tgl_mulai');
            $t->date('tgl_berakhir');
            $t->decimal('nilai_komitmen', 15, 2)->default(0);
            $t->string('status', 12)->default('draf');            // draf / aktif / berakhir / diperpanjang
            $t->string('file_dokumen', 255)->nullable();

            $t->foreign('id_mitra')->references('id_mitra')->on('t_mitra');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_kontrak');
    }
};
