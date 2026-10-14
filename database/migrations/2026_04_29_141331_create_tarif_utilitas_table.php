<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tarif_utilitas', function (Blueprint $table) {
            $table->id();
            $table->string('jenis'); // listrik, air
            $table->decimal('harga_per_unit', 12, 2);
            $table->string('satuan'); // kWh, m3
            $table->date('berlaku_mulai');
            $table->date('berlaku_selesai')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarif_utilitas');
    }
};
