<?php

namespace App\Http\Middleware;

use App\Models\Campaign;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class VerifyIngestToken
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $header = trim((string) $request->header('Authorization', ''));
        $provided = '';
        if (Str::startsWith(Str::lower($header), 'bearer ')) {
            $provided = trim(substr($header, 7));
        }
        if ($provided === '') {
            $provided = trim((string) $request->header('X-Ingest-Token', ''));
        }

        if ($provided === '') {
            return response()->json(['ok' => false, 'error' => 'invalid_ingest_token'], 401);
        }

        $campaignId = (int) ($request->input('campaign_id')
            ?? data_get($request->input('traffic_context'), 'campaign_id')
            ?? data_get($request->input('fingerprint'), 'campaign_id')
            ?? 0);

        if ($campaignId <= 0) {
            return response()->json(['ok' => false, 'error' => 'campaign_id_required_for_token_validation'], 422);
        }

        $campaign = Campaign::query()->find($campaignId);
        if (!$campaign) {
            return response()->json(['ok' => false, 'error' => 'campaign_not_found'], 404);
        }

        $expected = trim((string) ($campaign->ingest_bearer_token ?? ''));
        if ($expected === '' || !hash_equals($expected, $provided)) {
            return response()->json(['ok' => false, 'error' => 'invalid_ingest_token'], 401);
        }

        return $next($request);
    }
}
