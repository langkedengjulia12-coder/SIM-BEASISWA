<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_log_aktivitas', function (Blueprint $t) {
            $t->id('id_log');
            $t->char('id_pengguna', 11)->nullable();
            $t->string('aksi', 30);                               // login / tambah / ubah / hapus / ekspor ...
            $t->string('modul', 30);
            $t->text('detail')->nullable();
            $t->string('ip_address', 45);
            $t->dateTime('waktu');

            $t->index('waktu');
            $t->foreign('id_pengguna')->references('id_pengguna')->on('t_pengguna')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_log_aktivitas');
    }
};
