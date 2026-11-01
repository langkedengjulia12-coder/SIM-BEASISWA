<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('scholarship_scheme_id')->constrained('scholarship_schemes')->cascadeOnDelete();
            $table->string('registration_number')->unique();
            $table->string('study_program');
            $table->decimal('gpa', 3, 2);
            $table->string('status')->default('pending');
            $table->timestamp('submitted_at');
            $table->timestamps();

            $table->index(['status', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
