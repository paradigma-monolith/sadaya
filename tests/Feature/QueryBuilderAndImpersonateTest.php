<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QueryBuilderAndImpersonateTest extends TestCase
{
    use RefreshDatabase;

    public function test_query_builder_filters_and_sorts_users(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $staffRole = Role::firstOrCreate(['name' => 'Staff', 'guard_name' => 'web']);

        $admin = User::factory()->create([
            'name' => 'Alice Admin',
            'email' => 'alice@example.com',
        ]);
        $admin->assignRole($adminRole);

        $bob = User::factory()->create([
            'name' => 'Bob Builder',
            'email' => 'bob@example.com',
        ]);
        $bob->assignRole($staffRole);

        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/v1/users?filter[name]=Bob');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.name', 'Bob Builder');

        // Regular Staff cannot access user listing API
        $this->actingAs($bob, 'sanctum')
            ->getJson('/api/v1/users')
            ->assertStatus(403);
    }

    public function test_super_admin_can_impersonate_user_and_leave(): void
    {
        Role::create(['name' => 'Super Admin']);
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');

        $targetUser = User::factory()->create([
            'name' => 'Staff Member',
        ]);

        $this->assertTrue($superAdmin->canImpersonate());
        $this->assertTrue($targetUser->canBeImpersonated());

        // Perform impersonation
        $this->actingAs($superAdmin)->get(route('impersonate', $targetUser->id));

        $this->assertEquals($targetUser->id, auth()->id());
        $this->assertTrue(app('impersonate')->isImpersonating());

        // Leave impersonation
        $this->get(route('impersonate.leave'));

        $this->assertEquals($superAdmin->id, auth()->id());
        $this->assertFalse(app('impersonate')->isImpersonating());
    }

    public function test_backup_configuration_is_registered(): void
    {
        $backupConfig = config('backup');
        $this->assertIsArray($backupConfig);
        $this->assertArrayHasKey('backup', $backupConfig);
        $this->assertArrayHasKey('name', $backupConfig['backup']);
    }
}
