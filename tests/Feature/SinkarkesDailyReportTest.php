<?php

namespace Tests\Feature;

use App\Models\SinkarkesDailyReport;
use App\Models\SinkarkesReport;
use App\Models\User;
use App\Services\SinkarkesDailySyncService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Inertia\Testing\AssertableInertia as Assert;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class SinkarkesDailyReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->admin = User::where('email', 'admin@hybrid.local')->firstOrFail();
    }

    public function test_guest_is_redirected_from_sinkarkes_daily(): void
    {
        $this->get('/sinkarkes-daily')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_daily_page_and_schema(): void
    {
        $this->actingAs($this->admin)
            ->get('/sinkarkes-daily?wilker=Wilker+Patimban&year=2026&month=11')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('SinkarkesDaily/Index')
                ->where('active_tab', 'kapal')
                ->where('wilker', 'Wilker Patimban')
                ->where('year', 2026)
                ->where('month', 11)
                ->where('dailySchema.kapal.total_metric_columns', 74)
                ->where('dailySchema.kapal.red_mapped_count', 33)
                ->where('dailySchema.pesawat.total_metric_columns', 87)
                ->where('dailySchema.pesawat.red_mapped_count', 21)
                ->has('accumulation.indicators')
            );
    }

    public function test_daily_kapal_and_pesawat_inputs_accumulate_into_sinkarkes_report_and_preserve_manual_rows(): void
    {
        // Pre-seed manual unrelated SINKARKES row (PRL row 228 & KSE.13 row 105)
        SinkarkesReport::create([
            'wilker' => 'Wilker Cirebon',
            'year' => 2026,
            'month' => 11,
            'data' => [
                '105' => 9,
                '228' => 42,
            ],
            'status' => 'submitted',
        ]);

        // Day 1 Kapal: includes COP/PHQC pair check & fumigasi+desinseksi rollup -> 101 (summary 99)
        $this->actingAs($this->admin)
            ->post('/sinkarkes-daily', [
                'tanggal' => '2026-11-02',
                'wilker' => 'Wilker Cirebon',
                'transport_type' => 'kapal',
                'metrics' => [
                    'kd_dn_kapal' => 4,
                    'kd_dn_kru_kapal' => 80,
                    'kd_dn_penumpang' => 120,
                    'kd_dn_demam' => 2, // unmapped epidemiologic field
                    'kd_ln_kapal' => 3,
                    'kd_ln_kru_kapal' => 60,
                    'kd_ln_penumpang' => 45,
                    'dok_cop_phqc_cop_luar_negeri' => 3, // paired with kd_ln_kapal -> should NOT double-count (remains 3)
                    'kb_dn_kapal' => 2,
                    'dok_cop_phqc_phqc_dalam_negeri' => 2, // paired -> remains 2
                    'dok_sanitasi_sscc' => 1,
                    'dok_sanitasi_sscec' => 3,
                    'dok_sanitasi_sertifikat_p3k_kapal' => 2,
                    'dok_ukp_sertifikat_angkut_jenazah' => 1, // -> leaf 199 (summary 198)
                    'dok_ukp_sertifikat_angkut_orang_sakit' => 2, // -> 195
                    'dok_sanitasi_buku_kesehatan_kapal' => 5, // -> 108
                    'dok_lainnya_sertifikat_omka_eksport' => 4, // -> leaf 124 (summary 123)
                    'tindakan_tindakan_fumigasi' => 1, // -> leaf 101 (summary 99)
                    'tindakan_tindakan_desinseksi' => 2, // -> leaf 101 (summary 99)
                    'tindakan_tindakan_desinfeksi' => 3, // -> leaf 104 (summary 102)
                ],
            ])
            ->assertRedirect();

        // Day 2 Kapal: COP LN entered without kd_ln_kapal + extra SIAOS & SSCEC
        $this->actingAs($this->admin)
            ->post('/sinkarkes-daily', [
                'tanggal' => '2026-11-05',
                'wilker' => 'Wilker Cirebon',
                'transport_type' => 'kapal',
                'metrics' => [
                    'kd_dn_kapal' => 6,
                    'dok_cop_phqc_cop_luar_negeri' => 4, // kd_ln_kapal=0 so max(0,4)=4
                    'dok_sanitasi_sscec' => 5,
                    'dok_ukp_sertifikat_angkut_orang_sakit' => 1,
                ],
            ])
            ->assertRedirect();

        // Day 2 Pesawat: adds aircraft rows + combines SIAOS (195) and SIAJ (199 -> 198)
        $this->actingAs($this->admin)
            ->post('/sinkarkes-daily', [
                'tanggal' => '2026-11-05',
                'wilker' => 'Wilker Cirebon',
                'transport_type' => 'pesawat',
                'metrics' => [
                    'kd_dn_pesawat' => 10,
                    'kd_dn_kru_pesawat' => 50,
                    'kd_dn_penumpang' => 1400,
                    'kd_lnt_pesawat' => 1,
                    'kb_ln_pesawat' => 2,
                    'dok_ukp_surat_ket_laik_terbang_sklt' => 6,
                    'dok_ukp_sertifikat_angkut_jenazah_siaj' => 2,
                    'dok_ukp_sertifikat_angkut_orang_sakit_siaos' => 3,
                ],
            ])
            ->assertRedirect();

        $report = SinkarkesReport::where('wilker', 'Wilker Cirebon')
            ->where('year', 2026)
            ->where('month', 11)
            ->firstOrFail();

        $data = $report->data;

        // Manual non-daily rows preserved intact
        $this->assertSame(9, $data['105']);
        $this->assertSame(42, $data['228']);

        // Kapal accumulated leaf values
        $this->assertSame(10, $data['13']); // 4 + 6
        $this->assertSame(80, $data['71']);
        $this->assertSame(120, $data['43']);
        $this->assertSame(7, $data['10']); // Day1 max(3,3)=3 + Day2 max(0,4)=4
        $this->assertSame(60, $data['68']);
        $this->assertSame(45, $data['40']);
        $this->assertSame(2, $data['20']); // max(2,2)
        $this->assertSame(1, $data['94']); // SSCC
        $this->assertSame(8, $data['93']); // SSCEC (3 + 5)
        $this->assertSame(2, $data['95']); // P3K
        $this->assertSame(5, $data['108']); // Buku Kesehatan
        $this->assertSame(4, $data['124']); // OMKA -> rollup 123
        $this->assertSame(3, $data['101']); // fumigasi 1 + desinseksi 2 -> rollup 99
        $this->assertSame(3, $data['104']); // desinfeksi -> rollup 102

        // Pesawat accumulated leaf values
        $this->assertSame(10, $data['29']);
        $this->assertSame(50, $data['85']);
        $this->assertSame(1400, $data['57']);
        $this->assertSame(1, $data['23']);
        $this->assertSame(2, $data['33']);
        $this->assertSame(6, $data['196']); // SKLT

        // Combined Kapal + Pesawat shared UKP indicators
        $this->assertSame(6, $data['195']); // SIAOS: Kapal (2+1) + Pesawat (3)
        $this->assertSame(3, $data['199']); // SIAJ -> rollup 198: Kapal (1) + Pesawat (2)

        // Verify summary totals preview matches rollup rules
        /** @var SinkarkesDailySyncService $sync */
        $sync = app(SinkarkesDailySyncService::class);
        $acc = $sync->accumulateMonth('Wilker Cirebon', 2026, 11);
        $this->assertSame(3, $acc['summary_totals'][99]);
        $this->assertSame(3, $acc['summary_totals'][102]);
        $this->assertSame(4, $acc['summary_totals'][123]);
        $this->assertSame(3, $acc['summary_totals'][198]);
        $this->assertTrue($acc['in_sync']);
    }

    public function test_updating_date_and_deleting_daily_entry_recalculates_old_and_new_months(): void
    {
        $this->actingAs($this->admin)->post('/sinkarkes-daily', [
            'tanggal' => '2026-10-31',
            'wilker' => 'Wilker Kertajati',
            'transport_type' => 'pesawat',
            'metrics' => [
                'kd_dn_pesawat' => 7,
                'dok_ukp_surat_ket_laik_terbang_sklt' => 2,
            ],
        ])->assertRedirect();

        $rec = SinkarkesDailyReport::firstOrFail();

        $octReport = SinkarkesReport::where('wilker', 'Wilker Kertajati')->where('year', 2026)->where('month', 10)->firstOrFail();
        $this->assertSame(7, $octReport->data['29']);

        // Move record from Oct to Nov -> Oct clears, Nov gets 7 & 2
        $this->actingAs($this->admin)->post('/sinkarkes-daily', [
            'id' => $rec->id,
            'tanggal' => '2026-11-01',
            'wilker' => 'Wilker Kertajati',
            'transport_type' => 'pesawat',
            'metrics' => [
                'kd_dn_pesawat' => 7,
                'dok_ukp_surat_ket_laik_terbang_sklt' => 2,
            ],
        ])->assertRedirect();

        $octReport->refresh();
        $this->assertArrayNotHasKey('29', $octReport->data ?? []);

        $novReport = SinkarkesReport::where('wilker', 'Wilker Kertajati')->where('year', 2026)->where('month', 11)->firstOrFail();
        $this->assertSame(7, $novReport->data['29']);
        $this->assertSame(2, $novReport->data['196']);

        // Delete record -> Nov clears back to empty
        $this->actingAs($this->admin)
            ->delete("/sinkarkes-daily/{$rec->id}")
            ->assertRedirect();

        $novReport->refresh();
        $this->assertArrayNotHasKey('29', $novReport->data ?? []);
        $this->assertArrayNotHasKey('196', $novReport->data ?? []);
    }

    public function test_excel_export_and_import_round_trip_accumulates_to_sinkarkes(): void
    {
        SinkarkesDailyReport::create([
            'tanggal' => '2026-11-10',
            'wilker' => 'Wilker Patimban',
            'transport_type' => 'kapal',
            'metrics' => [
                'kd_dn_kapal' => 5,
                'kd_dn_kru_kapal' => 100,
                'kd_dn_penumpang' => 25,
                'dok_sanitasi_sscc' => 2,
            ],
        ]);

        /** @var SinkarkesDailySyncService $sync */
        $sync = app(SinkarkesDailySyncService::class);
        $spreadsheet = $sync->buildSpreadsheet('Wilker Patimban', 2026, 11, false);

        $tmpDir = storage_path('framework/testing');
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0775, true);
        }
        $tmpPath = $tmpDir . DIRECTORY_SEPARATOR . 'sinkarkes_daily_roundtrip.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tmpPath);

        // Clear DB daily & monthly tables before re-importing from Excel
        SinkarkesDailyReport::query()->delete();
        SinkarkesReport::query()->delete();

        $upload = new UploadedFile(
            $tmpPath,
            'rekap_harian.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $this->actingAs($this->admin)
            ->postJson('/sinkarkes-daily/import', [
                'file' => $upload,
                'wilker' => 'Wilker Patimban',
            ])
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('result.records_imported', 1)
            ->assertJsonPath('result.periods_synced', 1);

        @unlink($tmpPath);

        $report = SinkarkesReport::where('wilker', 'Wilker Patimban')
            ->where('year', 2026)
            ->where('month', 11)
            ->firstOrFail();

        $this->assertSame(5, $report->data['13']);
        $this->assertSame(100, $report->data['71']);
        $this->assertSame(25, $report->data['43']);
        $this->assertSame(2, $report->data['94']);
    }
}
