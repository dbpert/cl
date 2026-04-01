import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import ActionIconButton from '@/Components/ActionIconButton';
import { BarsChartIcon, PencilIcon, TrashIcon } from '@/Components/ActionIcons';
import { Head, Link } from '@inertiajs/react';

export default function Index({ campaigns }) {
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
                                                <ActionIconButton
                                                    href={route('campaigns.edit', campaign.id)}
                                                    title="Edit campaign"
                                                    variant="primary"
                                                >
                                                    <PencilIcon />
                                                </ActionIconButton>
                                                <ActionIconButton
                                                href={route('campaigns.destroy', campaign.id)}
                                                method="delete"
                                                as="button"
                                                    title="Delete campaign"
                                                    variant="danger"
                                            >
                                                    <TrashIcon />
                                                </ActionIconButton>
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
