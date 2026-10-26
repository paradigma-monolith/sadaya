<?php

namespace Tests\Feature;

use App\Models\AircraftSanitationInspection;
use App\Models\User;
use Database\Seeders\AircraftSanitationInspectionSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_public_user_can_submit_aircraft_inspection_with_geotagged_photos(): void
    {
        Storage::fake('public');

        $payload = [
            'pemilik' => 'PT Citilink Indonesia',
            'tanda_kebangsaan' => 'PK-GLQ',
            'tanggal' => now()->format('Y-m-d'),
            'waktu' => '14:20',
            'kabin_penumpang' => ['a' => 'baik'],
            'kompartemen_toilet' => ['a' => 'baik'],
            'galley' => ['a' => 'baik'],
            'dokumen_kesehatan' => ['a' => 'baik'],
            'pembuangan_kotoran' => 'ya',
            'penanganan_air' => ['petugas' => 'ya'],
            'keadaan_makanan' => ['kondisi' => 'baik'],
            'binatang_pengganggu' => ['lain2' => 'Nihil'],
            'surat_sanitasi' => 'ada',
            'kesimpulan' => 'layak',
            'petugas_kabin_nama' => 'Siti Rahmawati',
            'petugas_bkk_nama' => 'dr. Hendra Setiawan',
            'latitude' => -6.903892,
            'longitude' => 107.618615,
            'lokasi_geotag' => 'Apron Barat Bandara Husein Sastranegara',
            'foto_kategori' => ['Kabin Penumpang', 'Kompartemen Toilet'],
            'foto_latitudes' => [-6.903892, -6.903880],
            'foto_longitudes' => [107.618615, 107.618620],
            'foto_accuracies' => [5.5, 6.0],
            'foto_dokumentasi' => [
                UploadedFile::fake()->image('kabin.jpg', 640, 480),
                UploadedFile::fake()->image('toilet.jpg', 640, 480),
            ],
        ];

        $response = $this->post('/travelconsent/submit_aircraft', $payload);

        $inspection = AircraftSanitationInspection::where('tanda_kebangsaan', 'PK-GLQ')->first();
        $this->assertNotNull($inspection);
        $this->assertEqualsWithDelta(-6.903892, $inspection->latitude, 0.0001);
        $this->assertEqualsWithDelta(107.618615, $inspection->longitude, 0.0001);
        $this->assertEquals('Apron Barat Bandara Husein Sastranegara', $inspection->lokasi_geotag);
        $this->assertIsArray($inspection->foto_dokumentasi);
        $this->assertCount(2, $inspection->foto_dokumentasi);
        $this->assertEquals('Kabin Penumpang', $inspection->foto_dokumentasi[0]['category']);

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
