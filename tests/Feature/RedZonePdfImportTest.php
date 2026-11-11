<?php

namespace Tests\Feature;

use App\Models\QuarantineCountry;
use App\Models\QuarantineCountryImportLog;
use App\Models\ShipHealthSurveillance;
use App\Models\User;
use App\Services\RedZonePdfImportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class RedZonePdfImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_seeds_195_countries_and_requires_auth_for_red_zone_endpoints(): void
    {
        $this->assertSame(195, QuarantineCountry::count());
        $map = ShipHealthSurveillance::getCountriesMap();
        $this->assertCount(195, $map);
        $this->assertTrue($map['Singapore']);
        $this->assertFalse($map['Indonesia']);

        $this->get('/ship-surveillance/red-zones')->assertRedirect('/login');
        $this->post('/ship-surveillance/red-zones/preview-pdf')->assertRedirect('/login');
        $this->post('/ship-surveillance/red-zones/apply')->assertRedirect('/login');
    }

    public function test_preview_and_apply_pdf_update_reflected_in_international_form_and_scoring(): void
    {
        $user = User::factory()->create();

        // Generate a real PDF using DomPDF with a NEGARA column table and footer note
        $html = <<<'HTML'
        <html><body>
          <h3>NEGARA/WILAYAH TERJANGKIT</h3>
          <table border="1" cellspacing="0" cellpadding="4">
            <thead>
              <tr><th>NEGARA</th><th>PROVINSI</th><th>PENYAKIT</th></tr>
            </thead>
            <tbody>
              <tr><td>United States of America</td><td>Alabama</td><td>WEST NILE VIRUS</td></tr>
              <tr><td>Vietnam</td><td>Hanoi</td><td>MENINGOKOKUS</td></tr>
              <tr><td>Singapore</td><td>-</td><td>MPOX</td></tr>
              <tr><td>Kosovo</td><td>Peja</td><td>WEST NILE VIRUS</td></tr>
            </tbody>
          </table>
          <p>Keterangan: Update mingguan</p>
        </body></html>
        HTML;

        $pdfBytes = Pdf::loadHTML($html)->output();
        $uploadedPdf = UploadedFile::fake()->createWithContent('DAFTAR_NEGARA_TERJANGKIT.pdf', $pdfBytes);

        // 1. Preview PDF
        $previewRes = $this->actingAs($user)->postJson('/ship-surveillance/red-zones/preview-pdf', [
            'file' => $uploadedPdf,
            'mode' => 'replace',
        ]);

        $previewRes->assertStatus(200);
        $previewRes->assertJsonPath('success', true);
        $previewRes->assertJsonPath('preview.total_extracted', 4);
        $previewRes->assertJsonPath('preview.matched_count', 3);

        $previewData = $previewRes->json('preview');
        $this->assertSame(['United States of America', 'Vietnam', 'Singapore', 'Kosovo'], $previewData['extracted_countries']);
        $this->assertSame(['Kosovo'], $previewData['unmatched_countries']);

        // United States & Viet Nam were false by default, so they appear in newly_flagged
        $newlyNames = collect($previewData['newly_flagged'])->pluck('canonical_name')->all();
        $this->assertContains('United States', $newlyNames);
        $this->assertContains('Viet Nam', $newlyNames);

        // 2. Apply update in replace mode including Kosovo as new country
        $applyRes = $this->actingAs($user)->postJson('/ship-surveillance/red-zones/apply', [
            'red_countries' => $previewData['recommended_red_countries'],
            'new_countries' => ['Kosovo'],
            'mode' => 'replace',
            'source_filename' => 'DAFTAR_NEGARA_TERJANGKIT.pdf',
            'extracted_countries' => $previewData['extracted_countries'],
            'source_label_map' => [
                'United States' => 'United States of America',
                'Viet Nam' => 'Vietnam',
                'Singapore' => 'Singapore',
            ],
        ]);

        $applyRes->assertStatus(200);
        $applyRes->assertJsonPath('success', true);

        // Now DB has 196 countries and ONLY 4 are red zone (United States, Viet Nam, Singapore, Kosovo)
        $this->assertSame(196, QuarantineCountry::count());
        $this->assertSame(4, QuarantineCountry::where('is_red_zone', true)->count());
        $this->assertTrue((bool) QuarantineCountry::where('name', 'United States')->value('is_red_zone'));
        $this->assertTrue((bool) QuarantineCountry::where('name', 'Viet Nam')->value('is_red_zone'));
        $this->assertTrue((bool) QuarantineCountry::where('name', 'Kosovo')->value('is_red_zone'));
        $this->assertFalse((bool) QuarantineCountry::where('name', 'Malaysia')->value('is_red_zone'));

        $this->assertDatabaseHas('quarantine_country_import_logs', [
            'filename' => 'DAFTAR_NEGARA_TERJANGKIT.pdf',
            'mode' => 'replace',
            'matched_count' => 3,
            'added_countries_count' => 1,
        ]);

        // Verify TANGKAP admin page receives red_zones_data prop
        $indexRes = $this->actingAs($user)->get('/ship-surveillance');
        $indexRes->assertStatus(200);
        $indexRes->assertSee('red_zones_data', false);

        // 3. International form renders updated data-merah flags dynamically
        $formRes = $this->actingAs($user)->get('/ship-surveillance/assessment/international');
        $formRes->assertStatus(200);
        $formRes->assertSee('<option value="Viet Nam" data-merah="true"', false);
        $formRes->assertSee('<option value="United States" data-merah="true"', false);
        $formRes->assertSee('<option value="Malaysia" data-merah="false"', false);
        $formRes->assertSee('<option value="Kosovo" data-merah="true"', false);

        // 4. Server-side submission now scores Viet Nam as +20 Red Zone
        $submitRes = $this->post('/ship-surveillance/assessment/submit', [
            'nama_kapal' => 'MV DYNAMIC REDZONE',
            'datang_dari' => 'Ho Chi Minh',
            'origin_country' => 'Viet Nam',
            'pelabuhan' => 'Wilayah Kerja Patimban',
            'tanggal_kedatangan' => '2026-11-11',
            'gross_tonnage' => 6000,
            'jml_crew' => 24,
            'kategori' => 'Luar Negeri',
            'Q1' => ['Viet Nam'],
            'Q19' => '0',
        ]);

        $submitRes->assertRedirect();
        $record = ShipHealthSurveillance::where('nama_kapal', 'MV DYNAMIC REDZONE')->firstOrFail();
        $this->assertSame(20, $record->skoring);
        $this->assertSame('TINGGI', $record->kresiko);
        $this->assertSame('ZONA KARANTINA', $record->hasil_status);
    }

    public function test_merge_mode_preserves_existing_red_zones_and_manual_toggle_works(): void
    {
        $user = User::factory()->create();

        /** @var RedZonePdfImportService $svc */
        $svc = app(RedZonePdfImportService::class);
        $svc->applyUpdate(['Viet Nam'], [], 'merge', 'MERGE_TEST.pdf', $user->id, ['Vietnam']);

        // Existing Singapore stays true while Viet Nam becomes true
        $map = ShipHealthSurveillance::getCountriesMap();
        $this->assertTrue($map['Singapore']);
        $this->assertTrue($map['Viet Nam']);

        $log = QuarantineCountryImportLog::latest('id')->firstOrFail();
        $this->assertSame('merge', $log->mode);

        // Manual toggle off Viet Nam
        $vietNam = QuarantineCountry::where('name', 'Viet Nam')->firstOrFail();
        $this->actingAs($user)
            ->patchJson("/ship-surveillance/red-zones/{$vietNam->id}/toggle", ['is_red_zone' => false])
            ->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertFalse(ShipHealthSurveillance::getCountriesMap()['Viet Nam']);

        // Add manual country & reset defaults
        $this->actingAs($user)
            ->postJson('/ship-surveillance/red-zones/country', [
                'name' => 'Test Island',
                'is_red_zone' => true,
            ])
            ->assertStatus(200);
        $this->assertTrue(ShipHealthSurveillance::getCountriesMap()['Test Island']);

        $this->actingAs($user)
            ->postJson('/ship-surveillance/red-zones/reset')
            ->assertStatus(200);
        $this->assertSame(
            ShipHealthSurveillance::QUARANTINE_COUNTRIES['Singapore'],
            ShipHealthSurveillance::getCountriesMap()['Singapore']
        );
    }

    public function test_kemenkes_sample_pdf_extraction_is_accurate(): void
    {
        $samplePdf = 'C:/Users/USER/Downloads/Daftar Negara Zona Merah.pdf';
        if (!is_file($samplePdf)) {
            $this->markTestSkipped('Sample Kemenkes PDF not present on this machine.');
        }

        /** @var RedZonePdfImportService $svc */
        $svc = app(RedZonePdfImportService::class);
        $preview = $svc->buildPreviewFromPdf($samplePdf, 'DAFTAR NEGARA TERJANGKIT PENYAKIT INFEKSI EMERGING - Google Spreadsheet.pdf', 'replace');

        $this->assertSame(18, $preview['pages_parsed']);
        $this->assertSame(131, $preview['total_extracted']);
        $this->assertSame(127, $preview['matched_count']);
        $this->assertSame(['Mayotte', 'Reunion', 'French Guiana', 'Kosovo'], $preview['unmatched_countries']);
        $this->assertTrue($preview['indonesia_in_pdf']);
        $this->assertEmpty($preview['warnings']);

        $matchedNames = collect($preview['matched_records'])->pluck('canonical_name')->all();
        $this->assertContains('Democratic Republic of the Congo', $matchedNames);
        $this->assertContains('United States', $matchedNames);
        $this->assertContains('Viet Nam', $matchedNames);
        $this->assertContains('Türkiye', $matchedNames);
        $this->assertContains('Singapore', $matchedNames);
    }

    public function test_invalid_pdf_upload_returns_validation_error(): void
    {
        $user = User::factory()->create();
        $invalid = UploadedFile::fake()->createWithContent('not_a_pdf.pdf', 'NOT-PDF-BYTES');

        $res = $this->actingAs($user)->postJson('/ship-surveillance/red-zones/preview-pdf', [
            'file' => $invalid,
        ]);

        $res->assertStatus(422);
        $res->assertJsonPath('success', false);
    }
}
