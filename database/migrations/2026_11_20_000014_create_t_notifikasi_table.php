<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_notifikasi', function (Blueprint $t) {
            $t->id('id_notifikasi');
            $t->char('id_pengguna', 11);
            $t->string('jenis', 15);                              // masa_berlaku / seleksi / penyaluran / sistem
            $t->string('judul', 100);
            $t->text('isi');
            $t->boolean('dibaca')->default(false);
            $t->dateTime('tgl_kirim');

            $t->index(['id_pengguna', 'dibaca']);
            $t->foreign('id_pengguna')->references('id_pengguna')->on('t_pengguna');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_notifikasi');
    }
};
