<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_penilaian', function (Blueprint $t) {
            $t->char('id_penilaian', 12)->primary();              // NL.yy.nnnnn
            $t->char('id_pendaftaran', 12)->unique();
            $t->decimal('nilai_akademik', 5, 2)->default(0);
            $t->decimal('nilai_nonakademik', 5, 2)->default(0);
            $t->decimal('nilai_kebutuhan', 5, 2)->default(0);
            $t->decimal('nilai_total', 5, 2)->default(0);
            $t->smallInteger('peringkat')->nullable();
            $t->char('id_penilai', 11);
            $t->dateTime('tgl_penilaian');

            $t->foreign('id_pendaftaran')->references('id_pendaftaran')->on('t_pendaftaran')->cascadeOnDelete();
            $t->foreign('id_penilai')->references('id_pengguna')->on('t_pengguna');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_penilaian');
    }
};
