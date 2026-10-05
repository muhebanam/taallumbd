<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
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
            'app_env',
            'php_version',
            'checks' => [
                'database' => ['status'],
                'cache' => ['status', 'driver'],
                'storage' => ['status', 'disk'],
                'queue' => ['status'],
                'last_cron_run',
            ],
        ]);
        $response->assertJsonPath('status', 'healthy');
    }

    public function test_cron_endpoint_rejects_unauthorized_token(): void
    {
        Config::set('app.cron_secret', 'secret-cron-token-12345');

        // Missing token
        $response1 = $this->get('/internal/cron');
        $response1->assertStatus(403);

        // Wrong query token
        $response2 = $this->get('/internal/cron?token=wrong-token');
        $response2->assertStatus(403);

        // Wrong header token
        $response3 = $this->withHeader('X-Cron-Token', 'invalid-token-xyz')
            ->get('/internal/cron');
        $response3->assertStatus(403);
    }

    public function test_cron_endpoint_executes_with_valid_token_header(): void
    {
        Config::set('app.cron_secret', 'secret-cron-token-12345');

        $response = $this->withHeader('X-Cron-Token', 'secret-cron-token-12345')
            ->get('/internal/cron');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
        ]);

        $this->assertNotNull(Cache::get('internal:cron:last_run'));
    }

    public function test_cron_endpoint_executes_with_valid_token_query(): void
    {
        Config::set('app.cron_secret', 'secret-cron-token-12345');

        $response = $this->get('/internal/cron?token=secret-cron-token-12345');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
        ]);

        $this->assertNotNull(Cache::get('internal:cron:last_run'));
    }
}
