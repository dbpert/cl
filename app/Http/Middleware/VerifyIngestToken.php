<?php

namespace App\Http\Middleware;

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
        $expected = trim((string) config('services.ingest.token'));
        if ($expected === '') {
            return response()->json(['ok' => false, 'error' => 'ingest_token_not_configured'], 500);
        }

        $header = trim((string) $request->header('Authorization', ''));
        $provided = '';
        if (Str::startsWith(Str::lower($header), 'bearer ')) {
            $provided = trim(substr($header, 7));
        }
        if ($provided === '') {
            $provided = trim((string) $request->header('X-Ingest-Token', ''));
        }

        if ($provided === '' || !hash_equals($expected, $provided)) {
            return response()->json(['ok' => false, 'error' => 'invalid_ingest_token'], 401);
        }

        return $next($request);
    }
}
