<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table): void {
            $table->string('mutation_file')->nullable()->after('submitted_at');
            $table->string('confirmation_status')->default('pending')->after('mutation_file');
            $table->timestamp('confirmed_at')->nullable()->after('confirmation_status');
        });

        Schema::table('scholarship_schemes', function (Blueprint $table): void {
            $table->decimal('minimum_gpa', 3, 2)->nullable()->after('funding_per_student');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE applications MODIFY status VARCHAR(50) NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table): void {
            $table->dropColumn(['mutation_file', 'confirmation_status', 'confirmed_at']);
        });

        Schema::table('scholarship_schemes', function (Blueprint $table): void {
            $table->dropColumn('minimum_gpa');
        });
    }
};