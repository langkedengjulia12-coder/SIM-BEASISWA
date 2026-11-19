<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_mitra', function (Blueprint $t) {
            $t->char('id_mitra', 10)->primary();                  // MT.yy.nnnn
            $t->char('id_pengguna', 11)->nullable();
            $t->string('nama_mitra', 120);
            $t->string('jenis_mitra', 20);                        // industri / lembaga_donor / pemerintah / perorangan
            $t->text('alamat')->nullable();
            $t->string('nama_pic', 100);
            $t->string('email_pic', 100);
            $t->string('telepon_pic', 20)->nullable();
            $t->string('status_verifikasi', 15)->default('menunggu'); // menunggu / terverifikasi / ditolak
            $t->date('tgl_daftar');

            $t->foreign('id_pengguna')->references('id_pengguna')->on('t_pengguna');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_mitra');
    }
};
