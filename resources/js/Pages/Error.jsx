import React from 'react';
import { Head, Link } from '@inertiajs/react';

export default function ErrorPage({ status }) {
    // Tukuyin kung anong mensahe at title base sa error status
    const title =
        {
            503: 'Under Maintenance',
            500: 'Server Error',
            404: 'Page Not Found',
            403: 'Forbidden Access',
            419: 'Session Expired',
        }[status] || 'May Mali sa System';

    const heading = {
        503: 'Ang aming website ay down.',
        500: 'May nangyaring error sa server.',
        404: 'Walang page dito.',
        403: 'Bawal pumasok dito.',
        419: 'Pahina Nag-Expire',
    }[status];

    const description = {
        503: 'May inaayos lang kami sa Website. Maaring bumalik na lang po kayo mamaya!',
        500: 'Nakaranas ng problema ang aming server. Paki-try ulit mamaya.',
        404: 'Wala dito ang iyong hinahanap. Maaring bumalik na sa Homepage.',
        403: 'Wala ka pong access sa page na ito. Maaring bumalik na sa Homepage.',
        419: 'Masyadong matagal na nabakante ang iyong session. Para sa seguridad, paki-refresh ang pahina.',
    }[status];

    return (
        <div className="flex min-h-screen items-center justify-center bg-slate-50 p-4 font-sans text-slate-900">
            <Head title={`${title} - BDLS`} />

            <div className="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-xl">
                {/* Warning Icon (Red Accent) */}
                <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-red-100 text-red-600">
                    <svg className="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                        ></path>
                    </svg>
                </div>

                {/* Dynamic Typography */}
                <h1 className="mb-2 text-2xl font-extrabold text-slate-900">{heading}</h1>
                <p className="mb-6 text-sm font-medium text-slate-600">{description}</p>

                {/* Primary Action Button (Babalik sa Home gamit ang React Link) */}
                <Link
                    href="/"
                    className="inline-block w-full rounded-xl bg-slate-900 px-4 py-3.5 font-bold text-white shadow-md transition-all hover:bg-slate-800 active:scale-95"
                >
                    Bumalik sa Home
                </Link>
            </div>
        </div>
    );
}
