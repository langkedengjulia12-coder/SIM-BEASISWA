<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kamar', function (Blueprint $table) {
            if (!Schema::hasColumn('kamar', 'nama_kamar')) {
                $table->string('nama_kamar')->nullable()->after('nomor_kamar');
            }
        });

        Schema::table('utilitas', function (Blueprint $table) {
            if (!Schema::hasColumn('utilitas', 'tarif_id')) {
                $table->foreignId('tarif_id')->nullable()->after('penyewa_id')->constrained('tarif_utilitas')->nullOnDelete();
            }
            if (!Schema::hasColumn('utilitas', 'meteran_awal')) {
                $table->decimal('meteran_awal', 10, 2)->nullable()->after('jenis');
            }
            if (!Schema::hasColumn('utilitas', 'meteran_akhir')) {
                $table->decimal('meteran_akhir', 10, 2)->nullable()->after('meteran_awal');
            }
            if (!Schema::hasColumn('utilitas', 'harga_per_unit')) {
                $table->decimal('harga_per_unit', 12, 2)->nullable()->after('meteran_akhir');
            }
            if (!Schema::hasColumn('utilitas', 'tanggal_catat')) {
                $table->date('tanggal_catat')->nullable()->after('harga_per_unit');
            }
            if (!Schema::hasColumn('utilitas', 'status_bayar')) {
                $table->string('status_bayar')->default('belum')->after('tanggal_catat');
            }
            if (!Schema::hasColumn('utilitas', 'transaksi_id')) {
                $table->foreignId('transaksi_id')->nullable()->after('status_bayar')->constrained('transaksi')->nullOnDelete();
            }
            if (!Schema::hasColumn('utilitas', 'total_tagihan')) {
                $table->decimal('total_tagihan', 12, 2)->nullable()->after('transaksi_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('utilitas', function (Blueprint $table) {
            $table->dropColumn(['tarif_id', 'meteran_awal', 'meteran_akhir', 'harga_per_unit', 'tanggal_catat', 'status_bayar', 'transaksi_id', 'total_tagihan']);
        });

        Schema::table('kamar', function (Blueprint $table) {
            $table->dropColumn(['nama_kamar']);
        });
    }
};
