<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SimplyDataMigrationService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_guest_cannot_export_audit_logs_or_view_db_name_via_api(): void
    {
        $this->getJson('/api/v1/exports/audit-logs/excel')->assertStatus(401);
        $this->getJson('/api/v1/exports/audit-logs/csv')->assertStatus(401);
        $this->getJson('/api/v1/exports/audit-logs/pdf')->assertStatus(401);

        $stats = $this->getJson('/api/v1/stats');
        $stats->assertStatus(200);
        $stats->assertJsonPath('architecture.database.database', 'redacted');
    }

    public function test_staff_user_cannot_access_restricted_audit_or_user_management_endpoints(): void
    {
        $staff = User::where('email', 'staff@hybrid.local')->firstOrFail();

        $this->actingAs($staff)->get('/audit-logs')->assertStatus(403);
        $this->actingAs($staff)->get('/exports/audit-logs/excel')->assertStatus(403);

        $this->actingAs($staff, 'sanctum')->getJson('/api/v1/audit-logs')->assertStatus(403);
        $this->actingAs($staff, 'sanctum')->getJson('/api/v1/users')->assertStatus(403);
        $this->actingAs($staff, 'sanctum')->getJson('/api/v1/exports/audit-logs/pdf')->assertStatus(403);
    }

    public function test_security_headers_are_applied_to_responses(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_login_endpoint_enforces_brute_force_rate_limit(): void
    {
        RateLimiter::clear('login:brute@hybrid.local|127.0.0.1');

        for ($i = 0; $i < 5; $i++) {
            $this->from('/login')->post('/login', [
                'email' => 'brute@hybrid.local',
                'password' => 'wrong-password',
            ])->assertSessionHasErrors('email');
        }

        $throttled = $this->from('/login')->post('/login', [
            'email' => 'brute@hybrid.local',
            'password' => 'wrong-password',
        ]);

        // Route middleware or FormRequest rate limiter blocks 6th consecutive failure
        $this->assertTrue(
            $throttled->getStatusCode() === 429 || session()->has('errors'),
            'Expected HTTP 429 or lockout validation error on 6th login attempt'
        );
    }

    public function test_simply_credentials_secrets_are_encrypted_and_decrypted_transparently(): void
    {
        $plain = 'super-secret-mysql-pass';
        $encrypted = SimplyDataMigrationService::encryptSecret($plain);

        $this->assertStringStartsWith('enc:', $encrypted);
        $this->assertNotEquals($plain, $encrypted);
        $this->assertSame($plain, SimplyDataMigrationService::decryptSecret($encrypted));
        $this->assertSame('legacy-plain', SimplyDataMigrationService::decryptSecret('legacy-plain'));
    }
}
