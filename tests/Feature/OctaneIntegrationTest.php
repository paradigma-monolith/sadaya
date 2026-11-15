<?php

namespace Tests\Feature;

use Laravel\Octane\OctaneServiceProvider;
use Tests\TestCase;

class OctaneIntegrationTest extends TestCase
{
    public function test_octane_service_provider_is_registered(): void
    {
        $providers = $this->app->getLoadedProviders();
        $this->assertArrayHasKey(OctaneServiceProvider::class, $providers);
    }

    public function test_octane_config_is_loaded(): void
    {
        $this->assertNotNull(config('octane'));
        $this->assertEquals('roadrunner', config('octane.server'));
        $this->assertArrayHasKey('listeners', config('octane'));
    }

    public function test_roadrunner_binary_exists(): void
    {
        $binary = base_path('rr.exe');
        if (! file_exists($binary)) {
            $this->markTestSkipped('RoadRunner binary (rr.exe) not installed on this machine.');
        }

        $this->assertFileExists($binary);
    }
}
