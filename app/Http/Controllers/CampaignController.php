<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCampaignRequest;
use App\Http\Requests\UpdateCampaignRequest;
use App\Models\Campaign;
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

        DB::transaction(function () use ($validated) {
            [$allCountries, $geos] = $this->resolveGeoTargetsForSave($validated);

            $campaign = Campaign::create([
                'name' => $validated['name'],
                'integration_mode' => $validated['integration_mode'],
                'target_mode' => $validated['target_mode'],
                'target_redirect_url' => $validated['target_redirect_url'] ?? null,
                'target_content_file' => $validated['target_content_file'] ?? null,
                'bot_content_file' => $validated['bot_content_file'] ?? null,
                'all_countries' => $allCountries,
                'is_active' => $validated['is_active'],
                'settings_json' => $validated['settings_json'] ?? [],
            ]);

            $tags = collect($validated['tags'] ?? [])
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

        DB::transaction(function () use ($campaign, $validated) {
            [$allCountries, $geos] = $this->resolveGeoTargetsForSave($validated);

            $campaign->update([
                'name' => $validated['name'],
                'integration_mode' => $validated['integration_mode'],
                'target_mode' => $validated['target_mode'],
                'target_redirect_url' => $validated['target_redirect_url'] ?? null,
                'target_content_file' => $validated['target_content_file'] ?? null,
                'bot_content_file' => $validated['bot_content_file'] ?? null,
                'all_countries' => $allCountries,
                'is_active' => $validated['is_active'],
                'settings_json' => $validated['settings_json'] ?? [],
            ]);

            $tags = collect($validated['tags'] ?? [])
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
}
