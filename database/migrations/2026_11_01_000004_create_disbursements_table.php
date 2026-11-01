<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disbursements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->date('disbursement_date');
            $table->string('bank_name');
            $table->string('account_number');
            $table->string('reference_number')->nullable();
            $table->enum('status', ['success', 'pending', 'failed'])->default('pending');
            $table->timestamps();

            $table->index(['status', 'disbursement_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disbursements');
    }
};
