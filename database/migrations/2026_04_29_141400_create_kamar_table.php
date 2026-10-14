<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kamar', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_kamar')->unique();
            $table->enum('tipe', ['standar', 'premium', 'deluxe'])->default('standar');
            $table->integer('lantai');
            $table->decimal('harga_sewa', 12, 2);
            $table->integer('luas_m2')->nullable();
            $table->json('fasilitas')->nullable();
            $table->text('deskripsi')->nullable();
            $table->enum('status', ['tersedia', 'terisi', 'maintenance'])->default('tersedia');
            $table->integer('kapasitas_penghuni')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kamar');
    }
};
