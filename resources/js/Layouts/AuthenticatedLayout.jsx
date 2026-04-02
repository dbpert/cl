import { Link, usePage } from '@inertiajs/react';
import { useMemo, useState } from 'react';

export default function AuthenticatedLayout({ header, children }) {
    const user = usePage().props.auth.user;
    const [isSidebarExpanded, setIsSidebarExpanded] = useState(false);

    const navItems = useMemo(
        () => [
            { label: 'Dashboard', href: route('dashboard'), active: route().current('dashboard'), icon: 'D' },
            { label: 'Campaigns', href: route('campaigns.index'), active: route().current('campaigns.*'), icon: 'C' },
            { label: 'Precheck API', href: route('api-docs.precheck'), active: route().current('api-docs.precheck'), icon: 'P' },
            { label: 'Device API', href: route('api-docs.device-fingerprints'), active: route().current('api-docs.device-fingerprints'), icon: 'F' },
        ],
        [],
    );

    return (
        <div className="min-h-screen">
            <aside
                className={`fixed inset-y-0 left-0 z-40 hidden border-r border-gray-200 bg-white shadow-sm transition-all duration-200 md:flex md:flex-col ${
                    isSidebarExpanded ? 'w-64' : 'w-16'
                }`}
                onMouseEnter={() => setIsSidebarExpanded(true)}
                onMouseLeave={() => setIsSidebarExpanded(false)}
            >
                <div className="flex h-16 shadow items-center px-3">
                    <div className="flex h-9 w-9 items-center justify-center rounded-md bg-indigo-600 text-sm font-bold text-white">
                        M
                    </div>
                    {isSidebarExpanded && <span className="ml-3 text-sm font-semibold text-gray-800">Machu Admin</span>}
                </div>

                <nav className="flex-1 space-y-1 px-2 py-4">
                    {navItems.map((item) => (
                        <Link
                            key={item.label}
                            href={item.href}
                            className={`flex items-center rounded-md px-2 py-2 text-sm transition ${
                                item.active
                                    ? 'bg-indigo-50 text-indigo-700'
                                    : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'
                            }`}
                        >
                            <span className="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-gray-100 text-xs font-semibold text-gray-700">
                                {item.icon}
                            </span>
                            {isSidebarExpanded && <span className="ml-3">{item.label}</span>}
                        </Link>
                    ))}
                </nav>

                <div className="border-t border-gray-100 p-2">
                    <Link
                        href={route('profile.edit')}
                        className="flex items-center rounded-md px-2 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900"
                    >
                        <span className="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-gray-100 text-xs font-semibold text-gray-700">
                            P
                        </span>
                        {isSidebarExpanded && <span className="ml-3 truncate">{user.name}</span>}
                    </Link>
                    <Link
                        href={route('logout')}
                        method="post"
                        as="button"
                        className="mt-1 flex w-full items-center rounded-md px-2 py-2 text-sm text-red-600 hover:bg-red-50"
                    >
                        <span className="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-red-100 text-xs font-semibold text-red-700">
                            O
                        </span>
                        {isSidebarExpanded && <span className="ml-3">Log Out</span>}
                    </Link>
                </div>
            </aside>

            <div className="md:ml-16">
                {header && (
                    <header className="bg-white shadow h-16">
                        <div className="px-4 py-5 sm:px-6 lg:px-8">{header}</div>
                    </header>
                )}
                <main>{children}</main>
            </div>
        </div>
    );
}
