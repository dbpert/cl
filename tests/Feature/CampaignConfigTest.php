<?php

use App\Models\Campaign;
use App\Models\User;

test('authenticated user can create front controller campaign with tags and geos', function () {
    $user = User::factory()->create();

    $payload = [
        'name' => 'FC Campaign',
        'integration_mode' => 'front_controller',
        'target_mode' => 'redirect',
        'target_redirect_url' => 'https://target.example.com/landing',
        'bot_content_file' => 'white.php',
        'all_countries' => false,
        'is_active' => true,
        'tags' => ['team-a', 'active'],
        'target_geos' => [
            ['country_code' => 'US', 'country_name' => 'United States'],
            ['country_code' => 'CA', 'country_name' => 'Canada'],
        ],
    ];

    $this->actingAs($user)
        ->post(route('campaigns.store'), $payload)
        ->assertRedirect(route('campaigns.index'));

    $campaign = Campaign::query()->where('name', 'FC Campaign')->first();
    expect($campaign)->not()->toBeNull();
    expect($campaign->tags()->count())->toBe(2);
    expect($campaign->geoTargets()->count())->toBe(2);
});

test('campaign validation requires bot content file for front controller', function () {
    $user = User::factory()->create();

    $payload = [
        'name' => 'Broken FC Campaign',
        'integration_mode' => 'front_controller',
        'target_mode' => 'redirect',
        'target_redirect_url' => 'https://target.example.com/landing',
        'all_countries' => false,
        'is_active' => true,
    ];

    $this->actingAs($user)
        ->from(route('campaigns.create'))
        ->post(route('campaigns.store'), $payload)
        ->assertRedirect(route('campaigns.create'))
        ->assertSessionHasErrors(['bot_content_file']);
});

test('campaign validation requires target content file in content mode', function () {
    $user = User::factory()->create();

    $payload = [
        'name' => 'Broken Content Campaign',
        'integration_mode' => 'reverse_integration',
        'target_mode' => 'content',
        'all_countries' => false,
        'is_active' => true,
    ];

    $this->actingAs($user)
        ->from(route('campaigns.create'))
        ->post(route('campaigns.store'), $payload)
        ->assertRedirect(route('campaigns.create'))
        ->assertSessionHasErrors(['target_content_file']);
});
