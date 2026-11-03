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
        Schema::create('sinkarkes_reports', function (Blueprint $table) {
            $table->id();
            $table->string('wilker', 100)->index();
            $table->unsignedSmallInteger('year')->index();
            $table->unsignedTinyInteger('month')->index(); // 1 - 12
            $table->json('data')->nullable(); // Keyed by excel_row or item code
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('draft'); // draft, submitted, verified
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['wilker', 'year', 'month'], 'sinkarkes_unique_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sinkarkes_reports');
    }
};

