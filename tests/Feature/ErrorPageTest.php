<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_unknown_web_route_renders_custom_inertia_404_error_page(): void
    {
        $this->get('/halaman-yang-tidak-tersedia-404')
            ->assertStatus(404)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error')
                ->where('status', 404)
                ->where('isAuthenticated', false)
            );
    }

    public function test_authenticated_non_admin_user_receives_custom_inertia_403_error_page(): void
    {
        $staff = User::where('email', 'staff@hybrid.local')->firstOrFail();

        $this->actingAs($staff)
            ->get('/database-import')
            ->assertStatus(403)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error')
                ->where('status', 403)
                ->where('isAuthenticated', true)
            );
    }

    public function test_json_request_to_missing_api_endpoint_still_returns_json_404(): void
    {
        $this->getJson('/api/endpoint-tidak-ditemukan')
            ->assertStatus(404)
            ->assertHeader('content-type', 'application/json');
    }
}
