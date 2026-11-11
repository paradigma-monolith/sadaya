<?php

namespace Tests\Feature;

use App\Models\ShipHealthSurveillance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ShipSurveillanceAssessmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_domestic_form_omits_q1_and_starts_at_q2(): void
    {
        $response = $this->get('/ship-surveillance/assessment/domestic');

        $response->assertStatus(200);
        $response->assertDontSee('name="Q1"', false);
        $response->assertDontSee('Apakah mengunjungi negara lain dalam 14 hari terakhir?');
        $response->assertSee('name="Q2"', false);
        $response->assertSee('Apakah ada orang yang meninggal di atas kapal selama dalam perjalanan yang bukan diakibatkan oleh kecelakaan?');
        $response->assertSee('<span class="fw-bold text-dark">18.</span>', false);
        $response->assertSee('name="Q19"', false);
    }

    public function test_international_form_renders_q1_country_multiselect_with_red_zone_flags(): void
    {
        $response = $this->get('/ship-surveillance/assessment/international');

        $response->assertStatus(200);
        $response->assertSee('1.', false);
        $response->assertSee('Have you visited any countries in the last 14 days?');
        $response->assertSee('name="Q1[]"', false);
        $response->assertSee('id="negaraSinggahSelect"', false);
        $response->assertSee('id="originCountry"', false);
        $response->assertSee('value="Singapore" data-merah="true"', false);
        $response->assertSee('value="Indonesia" data-merah="false"', false);
        $response->assertSee('value="Türkiye" data-merah="false"', false);
        $response->assertSee('syncOriginToQ1', false);
        $response->assertDontSee('name="Q1"', false);
        $response->assertDontSee('countries with reported infectious disease outbreaks');
    }

    public function test_domestic_submission_stores_structured_questions_without_negara_singgah(): void
    {
        $response = $this->post('/ship-surveillance/assessment/submit', [
            'kategori' => 'Dalam Negeri',
            'nama_kapal' => 'KM. NUSANTARA BAHARI',
            'datang_dari' => 'SURABAYA',
            'pelabuhan' => 'Wilayah Kerja Patimban',
            'tanggal_kedatangan' => '2026-11-11',
            'gross_tonnage' => 1500,
            'jml_crew' => 25,
            'skoring' => 0,
            'Q2' => 0,
            'Q3' => 0,
            'Q4' => 0,
            'Q5' => 0,
            'Q6' => 0,
            'Q7' => 0,
            'Q8' => 0,
            'Q9' => 0,
            'Q10' => 0,
            'Q11' => 0,
            'Q12' => 0,
            'Q13' => 0,
            'Q14' => 0,
            'Q15' => 0,
            'Q16' => 0,
            'Q17' => 0,
            'Q18' => 0,
            'Q19' => 0,
        ]);

        $record = ShipHealthSurveillance::first();
        $this->assertNotNull($record);
        $response->assertRedirect(route('ship-surveillance.receipt', $record->registration_no));

        $this->assertNull($record->negara_singgah_14_hari);
        $this->assertSame(0, $record->skoring);
        $this->assertSame('RENDAH', $record->kresiko);
        $this->assertSame('SANDAR', $record->hasil_status);
        $this->assertSame('Tidak Berlaku (Domestik)', $record->questions[1]['answer']);
        $this->assertSame(0, $record->questions[1]['score']);
        $this->assertSame('Ya', $record->questions[19]['answer']);
    }

    public function test_international_submission_with_red_zone_country_adds_20_points(): void
    {
        Storage::fake('public');

        $response = $this->post('/ship-surveillance/assessment/submit', [
            'kategori' => 'Luar Negeri',
            'nama_kapal' => 'MV. PACIFIC VOYAGER',
            'datang_dari' => 'PORT OF SINGAPORE',
            'origin_country' => 'Singapore',
            'pelabuhan' => 'Wilayah Kerja Patimban',
            'tanggal_kedatangan' => '2026-11-11',
            'gross_tonnage' => 5000,
            'jml_crew' => 30,
            'skoring' => 0, // Pastikan server tetap hitung 20 dari negara zona merah
            'Q1' => ['Singapore', 'Indonesia'],
            'Q2' => 0,
            'Q19' => 0,
            'surat_pernyataan_nahkoda' => UploadedFile::fake()->create('statement.pdf', 100, 'application/pdf'),
        ]);

        $record = ShipHealthSurveillance::first();
        $this->assertNotNull($record);
        $response->assertRedirect(route('ship-surveillance.receipt', $record->registration_no));

        $this->assertSame('Singapore, Indonesia', $record->negara_singgah_14_hari);
        $this->assertSame(20, $record->skoring);
        $this->assertSame('TINGGI', $record->kresiko);
        $this->assertSame('ZONA KARANTINA', $record->hasil_status);
        $this->assertSame('Singapore, Indonesia', $record->questions[1]['answer']);
        $this->assertSame(20, $record->questions[1]['score']);
        $this->assertNotNull($record->surat_pernyataan_nahkoda);
    }

    public function test_international_submission_with_non_red_countries_scores_zero(): void
    {
        Storage::fake('public');

        $response = $this->post('/ship-surveillance/assessment/submit', [
            'kategori' => 'Luar Negeri',
            'nama_kapal' => 'MV. ARCHIPELAGO',
            'datang_dari' => 'PORT OF HAIPHONG',
            'origin_country' => 'Viet Nam',
            'pelabuhan' => 'Wilayah Kerja Cirebon',
            'tanggal_kedatangan' => '2026-11-11',
            'gross_tonnage' => 2500,
            'jml_crew' => 20,
            'Q1' => ['Viet Nam', 'United States'],
            'Q19' => 0,
            'surat_pernyataan_nahkoda' => UploadedFile::fake()->create('statement.pdf', 100, 'application/pdf'),
        ]);

        $record = ShipHealthSurveillance::first();
        $this->assertNotNull($record);
        $response->assertRedirect(route('ship-surveillance.receipt', $record->registration_no));

        $this->assertSame('Viet Nam, United States', $record->negara_singgah_14_hari);
        $this->assertSame(0, $record->skoring);
        $this->assertSame('RENDAH', $record->kresiko);
        $this->assertSame('SANDAR', $record->hasil_status);
        $this->assertSame(0, $record->questions[1]['score']);

        $receipt = $this->get(route('ship-surveillance.receipt', $record->registration_no));
        $receipt->assertStatus(200);
        $receipt->assertSee('Viet Nam, United States');
    }

    public function test_tangkap_dashboard_supports_clean_active_tab_query_parameter(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/ship-surveillance')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('ShipSurveillance/Index')
                ->where('active_tab', 'data')
            );

        $this->actingAs($user)
            ->get('/ship-surveillance?tab=laporan')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('ShipSurveillance/Index')
                ->where('active_tab', 'laporan')
            );

        $this->actingAs($user)
            ->get('/ship-surveillance?tab=red-zones')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('ShipSurveillance/Index')
                ->where('active_tab', 'red-zones')
            );

        $this->actingAs($user)
            ->get('/ship-surveillance?tab=unknown-tab')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('ShipSurveillance/Index')
                ->where('active_tab', 'data')
            );
    }
}

