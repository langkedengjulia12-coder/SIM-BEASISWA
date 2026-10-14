<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();
            $table->string('kode_transaksi')->unique();
            $table->foreignId('penyewa_id')->constrained('penyewa')->onDelete('restrict');
            $table->foreignId('kamar_id')->constrained('kamar')->onDelete('restrict');
            $table->enum('jenis', ['sewa', 'utilitas', 'deposit', 'denda', 'perawatan', 'lainnya']);
            $table->string('keterangan')->nullable();
            $table->decimal('jumlah', 12, 2);
            $table->date('tanggal_tagihan');
            $table->date('jatuh_tempo');
            $table->date('tanggal_bayar')->nullable();
            $table->string('metode_bayar')->nullable(); // transfer, tunai, kartu_kredit
            $table->integer('periode_bulan')->nullable();
            $table->integer('periode_tahun')->nullable();
            $table->enum('status', ['belum_bayar', 'lunas', 'terlambat', 'sebagian'])->default('belum_bayar');
            $table->foreignId('dibuat_oleh')->constrained('users')->onDelete('restrict');
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};
