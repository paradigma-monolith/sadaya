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
        Schema::table('aircraft_sanitation_inspections', function (Blueprint $table) {
            if (!Schema::hasColumn('aircraft_sanitation_inspections', 'latitude')) {
                $table->decimal('latitude', 11, 8)->nullable()->after('petugas_bkk_ttd');
            }
            if (!Schema::hasColumn('aircraft_sanitation_inspections', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            }
            if (!Schema::hasColumn('aircraft_sanitation_inspections', 'lokasi_geotag')) {
                $table->string('lokasi_geotag')->nullable()->after('longitude');
            }
            if (!Schema::hasColumn('aircraft_sanitation_inspections', 'foto_dokumentasi')) {
                $table->json('foto_dokumentasi')->nullable()->after('lokasi_geotag');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aircraft_sanitation_inspections', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('aircraft_sanitation_inspections', 'foto_dokumentasi')) {
                $columnsToDrop[] = 'foto_dokumentasi';
            }
            if (Schema::hasColumn('aircraft_sanitation_inspections', 'lokasi_geotag')) {
                $columnsToDrop[] = 'lokasi_geotag';
            }
            if (Schema::hasColumn('aircraft_sanitation_inspections', 'longitude')) {
                $columnsToDrop[] = 'longitude';
            }
            if (Schema::hasColumn('aircraft_sanitation_inspections', 'latitude')) {
                $columnsToDrop[] = 'latitude';
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
