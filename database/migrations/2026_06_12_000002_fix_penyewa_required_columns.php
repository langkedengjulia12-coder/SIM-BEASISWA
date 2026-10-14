<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penyewa', function (Blueprint $table) {
            if (!Schema::hasColumn('penyewa', 'jenis_kelamin')) {
                $table->char('jenis_kelamin', 1)->nullable()->after('nama_lengkap');
            }
            if (!Schema::hasColumn('penyewa', 'kontrak_mulai')) {
                $table->date('kontrak_mulai')->nullable()->after('tanggal_masuk');
            }
            if (!Schema::hasColumn('penyewa', 'kontrak_selesai')) {
                $table->date('kontrak_selesai')->nullable()->after('kontrak_mulai');
            }
            if (!Schema::hasColumn('penyewa', 'durasi_kontrak_bulan')) {
                $table->integer('durasi_kontrak_bulan')->nullable()->after('kontrak_selesai');
            }
        });
    }

    public function down(): void
    {
        Schema::table('penyewa', function (Blueprint $table) {
            $table->dropColumn(['jenis_kelamin', 'kontrak_mulai', 'kontrak_selesai', 'durasi_kontrak_bulan']);
        });
    }
};
