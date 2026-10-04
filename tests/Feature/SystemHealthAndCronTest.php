<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class SystemHealthAndCronTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint_returns_ok_status(): void
    {
        $response = $this->get('/health');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'timestamp',
            'checks' => [
                'database',
                'cache',
                'queue',
                'last_cron_run',
            ],
        ]);
    }

    public function test_cron_endpoint_rejects_unauthorized_token(): void
    {
        Config::set('app.cron_secret', 'secret-cron-token-12345');

        $response = $this->get('/internal/cron?token=wrong-token');
        $response->assertStatus(403);
    }

    public function test_cron_endpoint_executes_with_valid_token(): void
    {
        Config::set('app.cron_secret', 'secret-cron-token-12345');

        $response = $this->withHeader('X-Cron-Token', 'secret-cron-token-12345')
            ->get('/internal/cron');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
        ]);
    }
}
