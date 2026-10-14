<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penyewa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kamar_id')->constrained('kamar')->onDelete('restrict');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('nama_lengkap');
            $table->char('jenis_kelamin', 1); // L or P
            $table->string('no_hp');
            $table->string('email')->nullable();
            $table->string('nik')->unique(); // NIK/KTP
            $table->string('alamat_asal')->nullable();
            $table->string('pekerjaan')->nullable();
            $table->string('nama_penjamin')->nullable();
            $table->string('no_hp_penjamin')->nullable();
            $table->date('tanggal_masuk');
            $table->date('kontrak_mulai');
            $table->date('kontrak_selesai');
            $table->integer('durasi_kontrak_bulan')->nullable();
            $table->decimal('harga_sewa_kontrak', 12, 2)->nullable();
            $table->decimal('deposit', 12, 2)->default(0);
            $table->enum('status', ['aktif', 'keluar', 'pending'])->default('pending');
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penyewa');
    }
};
