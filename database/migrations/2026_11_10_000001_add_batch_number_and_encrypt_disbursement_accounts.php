<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('disbursements', function (Blueprint $table): void {
            $table->string('batch_number')->nullable()->index()->after('account_number');
            $table->text('account_number')->change();
        });

        DB::table('disbursements')
            ->select(['id', 'account_number'])
            ->orderBy('id')
            ->chunkById(100, static function ($disbursements): void {
                foreach ($disbursements as $disbursement) {
                    DB::table('disbursements')
                        ->where('id', $disbursement->id)
                        ->update(['account_number' => Crypt::encryptString($disbursement->account_number)]);
                }
            });
    }

    public function down(): void
    {
        DB::table('disbursements')
            ->select(['id', 'account_number'])
            ->orderBy('id')
            ->chunkById(100, static function ($disbursements): void {
                foreach ($disbursements as $disbursement) {
                    DB::table('disbursements')
                        ->where('id', $disbursement->id)
                        ->update(['account_number' => Crypt::decryptString($disbursement->account_number)]);
                }
            });

        Schema::table('disbursements', function (Blueprint $table): void {
            $table->dropIndex(['batch_number']);
            $table->dropColumn('batch_number');
            $table->string('account_number', 255)->change();
        });
    }
};
