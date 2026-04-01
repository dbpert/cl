import { Link } from '@inertiajs/react';

const VARIANT_CLASSES = {
    neutral: 'border-gray-200 text-gray-600 hover:bg-gray-100 hover:text-gray-900',
    primary: 'border-indigo-200 text-indigo-600 hover:bg-indigo-100 hover:text-indigo-700',
    danger: 'border-red-200 text-red-600 hover:bg-red-100 hover:text-red-700',
};

export default function ActionIconButton({
    href,
    method,
    as,
    title,
    variant = 'neutral',
    className = '',
    children,
    ...props
}) {
    const variantClass = VARIANT_CLASSES[variant] ?? VARIANT_CLASSES.neutral;

    return (
        <Link
            href={href}
            method={method}
            as={as}
            title={title}
            aria-label={title}
            className={`inline-flex h-9 w-9 items-center justify-center rounded-md border transition focus:outline-none focus:ring-2 focus:ring-indigo-300 ${variantClass} ${className}`}
            {...props}
        >
            <span className="h-5 w-5">{children}</span>
        </Link>
    );
}
