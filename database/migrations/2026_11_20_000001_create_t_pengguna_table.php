<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_pengguna', function (Blueprint $t) {
            $t->char('id_pengguna', 11)->primary();               // US.yy.nnnnn (11 karakter; SKPL menulis CHAR(10) namun format kodenya 11 karakter)
            $t->string('username', 50)->unique();
            $t->string('password', 255);                          // bcrypt
            $t->string('nama', 100);
            $t->string('email', 100)->unique();
            $t->string('role', 20)->default('mahasiswa');         // admin_wr3 / staf_beasiswa / mahasiswa / mitra
            $t->boolean('status_aktif')->default(true);
            $t->dateTime('last_login')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_pengguna');
    }
};
