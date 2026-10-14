<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('penyewa', 'kamar_id')) {
            DB::statement('ALTER TABLE penyewa DROP FOREIGN KEY penyewa_kamar_id_foreign');
            DB::statement('ALTER TABLE penyewa MODIFY kamar_id BIGINT UNSIGNED NULL');
            DB::statement('ALTER TABLE penyewa ADD CONSTRAINT penyewa_kamar_id_foreign FOREIGN KEY (kamar_id) REFERENCES kamar(id) ON DELETE RESTRICT');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('penyewa', 'kamar_id')) {
            DB::statement('ALTER TABLE penyewa DROP FOREIGN KEY penyewa_kamar_id_foreign');
            DB::statement('ALTER TABLE penyewa MODIFY kamar_id BIGINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE penyewa ADD CONSTRAINT penyewa_kamar_id_foreign FOREIGN KEY (kamar_id) REFERENCES kamar(id) ON DELETE RESTRICT');
        }
    }
};
