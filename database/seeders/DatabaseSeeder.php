<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(KantorWilkerSeeder::class);
        $this->call(LoginHistorySeeder::class);
        $this->call(AircraftSanitationInspectionSeeder::class);
        $this->call(ShipSanitationInspectionSeeder::class);
        $this->call(FlightMedicalInspectionSeeder::class);
    }
}
