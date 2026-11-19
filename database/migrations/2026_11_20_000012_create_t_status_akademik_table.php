<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_status_akademik', function (Blueprint $t) {
            $t->char('id_status', 12)->primary();                 // SA.yy.nnnnn
            $t->char('id_penerima', 12);
            $t->string('semester', 10);                           // contoh: 2026/1
            $t->decimal('ips', 3, 2);
            $t->decimal('ipk', 3, 2);
            $t->string('status', 15);                             // memenuhi / peringatan / tidak_memenuhi
            $t->date('tgl_pembaruan');

            $t->unique(['id_penerima', 'semester']);
            $t->foreign('id_penerima')->references('id_penerima')->on('t_penerima');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_status_akademik');
    }
};
