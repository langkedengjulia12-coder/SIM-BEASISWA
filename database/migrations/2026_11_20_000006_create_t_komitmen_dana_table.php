<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_komitmen_dana', function (Blueprint $t) {
            $t->char('id_komitmen', 10)->primary();               // KD.yy.nnnn
            $t->char('id_kontrak', 10);
            $t->char('id_skema', 9)->nullable();
            $t->date('tgl_komitmen');
            $t->decimal('nominal_komitmen', 15, 2);
            $t->decimal('nominal_diterima', 15, 2)->default(0);
            $t->date('tgl_terima_terakhir')->nullable();
            $t->string('keterangan', 200)->nullable();

            $t->foreign('id_kontrak')->references('id_kontrak')->on('t_kontrak');
            $t->foreign('id_skema')->references('id_skema')->on('t_skema_beasiswa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_komitmen_dana');
    }
};
