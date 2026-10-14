<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('utilitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kamar_id')->constrained('kamar')->onDelete('restrict');
            $table->foreignId('penyewa_id')->nullable()->constrained('penyewa')->onDelete('set null');
            $table->enum('jenis', ['listrik', 'air'])->default('listrik');
            $table->decimal('meter_awal', 10, 3);
            $table->decimal('meter_akhir', 10, 3);
            $table->decimal('penggunaan', 10, 3);
            $table->decimal('harga_satuan', 12, 2);
            $table->decimal('total_biaya', 12, 2);
            $table->integer('periode_bulan');
            $table->integer('periode_tahun');
            $table->enum('status', ['pending', 'diproses', 'selesai'])->default('pending');
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('utilitas');
    }
};
