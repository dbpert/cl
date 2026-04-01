import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';

export default function StatisticsIndex({ campaign, filters, campaignOptions, sourceOptions, metrics, topReasonCodes, recentEvents }) {
    const onFilterChange = (key, value) => {
        if (key === 'campaign_id' && value) {
            router.get(
                route('statistics.show', value),
                { ...filters, campaign_id: Number(value) },
                { preserveState: true, replace: true },
            );
            return;
        }

        router.get(
            route('statistics.show', campaign.id),
            { ...filters, [key]: value },
            { preserveState: true, replace: true },
        );
    };

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Statistics</h2>}>
            <Head title="Statistics" />
            <div className="py-8">
                <div className="space-y-6 sm:px-6 lg:px-8">
                    <div className="rounded-lg bg-white p-4 shadow">
                        <div className="grid grid-cols-1 gap-3 md:grid-cols-5">
                            <select
                                className="rounded-md border-gray-300"
                                value={filters.campaign_id ?? ''}
                                onChange={(e) => onFilterChange('campaign_id', e.target.value || null)}
                            >
                                {campaignOptions.map((c) => (
                                    <option key={c.id} value={c.id}>{c.name}</option>
                                ))}
                            </select>
                            <select
                                className="rounded-md border-gray-300"
                                value={filters.verdict ?? ''}
                                onChange={(e) => onFilterChange('verdict', e.target.value || null)}
                            >
                                <option value="">All verdicts</option>
                                <option value="allow">allow</option>
                                <option value="soft">soft</option>
                                <option value="hard">hard</option>
                            </select>
                            <select
                                className="rounded-md border-gray-300"
                                value={filters.source ?? ''}
                                onChange={(e) => onFilterChange('source', e.target.value || null)}
                            >
                                <option value="">All sources</option>
                                {sourceOptions.map((source) => (
                                    <option key={source} value={source}>{source}</option>
                                ))}
                            </select>
                            <input
                                type="date"
                                className="rounded-md border-gray-300"
                                value={filters.date_from}
                                onChange={(e) => onFilterChange('date_from', e.target.value)}
                            />
                            <input
                                type="date"
                                className="rounded-md border-gray-300"
                                value={filters.date_to}
                                onChange={(e) => onFilterChange('date_to', e.target.value)}
                            />
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-4 md:grid-cols-5">
                        <StatCard label="Precheck total" value={metrics.precheck_total} />
                        <StatCard label="Device total" value={metrics.device_total} />
                        <StatCard label="Allow" value={metrics.allow} />
                        <StatCard label="Soft" value={metrics.soft} />
                        <StatCard label="Hard" value={metrics.hard} />
                    </div>

                    <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                        <div className="rounded-lg bg-white p-4 shadow lg:col-span-1">
                            <h3 className="mb-3 text-sm font-semibold uppercase text-gray-500">Top reason codes</h3>
                            <ul className="space-y-2 text-sm">
                                {Object.entries(topReasonCodes).map(([code, total]) => (
                                    <li key={code} className="flex items-center justify-between">
                                        <span>{code}</span>
                                        <span className="font-semibold">{total}</span>
                                    </li>
                                ))}
                                {Object.keys(topReasonCodes).length === 0 && <li className="text-gray-500">No data</li>}
                            </ul>
                        </div>
                        <div className="rounded-lg bg-white p-4 shadow lg:col-span-2">
                            <h3 className="mb-3 text-sm font-semibold uppercase text-gray-500">Recent precheck events</h3>
                            <div className="overflow-x-auto">
                                <table className="min-w-full text-sm">
                                    <thead>
                                        <tr className="text-left text-gray-500">
                                            <th className="py-2">Time</th>
                                            <th className="py-2">Campaign</th>
                                            <th className="py-2">Host/Path</th>
                                            <th className="py-2">Score</th>
                                            <th className="py-2">Verdict</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {recentEvents.map((event) => (
                                            <tr key={event.id} className="border-t">
                                                <td className="py-2">{event.created_at}</td>
                                                <td className="py-2">{event.campaign_id ?? '-'}</td>
                                                <td className="py-2">{event.host}{event.path}</td>
                                                <td className="py-2">{event.risk_score}</td>
                                                <td className="py-2">{event.verdict}</td>
                                            </tr>
                                        ))}
                                        {recentEvents.length === 0 && (
                                            <tr><td className="py-3 text-gray-500" colSpan={5}>No events</td></tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

function StatCard({ label, value }) {
    return (
        <div className="rounded-lg bg-white p-4 shadow">
            <div className="text-xs uppercase text-gray-500">{label}</div>
            <div className="mt-1 text-2xl font-semibold text-gray-900">{value}</div>
        </div>
    );
}
