<?php

namespace Tests\Feature;

use App\Models\AircraftSanitationInspection;
use App\Models\FlightMedicalInspection;
use App\Models\ShipSanitationInspection;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TravelConsentHubTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->admin = User::where('email', 'admin@hybrid.local')->firstOrFail();
    }

    public function test_guest_is_redirected_from_travel_consent_hub(): void
    {
        $this->get('/travel-consent')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_unified_travel_consent_tabs(): void
    {
        $this->actingAs($this->admin)
            ->get('/travel-consent')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('TravelConsent/Index')
                ->where('active_tab', 'portal')
                ->has('aircraft.inspections.data')
                ->has('aircraft.stats')
                ->has('ship.inspections.data')
                ->has('ship.stats')
                ->has('alat.inspections.data')
                ->has('alat.stats')
                ->has('obat.inspections.data')
                ->has('obat.stats')
                ->has('summary')
            );

        foreach (['portal', 'aircraft', 'ship', 'alat', 'obat'] as $tab) {
            $this->actingAs($this->admin)
                ->get("/travel-consent/{$tab}")
                ->assertStatus(200)
                ->assertInertia(fn (Assert $page) => $page
                    ->component('TravelConsent/Index')
                    ->where('active_tab', $tab)
                );
        }
    }

    public function test_legacy_routes_open_corresponding_tab_in_travel_consent_hub(): void
    {
        $legacyMap = [
            '/aircraft-inspections' => 'aircraft',
            '/ship-inspections' => 'ship',
            '/flight-medical-inspections/alat' => 'alat',
            '/flight-medical-inspections/obat' => 'obat',
        ];

        foreach ($legacyMap as $url => $expectedTab) {
            $this->actingAs($this->admin)
                ->get($url)
                ->assertStatus(200)
                ->assertInertia(fn (Assert $page) => $page
                    ->component('TravelConsent/Index')
                    ->where('active_tab', $expectedTab)
                );
        }
    }

    public function test_tab_scoped_filters_work_without_cross_tab_interference(): void
    {
        AircraftSanitationInspection::create([
            'registration_no' => 'BKK-AC-TEST-01',
            'pemilik' => 'Garuda Test Airline',
            'tanda_kebangsaan' => 'PK-GTA',
            'tanggal' => now()->toDateString(),
            'waktu' => '10:00',
            'kesimpulan' => 'layak',
            'petugas_kabin_nama' => 'Crew Satu',
            'petugas_bkk_nama' => 'Petugas BKK',
            'status' => 'approved',
        ]);

        AircraftSanitationInspection::create([
            'registration_no' => 'BKK-AC-TEST-02',
            'pemilik' => 'Citilink Other Airline',
            'tanda_kebangsaan' => 'PK-CTO',
            'tanggal' => now()->toDateString(),
            'waktu' => '11:00',
            'kesimpulan' => 'tidak_layak',
            'petugas_kabin_nama' => 'Crew Dua',
            'petugas_bkk_nama' => 'Petugas BKK',
            'status' => 'submitted',
        ]);

        ShipSanitationInspection::create([
            'registration_no' => 'BKK-SP-TEST-01',
            'pelabuhan' => 'Wilayah Kerja Patimban',
            'tanggal' => now()->toDateString(),
            'sertifikat_mencatat' => 'Pembebasan',
            'imo_no' => '9876543',
            'nama_kapal' => 'MV Nusantara Jaya',
            'nama_pemeriksa' => 'Pemeriksa Laut',
            'status' => 'approved',
        ]);

        FlightMedicalInspection::create([
            'registration_no' => 'BKK-ALT-TEST-01',
            'type' => 'alat',
            'penerbangan_haji_h' => '1447',
            'penerbangan_haji_m' => '2026',
            'embarkasi' => 'Kertajati (KJT)',
            'nomor_kloter' => 'KJT-01',
            'crew_nama' => 'Awak Alkes',
            'pelapor_nama' => 'Pelapor Alkes',
            'status' => 'submitted',
            'items_p3k' => [['nama' => 'Gunting', 'satuan' => 'Buah', 'isi' => 1, 'terpakai' => 1]],
            'items_emergensi' => [],
        ]);

        FlightMedicalInspection::create([
            'registration_no' => 'BKK-OBT-TEST-01',
            'type' => 'obat',
            'penerbangan_haji_h' => '1447',
            'penerbangan_haji_m' => '2026',
            'embarkasi' => 'Bekasi (JKS)',
            'nomor_kloter' => 'JKS-02',
            'crew_nama' => 'Awak Obat',
            'pelapor_nama' => 'Pelapor Obat',
            'status' => 'approved',
            'items_p3k' => [['nama' => 'Paracetamol', 'satuan' => 'Tablet', 'isi' => 10, 'terpakai' => 2]],
            'items_emergensi' => [],
        ]);

        $this->actingAs($this->admin)
            ->get('/travel-consent?tab=aircraft&aircraft_search=Garuda')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('TravelConsent/Index')
                ->where('active_tab', 'aircraft')
                ->has('aircraft.inspections.data', 1)
                ->where('aircraft.inspections.data.0.pemilik', 'Garuda Test Airline')
                ->has('ship.inspections.data', 1)
                ->has('alat.inspections.data', 1)
                ->has('obat.inspections.data', 1)
                ->where('alat.stats.total_items_used', 1)
                ->where('obat.stats.total_items_used', 2)
                ->where('summary.total_records', 5)
            );

        // Legacy unprefixed query param fallback scoped to active tab
        $this->actingAs($this->admin)
            ->get('/aircraft-inspections?search=Citilink')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('TravelConsent/Index')
                ->where('active_tab', 'aircraft')
                ->has('aircraft.inspections.data', 1)
                ->where('aircraft.inspections.data.0.pemilik', 'Citilink Other Airline')
                ->has('ship.inspections.data', 1)
            );
    }

    public function test_status_updates_from_travel_consent_tabs_work_end_to_end(): void
    {
        $ship = ShipSanitationInspection::create([
            'registration_no' => 'BKK-SP-UPD-01',
            'pelabuhan' => 'Wilayah Kerja Cirebon',
            'tanggal' => now()->toDateString(),
            'sertifikat_mencatat' => 'Pembebasan',
            'imo_no' => '9123456',
            'nama_kapal' => 'MV Sinar Cirebon',
            'nama_pemeriksa' => 'Pemeriksa',
            'status' => 'submitted',
        ]);

        $alat = FlightMedicalInspection::create([
            'registration_no' => 'BKK-ALT-UPD-01',
            'type' => 'alat',
            'penerbangan_haji_h' => '1447',
            'penerbangan_haji_m' => '2026',
            'embarkasi' => 'Kertajati (KJT)',
            'nomor_kloter' => 'KJT-05',
            'crew_nama' => 'Crew Uji',
            'pelapor_nama' => 'Pelapor Uji',
            'status' => 'submitted',
            'items_p3k' => [],
            'items_emergensi' => [],
        ]);

        $this->actingAs($this->admin)
            ->from('/travel-consent/ship')
            ->put("/ship-inspections/{$ship->id}/status", [
                'status' => 'approved',
                'sertifikat_mencatat' => 'Pembebasan',
                'pejabat_penerbit' => 'Kepala BKK Bandung',
                'keterangan' => 'Memenuhi standar WHO IHR 2005',
            ])
            ->assertRedirect('/travel-consent/ship')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('ship_sanitation_inspections', [
            'id' => $ship->id,
            'status' => 'approved',
        ]);

        $this->actingAs($this->admin)
            ->from('/travel-consent/alat')
            ->put("/flight-medical-inspections/{$alat->id}/status", [
                'status' => 'approved',
                'keterangan' => 'Stok telah diperiksa',
            ])
            ->assertRedirect('/travel-consent/alat')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('flight_medical_inspections', [
            'id' => $alat->id,
            'status' => 'approved',
        ]);
    }
}
