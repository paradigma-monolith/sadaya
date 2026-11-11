<?php

use App\Models\ShipHealthSurveillance;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quarantine_countries', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique();
            $table->boolean('is_red_zone')->default(false)->index();
            $table->string('source_label', 255)->nullable();
            $table->timestamp('last_imported_at')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('quarantine_country_import_logs', function (Blueprint $table) {
            $table->id();
            $table->string('filename', 255);
            $table->string('mode', 32)->default('replace');
            $table->unsignedInteger('total_extracted')->default(0);
            $table->unsignedInteger('matched_count')->default(0);
            $table->unsignedInteger('newly_flagged_count')->default(0);
            $table->unsignedInteger('unflagged_count')->default(0);
            $table->unsignedInteger('added_countries_count')->default(0);
            $table->json('details')->nullable();
            $table->foreignId('imported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Seed default 195 countries from baseline reference
        $now = now();
        $rows = [];
        foreach (ShipHealthSurveillance::QUARANTINE_COUNTRIES as $name => $isRed) {
            $rows[] = [
                'name' => $name,
                'is_red_zone' => (bool) $isRed,
                'source_label' => null,
                'last_imported_at' => null,
                'updated_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('quarantine_countries')->insert($chunk);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quarantine_country_import_logs');
        Schema::dropIfExists('quarantine_countries');
    }
};

