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

        $event = DeviceEvent::query()->create([
            'campaign_id' => $campaign?->id,
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
        ]);
    }
}
