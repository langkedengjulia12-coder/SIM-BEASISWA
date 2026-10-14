<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE notifikasi MODIFY COLUMN tipe ENUM('pembayaran','kontrak','maintenance','sistem','lainnya','registrasi','jatuh_tempo') DEFAULT 'sistem'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE notifikasi MODIFY COLUMN tipe ENUM('pembayaran','kontrak','maintenance','sistem','lainnya') DEFAULT 'sistem'");
    }
};
