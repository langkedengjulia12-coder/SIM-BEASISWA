<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_penyaluran', function (Blueprint $t) {
            $t->char('id_penyaluran', 12)->primary();             // PY.yy.nnnnn
            $t->char('id_penerima', 12);
            $t->unsignedTinyInteger('termin_ke')->default(1);
            $t->decimal('nominal', 15, 2);
            $t->date('tgl_penyaluran')->nullable();
            $t->string('no_ref_keuangan', 50)->nullable();
            // menunggu / diproses / tersalurkan / gagal / dikonfirmasi
            $t->string('status', 12)->default('menunggu');
            $t->date('tgl_konfirmasi')->nullable();
            $t->char('id_petugas', 11);

            $t->unique(['id_penerima', 'termin_ke']);
            $t->foreign('id_penerima')->references('id_penerima')->on('t_penerima');
            $t->foreign('id_petugas')->references('id_pengguna')->on('t_pengguna');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_penyaluran');
    }
};
