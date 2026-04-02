<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCampaignRequest;
use App\Http\Requests\UpdateCampaignRequest;
use App\Models\Campaign;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CampaignController extends Controller
{
    public function index(): Response
    {
        $campaigns = Campaign::query()
            ->withCount(['tags', 'geoTargets'])
            ->latest()
            ->paginate(20)
            ->through(fn (Campaign $campaign) => [
                'id' => $campaign->id,
                'name' => $campaign->name,
                'integration_mode' => $campaign->integration_mode,
                'precheck_integration_mode' => $campaign->precheck_integration_mode,
                'soft_mode' => $campaign->soft_mode,
                'target_mode' => $campaign->target_mode,
                'tags_count' => $campaign->tags_count,
                'geo_targets_count' => $campaign->geo_targets_count,
                'is_active' => $campaign->is_active,
                'created_at' => optional($campaign->created_at)->toDateTimeString(),
            ]);

        return Inertia::render('Campaigns/Index', [
            'campaigns' => $campaigns,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Campaigns/Create', [
            'countryOptions' => $this->countryOptions(),
            'tagPresets' => $this->tagPresets(),
        ]);
    }

    public function store(StoreCampaignRequest $request)
    {
        $validated = $request->validated();
        $normalized = $this->normalizeCampaignConfigInput($validated);

        DB::transaction(function () use ($normalized) {
            [$allCountries, $geos] = $this->resolveGeoTargetsForSave($normalized);

            $campaign = Campaign::create([
                'name' => $normalized['name'],
                'integration_mode' => $normalized['integration_mode'],
                'precheck_integration_mode' => $normalized['precheck_integration_mode'],
                'soft_mode' => $normalized['soft_mode'],
                'ingest_bearer_token' => $this->generateUniqueCampaignBearerToken(),
                'target_mode' => $normalized['target_mode'],
                'target_redirect_url' => $normalized['target_redirect_url'],
                'target_content_file' => $normalized['target_content_file'],
                'bot_content_file' => $normalized['bot_content_file'],
                'all_countries' => $allCountries,
                'is_active' => $normalized['is_active'],
                'settings_json' => $normalized['settings_json'] ?? [],
            ]);

            $tags = collect($normalized['tags'] ?? [])
                ->filter(fn ($tag) => is_string($tag) && trim($tag) !== '')
                ->map(fn ($tag) => ['tag' => trim($tag)])
                ->unique('tag')
                ->values()
                ->all();
            if ($tags !== []) {
                $campaign->tags()->createMany($tags);
            }

            if (!$allCountries && $geos !== []) {
                $campaign->geoTargets()->createMany($geos);
            }
        });

        return redirect()->route('campaigns.index')->with('success', 'Campaign created.');
    }

    public function edit(Campaign $campaign): Response
    {
        $campaign->load(['tags', 'geoTargets']);

        return Inertia::render('Campaigns/Edit', [
            'campaign' => [
                ...$campaign->toArray(),
                'all_countries' => (bool) $campaign->all_countries,
                'tags' => $campaign->tags->pluck('tag')->values()->all(),
                'target_geos' => $campaign->geoTargets->map(fn ($geo) => [
                    'country_code' => $geo->country_code,
                    'country_name' => $geo->country_name,
                ])->values()->all(),
            ],
            'countryOptions' => $this->countryOptions(),
            'tagPresets' => $this->tagPresets(),
        ]);
    }

    public function update(UpdateCampaignRequest $request, Campaign $campaign)
    {
        $validated = $request->validated();
        $normalized = $this->normalizeCampaignConfigInput($validated);

        DB::transaction(function () use ($campaign, $normalized) {
            [$allCountries, $geos] = $this->resolveGeoTargetsForSave($normalized);

            $campaign->update([
                'name' => $normalized['name'],
                'integration_mode' => $normalized['integration_mode'],
                'precheck_integration_mode' => $normalized['precheck_integration_mode'],
                'soft_mode' => $normalized['soft_mode'],
                'target_mode' => $normalized['target_mode'],
                'target_redirect_url' => $normalized['target_redirect_url'],
                'target_content_file' => $normalized['target_content_file'],
                'bot_content_file' => $normalized['bot_content_file'],
                'all_countries' => $allCountries,
                'is_active' => $normalized['is_active'],
                'settings_json' => $normalized['settings_json'] ?? [],
            ]);

            $tags = collect($normalized['tags'] ?? [])
                ->filter(fn ($tag) => is_string($tag) && trim($tag) !== '')
                ->map(fn ($tag) => ['tag' => trim($tag)])
                ->unique('tag')
                ->values()
                ->all();
            $campaign->tags()->delete();
            if ($tags !== []) {
                $campaign->tags()->createMany($tags);
            }

            $campaign->geoTargets()->delete();
            if (!$allCountries && $geos !== []) {
                $campaign->geoTargets()->createMany($geos);
            }
        });

        return redirect()->route('campaigns.index')->with('success', 'Campaign updated.');
    }

    public function destroy(Campaign $campaign)
    {
        $campaign->delete();
        return redirect()->route('campaigns.index')->with('success', 'Campaign deleted.');
    }

    public function downloadClient(Campaign $campaign): HttpResponse
    {
        if (!is_string($campaign->ingest_bearer_token) || trim($campaign->ingest_bearer_token) === '') {
            $campaign->ingest_bearer_token = $this->generateUniqueCampaignBearerToken();
            $campaign->save();
        }

        $content = $this->buildIntegrationClientContent($campaign);
        $filename = 'index.php';

        return response($content, 200, [
            'Content-Type' => 'application/x-httpd-php; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * @return array{0: bool, 1: array<int, array{country_code: string, country_name: string}>}
     */
    private function resolveGeoTargetsForSave(array $validated): array
    {
        $allCountries = (bool) ($validated['all_countries'] ?? false);

        $mapped = collect($validated['target_geos'] ?? [])
            ->filter(fn ($geo) => is_array($geo) && !empty($geo['country_code']))
            ->map(fn ($geo) => [
                'country_code' => strtoupper((string) $geo['country_code']),
                'country_name' => isset($geo['country_name']) ? (string) $geo['country_name'] : '',
            ]);

        if ($mapped->contains(fn ($g) => $g['country_code'] === 'ALL')) {
            $allCountries = true;
        }

        $geos = $mapped
            ->filter(fn ($g) => $g['country_code'] !== 'ALL')
            ->filter(fn ($g) => $g['country_name'] !== '')
            ->filter(fn ($g) => preg_match('/^[A-Z]{2}$/', $g['country_code']) === 1)
            ->unique('country_code')
            ->values()
            ->all();

        return [$allCountries, $geos];
    }

    private function tagPresets(): array
    {
        return [
            'self-registration',
            'purchased',
            'warming-up',
            'active',
            'vertical',
            'traffic-source',
            'team-a',
            'team-b',
        ];
    }

    private function countryOptions(): array
    {
        $path = base_path('country_list.csv');
        if (!is_file($path)) {
            return [];
        }

        $rows = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($rows === false || count($rows) <= 1) {
            return [];
        }

        $result = [];
        foreach (array_slice($rows, 1) as $line) {
            $columns = str_getcsv($line);
            if (count($columns) < 2) {
                continue;
            }
            $name = trim((string) $columns[0]);
            $code = strtoupper(trim((string) $columns[1]));
            if ($name === '' || strlen($code) !== 2) {
                continue;
            }
            $result[] = [
                'country_code' => $code,
                'country_name' => $name,
            ];
        }

        return $result;
    }

    private function normalizeCampaignConfigInput(array $validated): array
    {
        $integrationMode = (string) ($validated['integration_mode'] ?? 'front_controller');
        $targetMode = (string) ($validated['target_mode'] ?? 'redirect');
        if ($integrationMode === 'block_integration') {
            $targetMode = 'redirect';
        }

        return [
            ...$validated,
            'precheck_integration_mode' => (string) ($validated['precheck_integration_mode'] ?? 'php_include'),
            'soft_mode' => (string) ($validated['soft_mode'] ?? 'challenge'),
            'target_mode' => $targetMode,
            'target_redirect_url' => $targetMode === 'redirect' && $integrationMode !== 'block_integration'
                ? ($validated['target_redirect_url'] ?? null)
                : null,
            'target_content_file' => $targetMode === 'content' && $integrationMode !== 'block_integration'
                ? ($validated['target_content_file'] ?? null)
                : null,
            'bot_content_file' => $integrationMode === 'front_controller'
                ? ($validated['bot_content_file'] ?? null)
                : null,
        ];
    }

    private function buildIntegrationClientContent(Campaign $campaign): string
    {
        $stub = file_get_contents(resource_path('stubs/integration_index.php.stub'));
        if (!is_string($stub) || $stub === '') {
            abort(500, 'Integration client stub not found.');
        }

        $apiBaseUrl = (string) config('app.api_url');
        if (trim($apiBaseUrl) === '') {
            $apiBaseUrl = (string) config('app.url');
        }
        $precheckApiUrl = rtrim($apiBaseUrl, '/') . '/api/precheck';
        $deviceCollectUrl = rtrim($apiBaseUrl, '/') . '/api/collect/device';
        $ingestToken = (string) ($campaign->ingest_bearer_token ?? '');

        $settings = is_array($campaign->settings_json ?? null) ? $campaign->settings_json : [];
        $tenantId = (string) ($settings['tenant_id'] ?? 'tenant-' . $campaign->id);
        $projectId = (string) ($settings['project_id'] ?? 'project-' . $campaign->id);

        $replacements = [
            '__PRECHECK_API_URL__' => addslashes($precheckApiUrl),
            '__PRECHECK_TOKEN__' => addslashes($ingestToken),
            '__TENANT_ID__' => addslashes($tenantId),
            '__PROJECT_ID__' => addslashes($projectId),
            '__CAMPAIGN_ID__' => (string) $campaign->id,
            '__RUNTIME_INTEGRATION_MODE__' => addslashes((string) $campaign->integration_mode),
            '__PRECHECK_INTEGRATION_MODE__' => addslashes((string) $campaign->precheck_integration_mode),
            '__SOFT_MODE__' => addslashes((string) $campaign->soft_mode),
            '__FINGERPRINT_ENDPOINT__' => addslashes($deviceCollectUrl),
            '__BEHAVIOR_ENDPOINT__' => addslashes('/collector.php'),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $stub);
    }

    private function generateUniqueCampaignBearerToken(): string
    {
        do {
            $token = 'cmp_' . rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        } while (Campaign::query()->where('ingest_bearer_token', $token)->exists());

        return $token;
    }
}
