import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import ActionIconButton from '@/Components/ActionIconButton';
import { BarsChartIcon, DownloadIcon, PencilIcon, TrashIcon } from '@/Components/ActionIcons';
import { Head, Link, router } from '@inertiajs/react';

export default function Index({ campaigns }) {
    const handleDeleteCampaign = (campaign) => {
        const confirmed = window.confirm(`Delete campaign "${campaign.name}"? This action cannot be undone.`);
        if (!confirmed) {
            return;
        }

        router.delete(route('campaigns.destroy', campaign.id));
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Campaigns</h2>}
        >
            <Head title="Campaigns" />

            <div className="py-8">
                <div className="sm:px-6 lg:px-8">
                    <div className="mb-4 flex justify-end">
                        <Link
                            href={route('campaigns.create')}
                            className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white"
                        >
                            New Campaign
                        </Link>
                    </div>
                    <div className="rounded-lg shadow overflow-hidden bg-white">
                        <table className="min-w-full divide-y divide-gray-200 text-sm">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-4 py-3 text-left">Name</th>
                                    <th className="px-4 py-3 text-left">Integration</th>
                                    <th className="px-4 py-3 text-left">Target Mode</th>
                                    <th className="px-4 py-3 text-left">Tags</th>
                                    <th className="px-4 py-3 text-left">Geos</th>
                                    <th className="px-4 py-3 text-left">Active</th>
                                    <th className="px-4 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {campaigns.data.map((campaign) => (
                                    <tr key={campaign.id}>
                                        <td className="px-4 py-3">{campaign.name}</td>
                                        <td className="px-4 py-3">{campaign.integration_mode}</td>
                                        <td className="px-4 py-3">{campaign.target_mode}</td>
                                        <td className="px-4 py-3">{campaign.tags_count}</td>
                                        <td className="px-4 py-3">{campaign.geo_targets_count}</td>
                                        <td className="px-4 py-3">{campaign.is_active ? 'Yes' : 'No'}</td>
                                        <td className="px-4 py-3">
                                            <div className="flex justify-end gap-2">
                                                <ActionIconButton
                                                    href={route('statistics.show', campaign.id)}
                                                    title="View campaign statistics"
                                                    variant="neutral"
                                                >
                                                    <BarsChartIcon />
                                                </ActionIconButton>
                                                <a
                                                    href={route('campaigns.download-client', campaign.id)}
                                                    title="Download integration index.php"
                                                    aria-label="Download integration index.php"
                                                    className="inline-flex h-9 w-9 items-center justify-center rounded-md border border-gray-200 text-gray-600 transition hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-300"
                                                >
                                                    <span className="h-5 w-5">
                                                        <DownloadIcon />
                                                    </span>
                                                </a>
                                                <ActionIconButton
                                                    href={route('campaigns.edit', campaign.id)}
                                                    title="Edit campaign"
                                                    variant="primary"
                                                >
                                                    <PencilIcon />
                                                </ActionIconButton>
                                                <button
                                                    type="button"
                                                    title="Delete campaign"
                                                    aria-label="Delete campaign"
                                                    onClick={() => handleDeleteCampaign(campaign)}
                                                    className="inline-flex h-9 w-9 items-center justify-center rounded-md border border-red-200 text-red-600 transition hover:bg-red-100 hover:text-red-700 focus:outline-none focus:ring-2 focus:ring-indigo-300"
                                                >
                                                    <span className="h-5 w-5">
                                                        <TrashIcon />
                                                    </span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                                {campaigns.data.length === 0 && (
                                    <tr>
                                        <td className="px-4 py-6 text-center text-gray-500" colSpan={7}>
                                            No campaigns yet
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
