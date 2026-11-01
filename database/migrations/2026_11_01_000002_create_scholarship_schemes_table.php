<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scholarship_schemes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->nullable()->constrained('partners')->nullOnDelete();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('category');
            $table->unsignedInteger('quota');
            $table->decimal('funding_per_student', 15, 2);
            $table->string('academic_year');
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index(['category', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scholarship_schemes');
    }
};
