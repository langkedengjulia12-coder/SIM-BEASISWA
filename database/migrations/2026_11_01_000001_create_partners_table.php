<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->string('mou_number')->unique();
            $table->date('mou_start');
            $table->date('mou_end');
            $table->decimal('funding_amount', 15, 2);
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};
