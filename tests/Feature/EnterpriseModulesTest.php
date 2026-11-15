<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\SystemAlertNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class EnterpriseModulesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_roles_and_permissions_are_seeded(): void
    {
        $admin = User::where('email', 'admin@hybrid.local')->first();
        $this->assertNotNull($admin);
        $this->assertTrue($admin->hasRole('Super Admin'));

        $staff = User::where('email', 'staff@hybrid.local')->first();
        $this->assertNotNull($staff);
        $this->assertTrue($staff->hasRole('Staff'));
        $this->assertFalse($staff->hasPermissionTo('manage users'));
    }

    public function test_user_mutations_are_logged_to_activity_log(): void
    {
        $admin = User::where('email', 'admin@hybrid.local')->first();

        $admin->update(['name' => 'Super Admin Updated']);

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'user',
            'subject_id' => $admin->id,
            'event' => 'updated',
        ]);
    }

    public function test_user_can_view_profile_and_update_name(): void
    {
        $user = User::where('email', 'admin@hybrid.local')->first();

        $response = $this->actingAs($user)->get('/profile');
        $response->assertStatus(200);

        $updateResponse = $this->actingAs($user)->patch('/profile', [
            'name' => 'Super Admin Updated Profile',
            'email' => 'admin@hybrid.local',
        ]);

        $updateResponse->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Super Admin Updated Profile',
        ]);
    }

    public function test_user_can_upload_avatar(): void
    {
        Storage::fake('public');
        $user = User::where('email', 'admin@hybrid.local')->first();

        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->actingAs($user)->post('/profile/avatar', [
            'avatar' => $file,
        ]);

        $response->assertRedirect();
        $user->refresh();
        $this->assertNotNull($user->avatar);
        Storage::disk('public')->assertExists($user->avatar);
    }

    public function test_audit_log_export_endpoints_return_successful_downloads(): void
    {
        $user = User::where('email', 'admin@hybrid.local')->first();

        // Trigger an activity log entry
        $user->update(['name' => 'Admin For Export']);

        // Excel
        $excelResponse = $this->actingAs($user)->get('/exports/audit-logs/excel');
        $excelResponse->assertStatus(200);
        $this->assertStringContainsString('audit-logs-', (string) $excelResponse->headers->get('content-disposition'));

        // CSV
        $csvResponse = $this->actingAs($user)->get('/exports/audit-logs/csv');
        $csvResponse->assertStatus(200);
        $this->assertStringContainsString('audit-logs-', (string) $csvResponse->headers->get('content-disposition'));

        // PDF
        $pdfResponse = $this->actingAs($user)->get('/exports/audit-logs/pdf');
        $pdfResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $pdfResponse->headers->get('content-type'));

        // Manual Book Documentation PDF
        $manualBookResponse = $this->actingAs($user)->get('/docs/manual-book/download');
        $manualBookResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $manualBookResponse->headers->get('content-type'));
    }

    public function test_notification_flow_and_mark_all_read(): void
    {
        $user = User::where('email', 'admin@hybrid.local')->first();

        $user->notify(new SystemAlertNotification(
            title: 'Pemberitahuan Sistem',
            message: 'Aktivitas pengujian notifikasi.',
            type: 'info'
        ));

        $this->assertEquals(1, $user->unreadNotifications()->count());

        $readResponse = $this->actingAs($user)->postJson('/api/v1/notifications/read-all');
        $readResponse->assertStatus(200);

        $this->assertEquals(0, $user->unreadNotifications()->count());
    }

    public function test_authenticated_user_can_access_core_inertia_modules(): void
    {
        $user = User::where('email', 'admin@hybrid.local')->first();

        foreach (['/dashboard', '/travel-consent', '/sanitasi-lingkungan', '/sivera'] as $route) {
            $response = $this->actingAs($user)->get($route);
            $response->assertStatus(200);
        }
    }

    public function test_scramble_api_docs_endpoint_is_accessible(): void
    {
        $response = $this->get('/docs/api');
        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_access_inertia_dashboard_with_chart_data(): void
    {
        $user = User::where('email', 'admin@hybrid.local')->first();

        $response = $this->actingAs($user)->get('/inertia/dashboard');
        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_access_audit_logs_endpoint(): void
    {
        $user = User::where('email', 'admin@hybrid.local')->first();

        $response = $this->actingAs($user)->get('/audit-logs');
        $response->assertStatus(200);
    }
}
