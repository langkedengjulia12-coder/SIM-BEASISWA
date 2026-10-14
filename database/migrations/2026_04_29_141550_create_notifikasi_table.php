<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('penyewa_id')->nullable()->constrained('penyewa')->onDelete('set null');
            $table->string('judul');
            $table->text('pesan');
            $table->enum('tipe', ['pembayaran', 'kontrak', 'maintenance', 'sistem', 'lainnya'])->default('sistem');
            $table->enum('prioritas', ['tinggi', 'sedang', 'rendah'])->default('sedang');
            $table->boolean('is_read')->default(false);
            $table->timestamp('dibaca_pada')->nullable();
            $table->json('data_tambahan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi');
    }
};
