<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\DeviceEvent;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DeviceCollectController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'campaign_id' => ['nullable', 'integer'],
            'request_id' => ['nullable', 'string', 'max:100'],
            'tenant_id' => ['nullable', 'string', 'max:255'],
            'project_id' => ['nullable', 'string', 'max:255'],
            'integration_mode' => ['nullable', 'string', 'max:50'],
            'session_id' => ['nullable', 'string', 'max:255'],
            'agua' => ['nullable', 'string', 'max:255'],
            'referrer' => ['nullable', 'string'],
            'cl_referrer' => ['nullable', 'string'],
            'referal' => ['nullable', 'string', 'max:255'],
            'fingerprint' => ['nullable', 'array'],
        ]);

        $campaignId = $data['campaign_id']
            ?? data_get($data, 'fingerprint.campaign_id')
            ?? null;
        $campaign = $campaignId ? Campaign::query()->find($campaignId) : null;
        $integrationMode = (string) ($data['integration_mode'] ?? $campaign?->precheck_integration_mode ?? 'php_include');

        $event = DeviceEvent::query()->create([
            'campaign_id' => $campaign?->id,
            'request_id' => (string) ($data['request_id'] ?? ''),
            'tenant_id' => isset($data['tenant_id']) ? (string) $data['tenant_id'] : null,
            'project_id' => isset($data['project_id']) ? (string) $data['project_id'] : null,
            'integration_mode' => $integrationMode,
            'session_id' => (string) ($data['session_id'] ?? ''),
            'agua' => (string) ($data['agua'] ?? ''),
            'referrer' => (string) ($data['referrer'] ?? ''),
            'cl_referrer' => (string) ($data['cl_referrer'] ?? ''),
            'referal' => (string) ($data['referal'] ?? ''),
            'fingerprint_json' => $data['fingerprint'] ?? [],
        ]);

        return response()->json([
            'ok' => true,
            'id' => $event->id,
            'request_id' => $event->request_id,
            'session_id' => $event->session_id,
            'campaign_id' => $event->campaign_id,
            'tenant_id' => $event->tenant_id,
            'project_id' => $event->project_id,
        ]);
    }
}
