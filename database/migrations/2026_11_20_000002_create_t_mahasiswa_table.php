<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_mahasiswa', function (Blueprint $t) {
            $t->char('npm', 11)->primary();
            $t->char('id_pengguna', 11)->unique();
            $t->string('nama_lengkap', 100);
            $t->string('program_studi', 60);
            $t->string('fakultas', 60);
            $t->smallInteger('angkatan');
            $t->decimal('ipk', 3, 2)->default(0);
            $t->string('status_akademik', 10)->default('aktif');  // aktif / cuti / lulus / nonaktif
            $t->string('no_rekening', 255)->nullable();           // terenkripsi AES-256
            $t->string('nama_bank', 40)->nullable();

            $t->foreign('id_pengguna')->references('id_pengguna')->on('t_pengguna');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_mahasiswa');
    }
};
