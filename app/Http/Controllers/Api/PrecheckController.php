<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\PrecheckEvent;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class PrecheckController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'request_id' => ['nullable', 'string', 'max:100'],
            'session_id' => ['nullable', 'string', 'max:255'],
            'campaign_id' => ['nullable', 'integer'],
            'tenant_id' => ['nullable', 'string', 'max:255'],
            'project_id' => ['nullable', 'string', 'max:255'],
            'integration_mode' => ['nullable', 'string', 'max:50'],
            'server_context' => ['nullable', 'array'],
            'client_context' => ['nullable', 'array'],
            'traffic_context' => ['nullable', 'array'],
        ]);

        $server = $data['server_context'] ?? [];
        $client = $data['client_context'] ?? [];
        $traffic = $data['traffic_context'] ?? [];

        $ip = (string) ($server['ip'] ?? '');
        $host = (string) ($server['host'] ?? '');
        $path = (string) ($server['path'] ?? '/');
        $query = (string) ($server['query'] ?? '');
        $ua = (string) ($server['user_agent'] ?? $client['ua'] ?? '');
        $acceptLanguage = (string) ($server['accept_language'] ?? '');
        $integrationMode = (string) ($data['integration_mode'] ?? 'php_include');
        $webdriver = (bool) ($client['webdriver'] ?? false);

        $reasonCodes = [];
        $score = 0;

        if ($webdriver) {
            $reasonCodes[] = 'webdriver_enabled';
            $score += 45;
        }

        if ($ua !== '') {
            $uaBad = ['bot', 'headless', 'crawler', 'spider'];
            foreach ($uaBad as $needle) {
                if (Str::contains(Str::lower($ua), $needle)) {
                    $reasonCodes[] = 'ua_bot_signature';
                    $score += 55;
                    break;
                }
            }
        }

        if (in_array($integrationMode, ['fetch_endpoint', 'unknown'], true)) {
            $reasonCodes[] = 'low_trust_mode';
            $score += 8;
            $trustLevel = 'medium';
        } else {
            $trustLevel = 'high';
        }

        $verdict = 'allow';
        if ($score >= 70) {
            $verdict = 'hard';
        } elseif ($score >= 30) {
            $verdict = 'soft';
        }

        $campaignId = $data['campaign_id']
            ?? $data['project_id']
            ?? data_get($traffic, 'campaign_id')
            ?? data_get($server, 'campaign_id')
            ?? null;
        $campaign = $campaignId ? Campaign::query()->find($campaignId) : null;
        $event = PrecheckEvent::query()->create([
            'campaign_id' => $campaign?->id,
            'session_id' => (string) ($data['session_id'] ?? ''),
            'request_id' => (string) ($data['request_id'] ?? Str::uuid()),
            'integration_mode' => $integrationMode,
            'ip' => $ip,
            'host' => $host,
            'path' => $path,
            'query' => $query,
            'ua' => $ua,
            'accept_language' => $acceptLanguage,
            'risk_score' => $score,
            'verdict' => $verdict,
            'reason_codes_json' => $reasonCodes,
            'server_context_json' => $server,
            'client_context_json' => $client,
            'traffic_context_json' => $traffic,
        ]);

        return response()->json([
            'ok' => true,
            'request_id' => $event->request_id,
            'session_id' => $event->session_id,
            'decision' => [
                'verdict' => $verdict,
                'risk_score' => $score,
                'reason_codes' => $reasonCodes,
                'trust_level' => $trustLevel,
                'policy_ttl_ms' => 30000,
                'config_version' => now()->format('Ymd') . '.1',
            ],
            'next_step' => [
                'collect_behavior' => $verdict !== 'hard',
                'challenge_required' => $verdict === 'soft',
            ],
            'action' => [
                'blocked_action' => 'white_page',
                'target_for_blacklisted' => $campaign?->target_redirect_url ?? '',
                'white_page_file' => $campaign?->bot_content_file
                    ?: data_get($campaign?->settings_json, 'white_page_file', 'white_page.html'),
            ],
            'timing' => [
                'processed_ms' => 0,
            ],
        ]);
    }
}
