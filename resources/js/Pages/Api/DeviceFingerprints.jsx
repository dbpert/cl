import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

export default function DeviceFingerprints({ endpoint, tokenHint, requiredKeys }) {
    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Device Fingerprints API</h2>}
        >
            <Head title="Device Fingerprints API" />

            <div className="py-6">
                <div className="mx-auto max-w-5xl space-y-4 sm:px-6 lg:px-8">
                    <div className="rounded-lg bg-white p-6 shadow">
                        <h3 className="text-lg font-semibold text-gray-900">Endpoint</h3>
                        <p className="mt-2 text-sm text-gray-600">POST endpoint для приема fingerprint событий устройств.</p>
                        <code className="mt-3 block rounded bg-gray-100 p-3 text-sm text-gray-800">{endpoint}</code>
                    </div>

                    <div className="rounded-lg bg-white p-6 shadow">
                        <h3 className="text-lg font-semibold text-gray-900">Authorization</h3>
                        <p className="mt-2 text-sm text-gray-600">
                            Используйте заголовок <code>Authorization: Bearer {'{INGEST_API_TOKEN}'}</code>.
                        </p>
                        <p className="mt-2 text-sm text-gray-500">Current token hint: {tokenHint}</p>
                    </div>

                    <div className="rounded-lg bg-white p-6 shadow">
                        <h3 className="text-lg font-semibold text-gray-900">Required context keys</h3>
                        <ul className="mt-2 list-disc pl-5 text-sm text-gray-700">
                            {requiredKeys.map((key) => (
                                <li key={key}>
                                    <code>{key}</code>
                                </li>
                            ))}
                        </ul>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
