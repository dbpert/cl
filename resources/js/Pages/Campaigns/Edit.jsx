import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import CampaignFormFields from '@/Pages/Campaigns/Partials/CampaignFormFields';
import { Head, useForm } from '@inertiajs/react';

export default function Edit({ campaign, countryOptions, tagPresets }) {
    const { data, setData, patch, processing, errors } = useForm({
        name: campaign.name ?? '',
        integration_mode: campaign.integration_mode ?? 'front_controller',
        target_mode: campaign.target_mode ?? 'redirect',
        target_redirect_url: campaign.target_redirect_url ?? '',
        target_content_file: campaign.target_content_file ?? '',
        bot_content_file: campaign.bot_content_file ?? '',
        all_countries: !!campaign.all_countries,
        is_active: !!campaign.is_active,
        settings_json: campaign.settings_json ?? {},
        tags: campaign.tags ?? [],
        target_geos: campaign.target_geos ?? [],
    });

    const submit = (e) => {
        e.preventDefault();
        patch(route('campaigns.update', campaign.id));
    };

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Edit Campaign</h2>}>
            <Head title="Edit Campaign" />
            <div className="py-8">
                <div className="mx-auto max-w-3xl sm:px-6 lg:px-8">
                    <div className="rounded-lg bg-white p-6 shadow">
                        <form onSubmit={submit}>
                            <CampaignFormFields
                                data={data}
                                setData={setData}
                                errors={errors}
                                processing={processing}
                                submitLabel="Save"
                                cancelHref={route('campaigns.index')}
                                countryOptions={countryOptions}
                                tagPresets={tagPresets}
                            />
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
