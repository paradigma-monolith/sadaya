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
        Schema::create('sinkarkes_daily_reports', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('wilker', 100);
            $table->string('transport_type', 20); // 'kapal' | 'pesawat'
            $table->json('metrics')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('sinkarkes_synced_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tanggal', 'wilker', 'transport_type'], 'sinkarkes_daily_unique_entry');
            $table->index(['wilker', 'tanggal'], 'sinkarkes_daily_wilker_date_idx');
            $table->index(['transport_type', 'tanggal'], 'sinkarkes_daily_type_date_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sinkarkes_daily_reports');
    }
};

