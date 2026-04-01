import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import { Link } from '@inertiajs/react';
import { useMemo, useRef, useState } from 'react';

export default function CampaignFormFields({
    data,
    setData,
    errors,
    processing,
    submitLabel,
    cancelHref,
    countryOptions = [],
    tagPresets = [],
}) {
    const [customTag, setCustomTag] = useState('');
    const [geoSearch, setGeoSearch] = useState('');
    const [geoPickerOpen, setGeoPickerOpen] = useState(false);
    const geoBlurTimer = useRef(null);

    const countryByCode = useMemo(() => {
        const map = new Map();
        map.set('ALL', 'All Countries');
        countryOptions.forEach((item) => map.set(item.country_code, item.country_name));
        return map;
    }, [countryOptions]);

    const filteredCountryOptions = useMemo(() => {
        const q = geoSearch.trim().toLowerCase();
        const list = countryOptions.filter(
            (c) =>
                !q ||
                c.country_name.toLowerCase().includes(q) ||
                c.country_code.toLowerCase().includes(q),
        );
        const allMatches =
            !q ||
            'all countries'.toLowerCase().includes(q) ||
            'all'.startsWith(q);
        if (allMatches) {
            return [{ country_code: 'ALL', country_name: 'All Countries' }, ...list];
        }
        return list;
    }, [countryOptions, geoSearch]);

    const selectedGeoCodes = (data.target_geos ?? []).map((item) => item.country_code);
    const selectedTags = data.tags ?? [];
    const isBlockIntegration = data.integration_mode === 'block_integration';
    const isFrontController = data.integration_mode === 'front_controller';
    const allCountries = !!data.all_countries;

    const toggleGeoCode = (code) => {
        if (code === 'ALL') {
            if (allCountries) {
                setData((form) => ({ ...form, all_countries: false, target_geos: [] }));
            } else {
                setData((form) => ({ ...form, all_countries: true, target_geos: [] }));
            }
            return;
        }
        if (allCountries) {
            setData((form) => ({
                ...form,
                all_countries: false,
                target_geos: [{ country_code: code, country_name: countryByCode.get(code) ?? code }],
            }));
            return;
        }
        const current = data.target_geos ?? [];
        let next = current.filter((g) => g.country_code !== 'ALL');
        if (next.some((g) => g.country_code === code)) {
            next = next.filter((g) => g.country_code !== code);
        } else {
            next = [
                ...next,
                { country_code: code, country_name: countryByCode.get(code) ?? code },
            ];
        }
        setData('target_geos', next);
    };

    const isGeoRowChecked = (code) =>
        code === 'ALL' ? allCountries : !allCountries && selectedGeoCodes.includes(code);

    const selectedIsoDisplay = useMemo(() => {
        if (allCountries) {
            return 'ALL';
        }
        const codes = selectedGeoCodes;
        if (codes.length === 0) {
            return '';
        }
        return codes.join(', ');
    }, [allCountries, selectedGeoCodes]);

    const clearGeoBlurTimer = () => {
        if (geoBlurTimer.current) {
            clearTimeout(geoBlurTimer.current);
            geoBlurTimer.current = null;
        }
    };

    const scheduleGeoClose = () => {
        clearGeoBlurTimer();
        geoBlurTimer.current = setTimeout(() => setGeoPickerOpen(false), 180);
    };

    const handleGeoContainerBlur = (e) => {
        if (!e.currentTarget.contains(e.relatedTarget)) {
            scheduleGeoClose();
        }
    };

    const toggleTag = (tag) => {
        if (!tag) {
            return;
        }
        const exists = selectedTags.includes(tag);
        if (exists) {
            setData('tags', selectedTags.filter((value) => value !== tag));
            return;
        }
        setData('tags', [...selectedTags, tag]);
    };

    const addCustomTag = () => {
        const normalized = customTag.trim();
        if (!normalized || selectedTags.includes(normalized)) {
            return;
        }
        setData('tags', [...selectedTags, normalized]);
        setCustomTag('');
    };

    const removeTag = (tag) => {
        setData('tags', selectedTags.filter((value) => value !== tag));
    };

    return (
        <div className="space-y-4">
            <div>
                <InputLabel htmlFor="name" value="Name" />
                <TextInput id="name" className="mt-1 block w-full" value={data.name} onChange={(e) => setData('name', e.target.value)} />
                <InputError className="mt-2" message={errors.name} />
            </div>

            <div>
                <InputLabel htmlFor="integration_mode" value="Integration Mode" />
                <select
                    id="integration_mode"
                    className="mt-1 block w-full rounded-md border-gray-300"
                    value={data.integration_mode}
                    onChange={(e) => setData('integration_mode', e.target.value)}
                >
                    <option value="front_controller">Front Controller</option>
                    <option value="reverse_integration">Reverse Integration</option>
                    <option value="block_integration">Block Integration</option>
                </select>
                <InputError className="mt-2" message={errors.integration_mode} />
            </div>

            {!isBlockIntegration && (
                <div className="grid grid-cols-1 gap-3 md:grid-cols-2">
                    <div>
                        <InputLabel htmlFor="target_mode" value="Target Traffic Mode" />
                        <select
                            id="target_mode"
                            className="mt-1 block w-full rounded-md border-gray-300"
                            value={data.target_mode}
                            onChange={(e) => setData('target_mode', e.target.value)}
                        >
                            <option value="redirect">Redirect</option>
                            <option value="content">Content</option>
                        </select>
                        <InputError className="mt-2" message={errors.target_mode} />
                    </div>

                    <div>
                        {data.target_mode === 'redirect' ? (
                            <>
                                <InputLabel htmlFor="target_redirect_url" value="Target Redirect URL" />
                                <TextInput
                                    id="target_redirect_url"
                                    className="mt-1 block w-full"
                                    placeholder="https://target.example.com/landing"
                                    value={data.target_redirect_url}
                                    onChange={(e) => setData('target_redirect_url', e.target.value)}
                                />
                                <InputError className="mt-2" message={errors.target_redirect_url} />
                            </>
                        ) : (
                            <>
                                <InputLabel htmlFor="target_content_file" value="Target Content Filename" />
                                <TextInput
                                    id="target_content_file"
                                    className="mt-1 block w-full"
                                    placeholder="target_page_filename.php"
                                    value={data.target_content_file}
                                    onChange={(e) => setData('target_content_file', e.target.value)}
                                />
                                <InputError className="mt-2" message={errors.target_content_file} />
                            </>
                        )}
                    </div>
                </div>
            )}

            {isFrontController && (
                <div>
                    <InputLabel htmlFor="bot_content_file" value="Bot Content Filename" />
                    <TextInput
                        id="bot_content_file"
                        className="mt-1 block w-full"
                        placeholder="white_page_filename.php"
                        value={data.bot_content_file}
                        onChange={(e) => setData('bot_content_file', e.target.value)}
                    />
                    <InputError className="mt-2" message={errors.bot_content_file} />
                </div>
            )}

            <div
                className="rounded-md border border-gray-200 p-2"
                onBlur={handleGeoContainerBlur}
            >
                <InputLabel htmlFor="target_geos_search" value="Target Geolocation" />
                <p className="mt-0.5 text-xs text-gray-500">
                    Selected ISO2 codes appear on the left; type on the right to filter the list. &quot;All Countries&quot; is exclusive with specific countries.
                </p>
                <div
                    className="mt-1 flex min-h-[42px] overflow-hidden rounded-md border border-gray-300 bg-white shadow-sm focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500"
                    id="target_geos_search_wrap"
                >
                    <div
                        className="flex min-w-0 max-w-[min(50%,14rem)] flex-shrink-0 items-center border-r border-gray-200 bg-gray-50 px-2 py-2 text-xs leading-tight text-gray-900"
                        title={selectedIsoDisplay || 'No countries selected'}
                        aria-live="polite"
                    >
                        <span className="truncate font-mono">{selectedIsoDisplay || '—'}</span>
                    </div>
                    <input
                        id="target_geos_search"
                        type="search"
                        autoComplete="off"
                        placeholder="Filter countries…"
                        className="min-w-0 flex-1 border-0 px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:ring-0"
                        value={geoSearch}
                        onChange={(e) => setGeoSearch(e.target.value)}
                        onFocus={() => {
                            clearGeoBlurTimer();
                            setGeoPickerOpen(true);
                        }}
                    />
                </div>
                <div className={geoPickerOpen ? 'mt-2' : 'hidden'} aria-hidden={!geoPickerOpen}>
                    <div
                        role="listbox"
                        aria-multiselectable="true"
                        className="max-h-48 overflow-y-auto rounded-md border border-gray-300 bg-white"
                        tabIndex={-1}
                        onFocus={() => {
                            clearGeoBlurTimer();
                            setGeoPickerOpen(true);
                        }}
                    >
                        {filteredCountryOptions.length === 0 ? (
                            <div className="px-3 py-4 text-center text-sm text-gray-500">No matches</div>
                        ) : (
                            filteredCountryOptions.map((country) => {
                                const code = country.country_code;
                                const checked = isGeoRowChecked(code);
                                return (
                                    <label
                                        key={code}
                                        className="flex cursor-pointer items-center gap-3 border-b border-gray-100 px-3 py-2 last:border-b-0 hover:bg-gray-50"
                                    >
                                        <input
                                            type="checkbox"
                                            className="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            checked={checked}
                                            onChange={() => toggleGeoCode(code)}
                                        />
                                        <span className="flex-1 text-sm text-gray-800">
                                            {country.country_name}
                                            {code !== 'ALL' && (
                                                <span className="ml-2 font-mono text-xs text-gray-500">{code}</span>
                                            )}
                                        </span>
                                    </label>
                                );
                            })
                        )}
                    </div>
                </div>
                <p className="mt-1 text-xs text-gray-500">
                    Click rows to toggle selection. Choosing &quot;All Countries&quot; clears specific countries.
                </p>
                <InputError className="mt-2" message={errors.target_geos} />
            </div>

            <div>
                <InputLabel value="Tags" />
                <div className="mt-2 flex flex-wrap gap-2">
                    {tagPresets.map((tag) => {
                        const active = selectedTags.includes(tag);
                        return (
                            <button
                                key={tag}
                                type="button"
                                onClick={() => toggleTag(tag)}
                                className={`rounded-full border px-3 py-1 text-xs ${active ? 'border-indigo-300 bg-indigo-50 text-indigo-700' : 'border-gray-300 text-gray-600'}`}
                            >
                                {tag}
                            </button>
                        );
                    })}
                </div>

                <div className="mt-3 flex gap-2">
                    <TextInput
                        className="block w-full"
                        placeholder="Add custom tag"
                        value={customTag}
                        onChange={(e) => setCustomTag(e.target.value)}
                    />
                    <button type="button" onClick={addCustomTag} className="rounded-md border border-gray-300 px-3 text-sm text-gray-700">
                        Add
                    </button>
                </div>

                {selectedTags.length > 0 && (
                    <div className="mt-3 flex flex-wrap gap-2">
                        {selectedTags.map((tag) => (
                            <button
                                type="button"
                                key={tag}
                                onClick={() => removeTag(tag)}
                                className="rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-700"
                                title="Remove tag"
                            >
                                {tag} x
                            </button>
                        ))}
                    </div>
                )}
                <InputError className="mt-2" message={errors.tags} />
            </div>

            <div className="flex items-center gap-2">
                <input id="is_active" type="checkbox" checked={data.is_active} onChange={(e) => setData('is_active', e.target.checked)} />
                <InputLabel htmlFor="is_active" value="Active" />
            </div>

            <div className="flex items-center justify-end gap-3">
                <Link href={cancelHref} className="text-sm text-gray-600">Cancel</Link>
                <button
                    type="submit"
                    disabled={processing}
                    className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
                >
                    {submitLabel}
                </button>
            </div>
        </div>
    );
}
