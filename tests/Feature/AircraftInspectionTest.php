<?php

namespace Tests\Feature;

use App\Models\AircraftSanitationInspection;
use App\Models\User;
use Database\Seeders\AircraftSanitationInspectionSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AircraftInspectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(AircraftSanitationInspectionSeeder::class);
    }

    public function test_public_aircraft_form_can_be_rendered(): void
    {
        $response = $this->get('/travelconsent/public_aircraft');

        $response->assertStatus(200);
        $response->assertSee('AIRCRAFT SANITATION INSPECTION CHECK LIST');
        $response->assertSee('Kop_BKK_bandung.png');
    }

    public function test_public_user_can_submit_aircraft_inspection(): void
    {
        $payload = [
            'pemilik' => 'PT Garuda Indonesia (Persero) Tbk',
            'tanda_kebangsaan' => 'PK-GAA',
            'tanggal' => now()->format('Y-m-d'),
            'waktu' => '09:30',
            'kabin_penumpang' => [
                'a' => 'baik',
                'b' => 'baik',
                'c' => 'baik',
                'd' => 'baik',
                'e' => 'baik',
                'f' => 'baik',
            ],
            'kompartemen_toilet' => [
                'a' => 'baik',
                'b' => 'baik',
                'c' => 'baik',
                'd' => 'baik',
                'e' => 'baik',
            ],
            'galley' => [
                'a' => 'baik',
                'b' => 'baik',
                'c' => 'baik',
                'd' => 'baik',
                'e' => 'baik',
            ],
            'dokumen_kesehatan' => [
                'a' => 'baik',
                'b' => 'baik',
                'c' => 'baik',
                'd' => 'baik',
            ],
            'pembuangan_kotoran' => 'ya',
            'penanganan_air' => [
                'petugas' => 'ya',
                'bau' => false,
                'warna' => false,
                'rasa' => false,
            ],
            'keadaan_makanan' => [
                'kondisi' => 'baik',
                'bau' => false,
                'lendir' => false,
                'jamur' => false,
                'jumlah' => '100 Porsi',
                'jenis' => 'Hot Meal Standard',
            ],
            'binatang_pengganggu' => [
                'nyamuk' => false,
                'lalat' => false,
                'kecoa' => false,
                'tikus' => false,
                'lain2' => 'Nihil',
            ],
            'surat_sanitasi' => 'ada',
            'kesimpulan' => 'layak',
            'petugas_kabin_nama' => 'Budi Sudarsono',
            'petugas_bkk_nama' => 'dr. Hendra Setiawan',
        ];

        $response = $this->post('/travelconsent/submit_aircraft', $payload);

        $this->assertDatabaseHas('aircraft_sanitation_inspections', [
            'pemilik' => 'PT Garuda Indonesia (Persero) Tbk',
            'tanda_kebangsaan' => 'PK-GAA',
            'kesimpulan' => 'layak',
            'petugas_kabin_nama' => 'Budi Sudarsono',
        ]);

        $inspection = AircraftSanitationInspection::where('tanda_kebangsaan', 'PK-GAA')->first();
        $this->assertNotNull($inspection);
        $this->assertNotNull($inspection->registration_no);

        $response->assertRedirect(route('travelconsent.receipt', $inspection->registration_no));
    }

    public function test_authenticated_user_can_access_inspection_management_and_dashboard(): void
    {
        $admin = User::first();

        // 1. Data management page
        $resIndex = $this->actingAs($admin)->get('/aircraft-inspections');
        $resIndex->assertStatus(200);

        // 2. Analytics Dashboard
        $resDash = $this->actingAs($admin)->get('/aircraft-inspections/dashboard');
        $resDash->assertStatus(200);
    }

    public function test_authenticated_user_can_update_status_and_export(): void
    {
        $admin = User::first();
        $inspection = AircraftSanitationInspection::first();

        $this->assertNotNull($inspection);

        // Update status
        $resUpdate = $this->actingAs($admin)->put("/aircraft-inspections/{$inspection->id}/status", [
            'status' => 'approved',
            'kesimpulan' => 'layak',
            'tindakan' => 'disinseksi',
            'catatan_rekomendasi' => 'Disetujui untuk izin terbang.',
        ]);

        $resUpdate->assertSessionHas('success');

        $this->assertDatabaseHas('aircraft_sanitation_inspections', [
            'id' => $inspection->id,
            'status' => 'approved',
            'tindakan' => 'disinseksi',
        ]);

        // Export Excel
        $resExport = $this->actingAs($admin)->get('/aircraft-inspections/export?format=xlsx');
        $resExport->assertStatus(200);

        // PDF Certificate Download
        $resPdf = $this->actingAs($admin)->get("/aircraft-inspections/{$inspection->id}/pdf");
        $resPdf->assertStatus(200);
    }
}
