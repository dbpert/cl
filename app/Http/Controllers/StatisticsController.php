<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\DeviceEvent;
use App\Models\PrecheckEvent;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StatisticsController extends Controller
{
    public function show(Request $request, Campaign $campaign): Response
    {
        $campaignId = $campaign->id;
        $verdict = $request->string('verdict')->value() ?: null;
        $source = $request->string('source')->value() ?: null;
        $dateFrom = $request->string('date_from')->value() ?: now()->subDays(7)->toDateString();
        $dateTo = $request->string('date_to')->value() ?: now()->toDateString();

        $precheckQuery = PrecheckEvent::query()
            ->when($campaignId, fn ($q) => $q->where('campaign_id', $campaignId))
            ->when($verdict, fn ($q) => $q->where('verdict', $verdict))
            ->when($source, fn ($q) => $q->where('traffic_context_json->source', $source))
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo);

        $precheckTotal = (clone $precheckQuery)->count();
        $verdictBreakdown = (clone $precheckQuery)
            ->selectRaw('verdict, count(*) as total')
            ->groupBy('verdict')
            ->pluck('total', 'verdict');

        $recent = (clone $precheckQuery)
            ->latest()
            ->limit(50)
            ->get([
                'id',
                'campaign_id',
                'session_id',
                'ip',
                'host',
                'path',
                'risk_score',
                'verdict',
                'reason_codes_json',
                'created_at',
            ]);

        $deviceTotal = DeviceEvent::query()
            ->when($campaignId, fn ($q) => $q->where('campaign_id', $campaignId))
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->count();

        $reasonCounts = [];
        foreach ((clone $precheckQuery)->limit(500)->get(['reason_codes_json']) as $row) {
            foreach (($row->reason_codes_json ?? []) as $code) {
                if (!is_string($code) || $code === '') {
                    continue;
                }
                $reasonCounts[$code] = ($reasonCounts[$code] ?? 0) + 1;
            }
        }
        arsort($reasonCounts);

        $sourceOptions = PrecheckEvent::query()
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->limit(1000)
            ->get(['traffic_context_json'])
            ->map(fn (PrecheckEvent $event) => data_get($event->traffic_context_json, 'source'))
            ->filter(fn ($value) => is_string($value) && $value !== '')
            ->unique()
            ->values();

        return Inertia::render('Statistics/Index', [
            'filters' => [
                'campaign_id' => $campaignId,
                'verdict' => $verdict,
                'source' => $source,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'campaign' => [
                'id' => $campaign->id,
                'name' => $campaign->name,
            ],
            'campaignOptions' => Campaign::query()->orderBy('name')->get(['id', 'name']),
            'sourceOptions' => $sourceOptions,
            'metrics' => [
                'precheck_total' => $precheckTotal,
                'device_total' => $deviceTotal,
                'allow' => (int) ($verdictBreakdown['allow'] ?? 0),
                'soft' => (int) ($verdictBreakdown['soft'] ?? 0),
                'hard' => (int) ($verdictBreakdown['hard'] ?? 0),
            ],
            'topReasonCodes' => array_slice($reasonCounts, 0, 10, true),
            'recentEvents' => $recent,
        ]);
    }
}
