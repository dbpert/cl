<?php

use App\Models\Campaign;
use App\Models\DeviceEvent;
use App\Models\PrecheckEvent;

test('ingest endpoints reject invalid token', function () {
    config()->set('services.ingest.token', 'secret-token');

    $this->postJson('/api/precheck', [])->assertStatus(401);
    $this->postJson('/api/collect/device', [])->assertStatus(401);
});

test('precheck stores event with valid token', function () {
    config()->set('services.ingest.token', 'secret-token');

    $campaign = Campaign::query()->create([
        'name' => 'Test campaign',
        'integration_mode' => 'front_controller',
        'target_mode' => 'redirect',
        'target_redirect_url' => 'https://example.com/target',
        'all_countries' => false,
        'is_active' => true,
    ]);

    $payload = [
        'request_id' => 'req-1',
        'session_id' => 'sess-1',
        'campaign_id' => $campaign->id,
        'integration_mode' => 'php_include',
        'server_context' => [
            'ip' => '1.1.1.1',
            'path' => '/landing',
            'query' => 'a=1',
            'user_agent' => 'Mozilla/5.0',
            'accept_language' => 'en-US',
        ],
        'client_context' => [
            'webdriver' => false,
        ],
        'traffic_context' => [
            'source' => 'tiktok',
        ],
    ];

    $this->withHeaders(['X-Ingest-Token' => 'secret-token'])
        ->postJson('/api/precheck', $payload)
        ->assertOk()
        ->assertJsonPath('ok', true)
        ->assertJsonPath('decision.verdict', 'allow');

    expect(PrecheckEvent::query()->count())->toBe(1);
});

test('device collect stores event with valid token', function () {
    config()->set('services.ingest.token', 'secret-token');

    $campaign = Campaign::query()->create([
        'name' => 'Device campaign',
        'integration_mode' => 'reverse_integration',
        'target_mode' => 'content',
        'target_content_file' => 'target.php',
        'all_countries' => false,
        'is_active' => true,
    ]);

    $payload = [
        'campaign_id' => $campaign->id,
        'session_id' => 'sess-device-1',
        'agua' => 'Mozilla/5.0',
        'fingerprint' => [
            'canvas' => 'abc',
            'webgl' => 'def',
        ],
    ];

    $this->withToken('secret-token')
        ->postJson('/api/collect/device', $payload)
        ->assertOk()
        ->assertJsonPath('ok', true);

    expect(DeviceEvent::query()->count())->toBe(1);
});
