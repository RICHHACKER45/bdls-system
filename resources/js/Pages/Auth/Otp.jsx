import React, { useState, useEffect, useRef } from 'react';
import { Head, useForm, router } from '@inertiajs/react';

export default function Otp({ verifyRoute, resendRoute, cooldown, errors, session }) {
    const [secondsLeft, setSecondsLeft] = useState(cooldown || 0);
    const inputs = useRef([]);

    const { data, setData, post, processing } = useForm({
        otp: ['', '', '', '', '', ''],
    });

    useEffect(() => {
        if (secondsLeft > 0) {
            const timer = setInterval(() => {
                setSecondsLeft((prev) => prev - 1);
            }, 1000);
            return () => clearInterval(timer);
        }
    }, [secondsLeft]);

    const handleChange = (index, value) => {
        const newValue = value.replace(/[^0-9]/g, '');
        const newOtp = [...data.otp];
        newOtp[index] = newValue;
        setData('otp', newOtp);

        if (newValue !== '' && index < 5) {
            inputs.current[index + 1].focus();
        }
    };

    const handleKeyDown = (index, e) => {
        if (e.key === 'Backspace' && data.otp[index] === '' && index > 0) {
            inputs.current[index - 1].focus();
        }
    };

    const submit = (e) => {
        e.preventDefault();
        // Inertia useForm post takes URL as first argument
        post(verifyRoute);
    };

    const handleResend = (e) => {
        e.preventDefault();
        router.post(
            resendRoute,
            {},
            {
                onSuccess: () => {
                    // Usually the server will return the updated cooldown in props
                    // but we can also manually reset if we know it's 60s
                    // However, let's rely on props if possible.
                },
            }
        );
    };

    // Update secondsLeft if cooldown prop changes (after resend)
    useEffect(() => {
        setSecondsLeft(cooldown || 0);
    }, [cooldown]);

    return (
        <div className="flex min-h-screen items-center justify-center bg-slate-50 p-4 font-sans text-slate-900 antialiased">
            <Head title="OTP Verification - Barangay Doña Lucia" />

            <div className="mx-4 w-full max-w-md rounded-2xl border border-slate-100 bg-white p-6 text-center shadow-xl md:p-8">
                <h2 className="mb-2 text-2xl font-bold text-slate-900">
                    I-verify ang iyong Numero
                </h2>
                <p className="mb-8 text-sm text-slate-500">
                    Nagpadala kami ng 6-digit code sa iyong numero. I-enter ito sa ibaba.
                </p>

                {session?.success && (
                    <div className="mb-6 rounded-r-lg border-l-4 border-green-500 bg-green-50 p-4 text-left shadow-sm">
                        <div className="mb-1 flex items-center gap-2 font-bold text-green-700">
                            <svg
                                className="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth="2"
                                    d="M5 13l4 4L19 7"
                                ></path>
                            </svg>
                            Success
                        </div>
                        <p className="text-sm font-medium text-green-600">{session.success}</p>
                    </div>
                )}

                {Object.keys(errors).length > 0 && (
                    <div className="mb-6 rounded-r-lg border-l-4 border-red-500 bg-red-50 p-4 text-left shadow-sm">
                        <div className="mb-1 flex items-center gap-2 font-bold text-red-700">
                            <svg
                                className="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                                ></path>
                            </svg>
                            Verification Failed
                        </div>
                        <p className="text-sm font-medium text-red-600">
                            {Object.values(errors)[0]}
                        </p>
                    </div>
                )}

                <form onSubmit={submit}>
                    <div
                        className="mb-8 flex justify-center gap-1 sm:gap-2 md:gap-3"
                        id="otp-container"
                    >
                        {data.otp.map((digit, index) => (
                            <input
                                key={index}
                                ref={(el) => (inputs.current[index] = el)}
                                type="text"
                                name="otp[]"
                                maxLength="1"
                                value={digit}
                                onChange={(e) => handleChange(index, e.target.value)}
                                onKeyDown={(e) => handleKeyDown(index, e)}
                                className="otp-box h-12 w-10 rounded-xl border border-slate-300 bg-slate-50 text-center text-xl font-extrabold text-slate-900 transition-all outline-none focus:border-slate-900 focus:bg-white focus:ring-4 focus:ring-slate-200 sm:h-14 sm:w-12 md:h-16 md:w-14 md:text-2xl"
                            />
                        ))}
                    </div>

                    <button
                        type="submit"
                        disabled={processing}
                        className="w-full rounded-xl bg-slate-900 px-8 py-3 font-bold text-white transition-all duration-200 hover:bg-slate-800 active:scale-95 disabled:opacity-50"
                    >
                        {processing ? 'Verifying...' : 'Verify Account'}
                    </button>
                </form>

                <div className="mt-8 border-t border-slate-100 pt-6 text-center">
                    <p className="mb-3 text-sm text-slate-500">Hindi nakuha ang code?</p>
                    <button
                        onClick={handleResend}
                        disabled={secondsLeft > 0 || processing}
                        className="text-sm font-bold text-slate-900 transition-all hover:underline disabled:cursor-not-allowed disabled:text-slate-400 disabled:no-underline"
                    >
                        Magpadala ulit ng code{' '}
                        {secondsLeft > 0 && (
                            <span className="ml-1 font-mono text-red-600">({secondsLeft}s)</span>
                        )}
                    </button>
                </div>
            </div>
        </div>
    );
}
