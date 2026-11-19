<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_laporan', function (Blueprint $t) {
            $t->char('id_laporan', 12)->primary();                // LP.yy.nnnnn
            $t->string('jenis_laporan', 15);                      // realisasi / mitra / akademik / komprehensif
            $t->char('id_mitra', 10)->nullable();
            $t->date('periode_awal');
            $t->date('periode_akhir');
            $t->string('format_file', 5)->default('PDF');         // PDF / XLSX / CSV
            $t->string('path_file', 255);
            $t->char('id_pembuat', 11);
            $t->dateTime('tgl_generate');

            $t->foreign('id_mitra')->references('id_mitra')->on('t_mitra');
            $t->foreign('id_pembuat')->references('id_pengguna')->on('t_pengguna');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_laporan');
    }
};
