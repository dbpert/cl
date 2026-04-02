import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import CampaignFormFields from '@/Pages/Campaigns/Partials/CampaignFormFields';
import { Head, useForm } from '@inertiajs/react';

export default function Create({ countryOptions, tagPresets }) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        integration_mode: 'front_controller',
        precheck_integration_mode: 'php_include',
        soft_mode: 'challenge',
        target_mode: 'redirect',
        target_redirect_url: '',
        target_content_file: '',
        bot_content_file: '',
        all_countries: false,
        is_active: true,
        settings_json: {},
        tags: [],
        target_geos: [],
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('campaigns.store'));
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">New Campaign</h2>}
        >
            <Head title="Create Campaign" />
            <div className="py-8">
                <div className="mx-auto max-w-3xl sm:px-6 lg:px-8">
                    <div className="rounded-lg bg-white p-6 shadow">
                        <form onSubmit={submit}>
                            <CampaignFormFields
                                data={data}
                                setData={setData}
                                errors={errors}
                                processing={processing}
                                submitLabel="Create"
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
