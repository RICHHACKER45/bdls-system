import React from 'react';
import { Link, Head, useForm } from '@inertiajs/react';

export default function ResetPassword({ errors, session }) {
    const { data, setData, post, processing, reset } = useForm({
        password: '',
        password_confirmation: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('password.update.submit'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <div className="flex min-h-screen items-center justify-center bg-slate-50 p-4 font-sans text-slate-900 antialiased">
            <Head title="Reset Password - BDLS" />

            <div className="w-full max-w-md rounded-2xl border border-slate-100 bg-white p-6 shadow-xl md:p-8">
                <div className="mb-8 text-center">
                    <h2 className="text-2xl font-bold text-slate-900">I-reset ang Password</h2>
                    <p className="mt-2 text-sm text-slate-500">
                        I-enter ang iyong bagong password para sa account.
                    </p>
                </div>

                {Object.keys(errors).length > 0 && (
                    <div className="mb-6 rounded-r-lg border-l-4 border-red-500 bg-red-50 p-4 text-sm font-medium text-red-700 shadow-sm">
                        {Object.values(errors)[0]}
                    </div>
                )}

                {session?.success && (
                    <div className="mb-6 rounded-r-lg border-l-4 border-green-500 bg-green-50 p-4 text-sm font-medium text-green-700 shadow-sm">
                        {session.success}
                    </div>
                )}

                <form onSubmit={submit} className="space-y-6">
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label className="mb-1 block text-sm font-semibold text-slate-700">Bagong Password</label>
                            <input
                                type="password"
                                name="password"
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                minLength="8"
                                required
                                className="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-3 transition-all outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-900"
                            />
                        </div>
                        <div>
                            <label className="mb-1 block text-sm font-semibold text-slate-700">Confirm Password</label>
                            <input
                                type="password"
                                name="password_confirmation"
                                value={data.password_confirmation}
                                onChange={(e) => setData('password_confirmation', e.target.value)}
                                minLength="8"
                                required
                                className="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-3 transition-all outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-900"
                            />
                        </div>
                    </div>

                    <button
                        type="submit"
                        disabled={processing}
                        className="mt-4 w-full rounded-xl bg-red-600 px-8 py-3.5 font-bold text-white shadow-md transition-all hover:bg-red-700 active:scale-95 disabled:opacity-50"
                    >
                        {processing ? 'Sinasave...' : 'I-save at Mag-login'}
                    </button>
                </form>

                <div className="mt-6 text-center">
                    <Link href={route('login')} className="text-xs font-bold text-slate-400 transition-all hover:text-slate-600">
                        I-cancel at bumalik sa Login
                    </Link>
                </div>
            </div>
        </div>
    );
}
