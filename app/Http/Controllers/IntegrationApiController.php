<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IntegrationApiController extends Controller
{
    public function precheck(Request $request): Response
    {
        return Inertia::render('Api/Precheck', [
            'endpoint' => route('api.precheck'),
            'tokenHint' => $this->tokenHint(),
            'requiredKeys' => ['request_id', 'session_id', 'tenant_id', 'project_id', 'campaign_id', 'integration_mode'],
        ]);
    }

    public function deviceFingerprints(Request $request): Response
    {
        return Inertia::render('Api/DeviceFingerprints', [
            'endpoint' => route('api.collect.device'),
            'tokenHint' => $this->tokenHint(),
            'requiredKeys' => ['request_id', 'session_id', 'tenant_id', 'project_id', 'campaign_id', 'integration_mode'],
        ]);
    }

    private function tokenHint(): string
    {
        $token = (string) config('services.ingest.token');
        if ($token === '') {
            return 'INGEST_API_TOKEN is not configured.';
        }

        if (strlen($token) <= 8) {
            return str_repeat('*', strlen($token));
        }

        return substr($token, 0, 4) . str_repeat('*', max(strlen($token) - 8, 0)) . substr($token, -4);
    }
}
