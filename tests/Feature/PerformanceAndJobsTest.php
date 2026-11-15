<?php

namespace Tests\Feature;

use App\Http\Controllers\Inertia\DashboardController;
use App\Jobs\GenerateAuditLogExportJob;
use App\Jobs\RunSimplyAutoSyncJob;
use App\Models\AircraftSanitationInspection;
use App\Models\PrlSiveraVektor;
use App\Models\User;
use App\Services\SiveraService;
use App\Support\DashboardCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PerformanceAndJobsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_cache_invalidates_when_inspection_or_sivera_changes(): void
    {
        $before = DashboardCache::remember('probe:key', 120, fn () => 'first-value');
        $this->assertSame('first-value', $before);

        $cached = DashboardCache::remember('probe:key', 120, fn () => 'stale-should-not-run');
        $this->assertSame('first-value', $cached);

        AircraftSanitationInspection::create([
            'registration_no' => 'AIR-TEST-01',
            'tanda_kebangsaan' => 'PK-TEST',
            'pemilik' => 'Test Air',
            'tanggal' => now()->toDateString(),
            'waktu' => '09:30',
            'kesimpulan' => 'Memenuhi Syarat',
            'petugas_kabin_nama' => 'Petugas Kabin',
            'petugas_bkk_nama' => 'Petugas BKK',
            'status' => 'approved',
        ]);

        $afterInspection = DashboardCache::remember('probe:key', 120, fn () => 'recomputed-after-aircraft');
        $this->assertSame('recomputed-after-aircraft', $afterInspection);

        $sivera = PrlSiveraVektor::create([
            'jenis_vektor' => 'Jentik Aedes',
            'wilker' => 'Wilayah Kerja Cirebon',
            'lokasi' => 'Pelabuhan Cirebon',
            'tanggal' => now()->toDateString(),
            'parameter_utama' => 'ABJ 98%',
            'nilai_indeks' => 98,
            'baku_mutu_status' => 'Memenuhi Syarat',
        ]);

        $afterSivera = DashboardCache::remember('probe:key', 120, fn () => 'recomputed-after-sivera');
        $this->assertSame('recomputed-after-sivera', $afterSivera);

        $serviceMetrics = app(SiveraService::class)->getDashboardMetrics();
        $this->assertSame(1, $serviceMetrics['totalSurveilans']);
        $this->assertSame(1, $serviceMetrics['jentikCount']);

        $sivera->delete();

        $freshMetrics = app(SiveraService::class)->getDashboardMetrics();
        $this->assertSame(0, $freshMetrics['totalSurveilans']);

        DashboardController::getBkkOffices();
        $this->assertTrue(true);
    }

    public function test_admin_can_queue_async_simply_sync_job(): void
    {
        Queue::fake();

        Role::firstOrCreate(['name' => 'Admin']);
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $response = $this->actingAs($admin)
            ->postJson('/database-import/sync-now', ['async' => true]);

        $response->assertStatus(202)
            ->assertJson([
                'success' => true,
                'queued' => true,
            ]);

        Queue::assertPushed(RunSimplyAutoSyncJob::class, function (RunSimplyAutoSyncJob $job) use ($admin) {
            return $job->requestedByUserId === $admin->id;
        });
    }

    public function test_audit_export_job_dispatches_cleanly(): void
    {
        Queue::fake();
        $user = User::factory()->create();

        GenerateAuditLogExportJob::dispatch($user->id, 'csv');

        Queue::assertPushed(GenerateAuditLogExportJob::class, function (GenerateAuditLogExportJob $job) use ($user) {
            return $job->requestedByUserId === $user->id && $job->format === 'csv';
        });
    }

    public function test_ship_surveillance_form_request_validates_required_fields(): void
    {
        $response = $this->post('/ship-surveillance/assessment/submit', []);

        $response->assertSessionHasErrors([
            'nama_kapal',
            'datang_dari',
            'pelabuhan',
            'tanggal_kedatangan',
            'gross_tonnage',
            'jml_crew',
            'kategori',
        ]);
    }

    public function test_scheduler_registers_maintenance_and_sync_commands(): void
    {
        Artisan::call('schedule:list');
        $output = Artisan::output();

        $this->assertStringContainsString('simply:auto-sync', $output);
        $this->assertStringContainsString('activitylog:clean', $output);
        $this->assertStringContainsString('backup:clean', $output);
    }
}
