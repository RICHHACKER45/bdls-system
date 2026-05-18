import React from 'react';
import { Link, Head, useForm } from '@inertiajs/react';

export default function Login({ errors, session }) {
    const { data, setData, post, processing } = useForm({
        login_id: '',
        password: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('login.post'));
    };

    return (
        <div className="flex min-h-screen items-center justify-center bg-slate-50 font-sans text-slate-900 antialiased">
            <Head title="BDLS - Login" />

            <div className="absolute top-4 left-4 md:top-8 md:left-8">
                <Link
                    href="/"
                    className="flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-slate-500 transition-all duration-200 hover:text-red-600 focus:ring-4 focus:ring-slate-200 focus:outline-none active:scale-95 active:bg-slate-200"
                >
                    <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Bumalik sa Home
                </Link>
            </div>

            <main className="mx-auto flex w-full max-w-5xl flex-col items-center gap-8 p-4 md:flex-row md:gap-16 md:p-8">
                <div className="flex w-full flex-col items-center text-center md:w-1/2 md:items-start md:text-left">
                    <div className="mt-12 mb-6 flex h-24 w-24 items-center justify-center overflow-hidden rounded-full border-4 border-white bg-white shadow-lg md:mt-0">
                        <img
                            src="/images/bdls-logo-large.png"
                            alt="BDLS Logo"
                            className="h-full w-full object-cover"
                            onError={(e) => {
                                e.currentTarget.style.display = 'none';
                                e.currentTarget.parentElement.innerHTML = '<span class="text-2xl font-bold text-red-600">BDLS</span>';
                            }}
                        />
                    </div>
                    <h1 className="mb-4 text-3xl font-extrabold tracking-tight md:text-5xl">
                        Barangay Doña Lucia <span className="text-red-600">Services</span>
                    </h1>
                    <p className="max-w-md text-lg text-slate-600 md:text-xl">
                        Ang iyong mabilis at direktang koneksyon para sa mga dokumento at serbisyo ng barangay.
                    </p>
                </div>

                <div className="w-full md:w-1/2">
                    <div className="rounded-2xl border border-slate-100 bg-white p-8 shadow-xl">
                        <h2 className="mb-6 text-center text-2xl font-bold">Mag-login sa System</h2>

                        {Object.keys(errors).length > 0 && (
                            <div className="mb-4 rounded-r-lg border-l-4 border-red-500 bg-red-50 p-4 text-left shadow-sm">
                                <div className="mb-1 flex items-center gap-2 font-bold text-red-700">
                                    <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                                        ></path>
                                    </svg>
                                    Mali ang Login
                                </div>
                                <p className="text-sm font-medium text-red-600">{Object.values(errors)[0]}</p>
                            </div>
                        )}

                        {session?.success && (
                            <div className="mb-4 rounded-r-lg border-l-4 border-green-500 bg-green-50 p-4 text-left shadow-sm">
                                <p className="text-sm font-medium text-green-600">{session.success}</p>
                            </div>
                        )}

                        <form onSubmit={submit} className="space-y-5">
                            <div>
                                <label htmlFor="login_id" className="mb-1 block text-sm font-semibold text-slate-700">
                                    Contact Number o Email
                                </label>
                                <input
                                    type="text"
                                    id="login_id"
                                    name="login_id"
                                    value={data.login_id}
                                    onChange={(e) => setData('login_id', e.target.value)}
                                    placeholder="09123456789 / juan@email.com"
                                    required
                                    className="w-full rounded-lg border border-slate-300 px-4 py-3 transition-all outline-none focus:border-red-600 focus:ring-2 focus:ring-red-600"
                                />
                            </div>

                            <div>
                                <div className="mb-1 flex items-center justify-between">
                                    <label htmlFor="password" class="block text-sm font-semibold text-slate-700">
                                        Password
                                    </label>
                                    <Link
                                        href={route('password.request')}
                                        className="text-xs font-bold text-slate-400 transition-all hover:text-slate-900 hover:underline"
                                    >
                                        Nakalimutan ang password?
                                    </Link>
                                </div>
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    value={data.password}
                                    onChange={(e) => setData('password', e.target.value)}
                                    placeholder="••••••••"
                                    required
                                    className="w-full rounded-lg border border-slate-300 px-4 py-3 transition-all outline-none focus:border-red-600 focus:ring-2 focus:ring-red-600"
                                />
                            </div>

                            <div className="pt-2">
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="w-full rounded-lg bg-red-600 px-4 py-3.5 font-bold text-white shadow-md transition-all duration-200 hover:bg-red-700 hover:shadow-lg active:scale-95 disabled:opacity-50"
                                >
                                    {processing ? 'Pumapasok...' : 'Pumasok'}
                                </button>
                            </div>
                        </form>

                        <div className="mt-6 flex items-center gap-3 text-sm font-medium text-slate-400">
                            <div className="h-px flex-1 bg-slate-200"></div>
                            <span className="text-[10px] tracking-widest uppercase">o kaya</span>
                            <div className="h-px flex-1 bg-slate-200"></div>
                        </div>

                        <div className="mt-6">
                            <Link
                                href="/signup"
                                className="flex w-full items-center justify-center rounded-lg border-2 border-slate-200 bg-transparent px-4 py-3.5 font-bold text-slate-600 shadow-sm transition-all duration-200 hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900 active:scale-95"
                            >
                                Gumawa ng Account
                            </Link>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    );
}
