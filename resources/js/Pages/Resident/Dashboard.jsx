import React, { useState, useEffect, useRef, useCallback } from 'react';
import { Head, Link, useForm, usePage, router } from '@inertiajs/react';
import Webcam from 'react-webcam';
import ResidentLayout from '@/Layouts/ResidentLayout';

// ==========================================
// MODAL: CREATE SERVICE REQUEST
// ==========================================
const RequestModal = ({ isOpen, onClose, documents, auth }) => {
    const [requirements, setRequirements] = useState('');
    const [needsUpload, setNeedsUpload] = useState(false);

    const { data, setData, post, processing, errors, reset, clearErrors } = useForm({
        document_type_id: '',
        purpose: '',
        preferred_pickup_time: '',
        additional_details: '',
        attachments: [],
    });

    const handleDocumentChange = (id) => {
        const doc = documents.find((d) => d.id === parseInt(id));
        setData('document_type_id', id);

        if (doc) {
            setRequirements(doc.requirements_description);
            setNeedsUpload(doc.requirements_description.toLowerCase() !== 'valid id');
        } else {
            setRequirements('');
            setNeedsUpload(false);
        }
        clearErrors('document_type_id');
    };

    const submit = (e) => {
        e.preventDefault();
        post(route('resident.request.store'), {
            preserveScroll: true,
            onSuccess: () => {
                reset();
                onClose();
            },
        });
    };

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm transition-opacity">
            <div className="flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-2xl">
                <div className="flex items-center justify-between border-b border-slate-100 bg-slate-50 p-6">
                    <div>
                        <h2 className="text-xl font-bold text-slate-900">Gumawa ng Request</h2>
                        <p className="mt-1 text-sm text-slate-500">
                            Kumpletuhin ang detalye para sa iyong queue number.
                        </p>
                    </div>
                    <button
                        type="button"
                        onClick={onClose}
                        className="rounded-full bg-slate-200 p-2 text-slate-400 transition-all hover:bg-red-100 hover:text-red-600 active:scale-95"
                    >
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
                                d="M6 18L18 6M6 6l12 12"
                            ></path>
                        </svg>
                    </button>
                </div>

                <div className="overflow-y-auto p-6">
                    <form onSubmit={submit} className="space-y-6">
                        {Object.keys(errors).length > 0 && (
                            <div className="mb-5 rounded-lg border-l-4 border-red-500 bg-red-50 p-4 shadow-sm">
                                <div className="mb-1 flex items-center gap-2 text-sm font-bold text-red-800">
                                    <svg
                                        className="h-4 w-4"
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
                                    May Kulang o Mali sa Form
                                </div>
                                <p className="text-xs font-medium text-red-600">
                                    {Object.values(errors)}
                                </p>
                            </div>
                        )}

                        <div className="rounded-xl border border-slate-200 bg-slate-100 p-5 shadow-inner">
                            <h3 className="mb-3 border-b border-slate-200 pb-2 text-sm font-bold text-slate-800">
                                Impormasyon ng Nagre-request
                            </h3>
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label className="block text-xs font-semibold text-slate-500">
                                        Buong Pangalan
                                    </label>
                                    <p className="text-sm font-bold text-slate-900">
                                        {auth?.user?.first_name} {auth?.user?.middle_name}{' '}
                                        {auth?.user?.last_name} {auth?.user?.suffix}
                                    </p>
                                </div>
                                <div>
                                    <label className="block text-xs font-semibold text-slate-500">
                                        Edad
                                    </label>
                                    <p className="text-sm font-bold text-slate-900">
                                        {auth?.user?.age} taong gulang
                                    </p>
                                </div>
                                <div className="md:col-span-2">
                                    <label className="block text-xs font-semibold text-slate-500">
                                        Tirahan
                                    </label>
                                    <p className="text-sm font-bold text-slate-900">
                                        {auth?.user?.house_number} {auth?.user?.purok_street},
                                        Barangay Doña Lucia
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label className="mb-2 block text-sm font-bold text-slate-800">
                                Uri ng Dokumento <span className="text-red-500">*</span>
                            </label>
                            <select
                                value={data.document_type_id}
                                onChange={(e) => handleDocumentChange(e.target.value)}
                                required
                                className={`w-full rounded-xl border px-4 py-3 ${errors.document_type_id ? 'border-red-500 ring-1 ring-red-500' : 'border-slate-300'} cursor-pointer bg-slate-50 transition-all outline-none focus:ring-2 focus:ring-slate-900`}
                            >
                                <option value="">-- Pumili ng Dokumento --</option>
                                {documents.map((doc) => (
                                    <option key={doc.id} value={doc.id}>
                                        {doc.name}
                                    </option>
                                ))}
                            </select>
                            {requirements && (
                                <div className="mt-3 rounded-lg border border-blue-200 bg-blue-50 p-4 text-blue-700 shadow-inner transition-all">
                                    <div className="mb-1 flex items-center gap-2">
                                        <svg
                                            className="h-5 w-5 text-blue-600"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth="2"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            ></path>
                                        </svg>
                                        <p className="text-sm font-bold text-blue-800">
                                            Mga Kinakailangang Dalhin / I-upload:
                                        </p>
                                    </div>
                                    <p className="ml-7 text-sm font-medium">{requirements}</p>
                                </div>
                            )}
                        </div>

                        <div>
                            <label className="mb-2 block text-sm font-bold text-slate-800">
                                Layunin (Purpose) <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                value={data.purpose}
                                onChange={(e) => {
                                    setData('purpose', e.target.value);
                                    clearErrors('purpose');
                                }}
                                placeholder="Hal. Requirement sa Trabaho..."
                                required
                                className={`w-full rounded-xl border px-4 py-3 ${errors.purpose ? 'border-red-500 ring-1 ring-red-500' : 'border-slate-300'} bg-slate-50 transition-all outline-none focus:ring-2 focus:ring-slate-900`}
                            />
                        </div>

                        <div>
                            <label className="mb-2 block text-sm font-bold text-slate-800">
                                Kailan mo gustong kunin? <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="datetime-local"
                                value={data.preferred_pickup_time}
                                onChange={(e) => {
                                    setData('preferred_pickup_time', e.target.value);
                                    clearErrors('preferred_pickup_time');
                                }}
                                required
                                className={`w-full rounded-xl border px-4 py-3 ${errors.preferred_pickup_time ? 'border-red-500 ring-1 ring-red-500' : 'border-slate-300'} bg-slate-50 transition-all outline-none focus:ring-2 focus:ring-slate-900`}
                            />
                        </div>

                        <div>
                            <label className="mb-2 block text-sm font-bold text-slate-800">
                                Karagdagang Detalye (Optional)
                            </label>
                            <textarea
                                value={data.additional_details}
                                onChange={(e) => setData('additional_details', e.target.value)}
                                rows="2"
                                className="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 transition-all outline-none focus:ring-2 focus:ring-slate-900"
                            ></textarea>
                        </div>

                        {needsUpload && (
                            <div
                                className={`border-2 border-dashed bg-white p-5 ${errors.attachments ? 'border-red-500 bg-red-50' : 'border-slate-300'} rounded-xl`}
                            >
                                <label className="mb-2 block text-sm font-bold text-slate-800">
                                    I-upload ang Karagdagang Dokumento{' '}
                                    <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="file"
                                    onChange={(e) =>
                                        setData('attachments', Array.from(e.target.files))
                                    }
                                    multiple
                                    accept="image/jpeg, image/png, image/jpg, application/pdf"
                                    className="w-full cursor-pointer text-sm text-slate-500 file:mr-4 file:rounded-lg file:border-0 file:bg-slate-900 file:px-4 file:py-2.5 file:text-sm file:font-bold file:text-white hover:file:bg-slate-800"
                                />
                            </div>
                        )}

                        <div className="flex justify-end gap-3 border-t border-slate-100 bg-slate-50 p-4">
                            <button
                                type="button"
                                onClick={onClose}
                                className="rounded-xl px-6 py-2.5 font-bold text-slate-600 transition-all hover:bg-slate-200 active:scale-95"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                disabled={processing}
                                className="rounded-xl bg-slate-900 px-8 py-2.5 font-bold text-white shadow-md hover:bg-slate-800 active:scale-95 disabled:opacity-50"
                            >
                                {processing ? 'Submitting...' : 'Submit Request'}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
};

// ==========================================
// MAIN COMPONENT: RESIDENT DASHBOARD
// ==========================================
export default function Dashboard() {
    const {
        documents,
        myRequests = [],
        pendingRequests = [],
        readyRequests = [],
        historyRequests = [],
        announcements = [],
        auth,
        errors = {},
    } = usePage().props;

    // Main Tabs State
    const [activeTab, setActiveTab] = useState('dashboard');
    const [activeSubTab, setActiveSubTab] = useState('track-pending');

    // Modals State
    const [isRequestModalOpen, setIsRequestModalOpen] = useState(false);
    const [isLiveKycModalOpen, setIsLiveKycModalOpen] = useState(false);
    const [settingsModal, setSettingsModal] = useState(null); // 'changeContact', 'verifyContact', 'changeEmail', 'verifyEmail'

    // ==========================================
    // 📸 LIVE ID SCANNER LOGIC
    // ==========================================
    const webcamRef = useRef(null);
    const [isScanning, setIsScanning] = useState(false);
    const [previewImage, setPreviewImage] = useState(null);
    const kycForm = useForm({ id_photo_path: null });

    const captureId = useCallback(() => {
        const imageSrc = webcamRef.current.getScreenshot();
        setPreviewImage(imageSrc);
        setIsScanning(false);

        fetch(imageSrc)
            .then((res) => res.blob())
            .then((blob) => {
                const file = new File([blob], 'live_id_capture.jpg', { type: 'image/jpeg' });
                kycForm.setData('id_photo_path', file);
            });
    }, [webcamRef, kycForm]);

    // Timers for OTP Resend
    const [contactTimer, setContactTimer] = useState(0);
    const [emailTimer, setEmailTimer] = useState(0);

    useEffect(() => {
        let ct, et;
        if (contactTimer > 0) ct = setInterval(() => setContactTimer((prev) => prev - 1), 1000);
        if (emailTimer > 0) et = setInterval(() => setEmailTimer((prev) => prev - 1), 1000);
        return () => {
            clearInterval(ct);
            clearInterval(et);
        };
    }, [contactTimer, emailTimer]);

    // Error Interceptor for Modals Auto-Open (Fallback Routing)
    useEffect(() => {
        if (errors.otp_error || errors.contact_number) setSettingsModal('verifyContact');
        else if (errors.email_otp) setSettingsModal('verifyEmail');
    }, [errors]);

    // Idagdag ito malapit sa ibang useEffect
    useEffect(() => {
        if (window.Echo && auth?.user?.id) {
            // THE FIX: Makikinig sa pinasimpleng 'resident.{id}' channel
            window.Echo.private(`resident.${auth.user.id}`).listen(
                'ResidentRequestUpdated',
                (e) => {
                    // SILENT REFRESH
                    router.reload({
                        only: [
                            'myRequests',
                            'pendingRequests',
                            'readyRequests',
                            'historyRequests',
                            'auth',
                        ],
                        preserveScroll: true,
                        preserveState: true,
                    });
                }
            );
        }
        return () => {
            if (window.Echo && auth?.user?.id) {
                window.Echo.leaveChannel(`resident.${auth.user.id}`);
            }
        };
    }, [auth?.user?.id]);

    // ==========================================
    // SETTINGS FORMS (Phase 5 Completed)
    // ==========================================
    const passwordForm = useForm({ current_password: '', password: '', password_confirmation: '' });
    const contactForm = useForm({ contact_number: '' });
    const verifyContactForm = useForm({ otp_code: '' });
    const emailForm = useForm({ new_email: '' });
    const verifyEmailForm = useForm({ email_otp: '' });

    const submitPasswordUpdate = (e) => {
        e.preventDefault();
        passwordForm.post(route('password.update'), {
            preserveScroll: true,
            onSuccess: () => passwordForm.reset(),
        });
    };

    const submitContactUpdate = (e) => {
        e.preventDefault();
        contactForm.post(route('resident.settings.update_contact'), {
            preserveScroll: true,
            onSuccess: () => setSettingsModal('verifyContact'),
        });
    };

    const submitVerifyContact = (e) => {
        e.preventDefault();
        verifyContactForm.post(route('resident.settings.verify_contact'), {
            preserveScroll: true,
            onSuccess: () => {
                verifyContactForm.reset();
                setSettingsModal(null);
            },
        });
    };

    const resendContactOtp = (e) => {
        e.preventDefault();
        setContactTimer(60);
        router.post(
            route('resident.settings.update_contact'),
            { contact_number: auth?.user?.contact_number },
            { preserveScroll: true }
        );
    };

    const submitEmailUpdate = (e) => {
        e.preventDefault();
        emailForm.post(route('resident.email.add'), {
            preserveScroll: true,
            onSuccess: () => setSettingsModal('verifyEmail'),
        });
    };

    const submitVerifyEmail = (e) => {
        e.preventDefault();
        verifyEmailForm.post(route('resident.email.verify'), {
            preserveScroll: true,
            onSuccess: () => {
                verifyEmailForm.reset();
                setSettingsModal(null);
            },
        });
    };

    const resendEmailOtp = (e) => {
        e.preventDefault();
        setEmailTimer(60);
        router.post(route('resident.email.send'), {}, { preserveScroll: true });
    };

    const toggleEmailPreference = (e) => {
        router.post(
            route('resident.settings.email_preference'),
            { wants_email_notification: e.target.checked ? 1 : 0 },
            { preserveScroll: true }
        );
    };

    const sidebarNav = [
        {
            id: 'dashboard',
            label: 'Dashboard',
            icon: (
                <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                    ></path>
                </svg>
            ),
        },
        {
            id: 'tracking',
            label: 'Track Requests',
            icon: (
                <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"
                    ></path>
                </svg>
            ),
        },
        {
            id: 'settings',
            label: 'Account Settings',
            icon: (
                <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth="2"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                    ></path>
                    <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                    ></path>
                </svg>
            ),
        },
    ];

    return (
        <ResidentLayout activeTab={activeTab} setActiveTab={setActiveTab}>
            <Head title="Dashboard - BDLS" />

            {/* --- TAB 1: DASHBOARD --- */}
            {activeTab === 'dashboard' && (
                <div className="animate-in fade-in duration-500">
                    {auth?.user?.email && !auth?.user?.email_verified_at && (
                        <div className="mb-8 flex flex-col items-start justify-between gap-4 rounded-r-xl border-l-4 border-blue-500 bg-blue-50 p-4 shadow-sm sm:flex-row sm:items-center sm:p-5">
                            <div>
                                <h3 className="flex items-center gap-2 text-lg font-bold text-blue-800">
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
                                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                        ></path>
                                    </svg>
                                    I-verify ang iyong Email
                                </h3>
                                <p className="mt-1 text-sm text-blue-600">
                                    Kasalukuyang naka-link ang{' '}
                                    <span className="font-bold">{auth?.user?.email}</span>. I-verify
                                    ito para makatanggap ng digital receipts.
                                </p>
                            </div>
                            <div className="w-full sm:w-auto">
                                <button
                                    type="button"
                                    onClick={() => setActiveTab('settings')}
                                    className="w-full rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:bg-blue-700 active:scale-95 sm:w-auto"
                                >
                                    Pumunta sa Settings
                                </button>
                            </div>
                        </div>
                    )}

                    <h1 className="mb-6 text-2xl font-bold text-slate-900">Resident Dashboard</h1>

                    {!auth?.user?.is_verified ? (
                        new Date(auth?.user?.ocr_locked_until) > new Date() ? (
                            <div className="mb-6 rounded-r-xl border-l-4 border-red-500 bg-red-50 p-6 shadow-sm">
                                <div className="mb-2 flex items-center gap-3 text-lg font-bold text-red-800">
                                    <svg
                                        className="h-6 w-6"
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
                                    Pansamantalang Naka-lock ang Verification
                                </div>
                                <p className="mb-4 text-sm text-red-700">
                                    Nagamit mo na ang lahat ng 5 subok. Kailangan mong maghintay
                                    hanggang {new Date(auth.user.ocr_locked_until).toLocaleString()}
                                    .
                                </p>
                            </div>
                        ) : (
                            <div className="mb-6 rounded-r-xl border-l-4 border-amber-500 bg-amber-50 p-6 shadow-sm">
                                <div className="mb-2 flex items-center gap-3 text-lg font-bold text-amber-800">
                                    <svg
                                        className="h-6 w-6"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                        ></path>
                                    </svg>
                                    Hindi pa Verified ang Account
                                </div>
                                <p className="mb-4 text-sm text-amber-700">
                                    Kailangan mong i-verify ang iyong account. Mayroon ka na lamang{' '}
                                    {5 - (auth?.user?.ocr_attempts || 0)} na subok na natitira.
                                </p>
                                <button
                                    type="button"
                                    onClick={() => setIsLiveKycModalOpen(true)}
                                    className="rounded-lg bg-red-600 px-5 py-2.5 text-xs font-bold tracking-widest text-white uppercase shadow-sm transition-all hover:bg-red-700 active:scale-95"
                                >
                                    📸 I-Scan Ulit ang ID
                                </button>
                            </div>
                        )
                    ) : (
                        <div className="grid grid-cols-1 gap-4 lg:grid-cols-3">
                            <div className="col-span-1 flex flex-col gap-4 lg:col-span-2">
                                <div className="flex flex-col justify-between rounded-2xl border border-slate-100 bg-white p-6 shadow-sm md:p-8">
                                    <div>
                                        <div className="mb-5 flex h-14 w-14 items-center justify-center rounded-xl bg-red-100 text-red-600">
                                            <svg
                                                className="h-7 w-7"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    strokeLinecap="round"
                                                    strokeLinejoin="round"
                                                    strokeWidth="2"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                                ></path>
                                            </svg>
                                        </div>
                                        <h2 className="text-2xl font-bold text-slate-900">
                                            Kumuha ng Dokumento
                                        </h2>
                                        <p className="mb-8 max-w-md text-slate-500">
                                            Mag-request ng Barangay Clearance, Certificate of
                                            Indigency, at iba pang legal na papel nang hindi na
                                            pumipila nang matagal.
                                        </p>
                                    </div>
                                    <div>
                                        {auth?.user?.locked_until &&
                                        new Date(auth?.user?.locked_until) > new Date() ? (
                                            <div className="rounded-xl border border-amber-200 bg-amber-50 p-4 text-center">
                                                <p className="text-sm font-bold text-amber-700">
                                                    ⚠️ Naka-Suspend ang iyong account.
                                                </p>
                                                <p className="mt-1 text-xs text-amber-600">
                                                    Maaari ka muling mag-request sa: <br />
                                                    <span className="font-black">
                                                        {new Date(
                                                            auth?.user?.locked_until
                                                        ).toLocaleString()}
                                                    </span>
                                                </p>
                                            </div>
                                        ) : (
                                            <button
                                                onClick={() => setIsRequestModalOpen(true)}
                                                className="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-red-600 px-6 py-3.5 font-bold text-white shadow-md transition-all hover:bg-red-700 focus:outline-none active:scale-95 sm:w-auto"
                                            >
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
                                                        d="M12 4v16m8-8H4"
                                                    ></path>
                                                </svg>
                                                Gumawa ng Request
                                            </button>
                                        )}
                                    </div>
                                </div>

                                <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                                    <h3 className="mb-4 flex items-center gap-2 font-bold text-slate-700">
                                        <svg
                                            className="h-5 w-5 text-red-600"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth="2"
                                                d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"
                                            ></path>
                                        </svg>
                                        Mga Anunsyo ng Barangay
                                    </h3>
                                    <div className="space-y-3">
                                        {announcements.length > 0 ? (
                                            announcements.map((a) => (
                                                <div
                                                    key={a.id}
                                                    className="rounded-lg border border-red-100 bg-red-50/60 p-4 transition-all hover:bg-red-50"
                                                >
                                                    <p className="mb-1 text-[9px] font-black tracking-widest text-red-600 uppercase">
                                                        {new Date(a.created_at).toLocaleString()}
                                                    </p>
                                                    <p className="text-xs font-bold text-slate-800">
                                                        {a.message_body}
                                                    </p>
                                                </div>
                                            ))
                                        ) : (
                                            <p className="text-xs font-bold text-slate-400 italic">
                                                Walang bagong anunsyo.
                                            </p>
                                        )}
                                    </div>
                                </div>
                            </div>

                            <div className="flex flex-col gap-4">
                                <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                                    <h3 className="mb-4 font-bold text-slate-700">
                                        Active Requests
                                    </h3>
                                    <div className="grid grid-cols-3 gap-2 text-center">
                                        <button
                                            onClick={() => {
                                                setActiveTab('tracking');
                                                setActiveSubTab('track-pending');
                                            }}
                                            className="flex flex-col items-center justify-center rounded-xl border border-yellow-200 bg-yellow-50 py-3 transition-all hover:bg-yellow-100 active:scale-95"
                                        >
                                            <span className="text-2xl font-black text-yellow-600">
                                                {pendingRequests.length}
                                            </span>
                                            <span className="mt-1 text-[8px] font-bold tracking-widest text-yellow-700 uppercase">
                                                Pending
                                            </span>
                                        </button>
                                        <button
                                            onClick={() => {
                                                setActiveTab('tracking');
                                                setActiveSubTab('track-status');
                                            }}
                                            className="flex flex-col items-center justify-center rounded-xl border border-blue-200 bg-blue-50 py-3 transition-all hover:bg-blue-100 active:scale-95"
                                        >
                                            <span className="text-2xl font-black text-blue-600">
                                                {
                                                    myRequests.filter((r) =>
                                                        ['processing', 'for_interview'].includes(
                                                            r.status
                                                        )
                                                    ).length
                                                }
                                            </span>
                                            <span className="mt-1 text-[8px] font-bold tracking-widest text-blue-700 uppercase">
                                                Process
                                            </span>
                                        </button>
                                        <button
                                            onClick={() => {
                                                setActiveTab('tracking');
                                                setActiveSubTab('track-status');
                                            }}
                                            className="flex flex-col items-center justify-center rounded-xl border border-orange-200 bg-orange-50 py-3 transition-all hover:bg-orange-100 active:scale-95"
                                        >
                                            <span className="text-2xl font-black text-orange-600">
                                                {readyRequests.length}
                                            </span>
                                            <span className="mt-1 text-[8px] font-bold tracking-widest text-orange-700 uppercase">
                                                Ready
                                            </span>
                                        </button>
                                    </div>
                                </div>

                                <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                                    <h3 className="mb-4 font-bold text-slate-700">
                                        Recent History
                                    </h3>
                                    <div className="space-y-3">
                                        {historyRequests
                                            .filter((r) => r.status === 'received')
                                            .slice(0, 3).length > 0 ? (
                                            historyRequests
                                                .filter((r) => r.status === 'received')
                                                .slice(0, 3)
                                                .map((req) => (
                                                    <div
                                                        key={req.id}
                                                        className="flex items-center justify-between rounded-lg border border-slate-100 bg-slate-50 p-3"
                                                    >
                                                        <div className="opacity-75">
                                                            <p className="text-xs font-black text-slate-900 uppercase">
                                                                {req.queue_number}
                                                            </p>
                                                            <p className="text-[10px] font-bold text-slate-500">
                                                                {req.document_type?.name ??
                                                                    'Dokumento'}
                                                            </p>
                                                        </div>
                                                        <span className="rounded border border-green-200 bg-green-100 px-2 py-1 text-[9px] font-black tracking-widest text-green-700 uppercase shadow-sm">
                                                            {req.status}
                                                        </span>
                                                    </div>
                                                ))
                                        ) : (
                                            <p className="text-xs font-bold text-slate-400 italic">
                                                Wala ka pang nakaraang transaksyon.
                                            </p>
                                        )}
                                    </div>
                                    {historyRequests.filter((r) => r.status === 'received').length >
                                        3 && (
                                        <button
                                            onClick={() => {
                                                setActiveTab('tracking');
                                                setActiveSubTab('track-history');
                                            }}
                                            className="mt-4 w-full rounded-lg bg-slate-100 py-2 text-[10px] font-black tracking-widest text-slate-600 uppercase transition-all hover:bg-slate-200 active:scale-95"
                                        >
                                            Tingnan Lahat
                                        </button>
                                    )}
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            )}

            {/* --- TAB 2: TRACKING --- */}
            {activeTab === 'tracking' && (
                <div className="animate-in fade-in duration-500">
                    <h1 className="mb-6 text-2xl font-bold text-slate-900">Track My Requests</h1>
                    <div className="mb-6 flex gap-2 overflow-x-auto border-b border-slate-100 pb-2">
                        {[
                            {
                                id: 'track-pending',
                                label: 'Pending',
                                count: myRequests.filter((r) => r.status === 'pending').length,
                            },
                            {
                                id: 'track-status',
                                label: 'Status',
                                count: myRequests.filter((r) =>
                                    ['processing', 'for_interview', 'released'].includes(r.status)
                                ).length,
                            },
                            {
                                id: 'track-history',
                                label: 'Received',
                                count: myRequests.filter((r) => r.status === 'received').length,
                            },
                            {
                                id: 'track-rejected',
                                label: 'Rejected / Canceled',
                                count: myRequests.filter((r) =>
                                    ['rejected', 'canceled'].includes(r.status)
                                ).length,
                            },
                        ].map((sub) => (
                            <button
                                key={sub.id}
                                onClick={() => setActiveSubTab(sub.id)}
                                className={`rounded-full px-5 py-2 text-sm font-bold whitespace-nowrap shadow-sm transition-all ${activeSubTab === sub.id ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'}`}
                            >
                                {sub.label} ({sub.count})
                            </button>
                        ))}
                    </div>

                    <div className="space-y-4">
                        {activeSubTab === 'track-pending' &&
                            (myRequests.filter((r) => r.status === 'pending').length > 0 ? (
                                myRequests
                                    .filter((r) => r.status === 'pending')
                                    .map((req) => (
                                        <div
                                            key={req.id}
                                            className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-all hover:shadow-md"
                                        >
                                            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                                <div>
                                                    <div className="mb-2 flex items-center gap-3">
                                                        <span className="text-lg font-black tracking-tighter text-slate-900 uppercase">
                                                            {req.queue_number}
                                                        </span>
                                                        <span className="rounded-md border border-yellow-200 bg-yellow-100 px-2 py-1 text-[10px] font-black tracking-widest text-yellow-700 uppercase shadow-sm">
                                                            {' '}
                                                            {req.status.replace('_', ' ')}{' '}
                                                        </span>
                                                    </div>
                                                    <p className="text-sm font-bold text-slate-800">
                                                        {req.document_type?.name ?? 'Dokumento'}
                                                    </p>
                                                    <p className="mt-1 text-xs text-slate-500">
                                                        <span className="font-semibold">
                                                            Layunin:
                                                        </span>{' '}
                                                        {req.purpose}
                                                    </p>
                                                    <p className="text-xs text-slate-500">
                                                        <span className="font-semibold">
                                                            Petsa:
                                                        </span>{' '}
                                                        {new Date(req.created_at).toLocaleString()}
                                                    </p>
                                                </div>
                                                <Link
                                                    href={route('resident.request.cancel', req.id)}
                                                    method="post"
                                                    as="button"
                                                    onBefore={() =>
                                                        confirm(
                                                            'Sigurado kang gusto mong i-cancel ang request na ito?'
                                                        )
                                                    }
                                                    className="flex items-center justify-center gap-2 rounded-lg border border-red-200 bg-red-50 px-6 py-3 text-[10px] font-black tracking-widest text-red-600 uppercase shadow-sm transition-all hover:bg-red-100 active:scale-95"
                                                >
                                                    <svg
                                                        className="h-4 w-4"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path
                                                            strokeLinecap="round"
                                                            strokeLinejoin="round"
                                                            strokeWidth="2"
                                                            d="M6 18L18 6M6 6l12 12"
                                                        ></path>
                                                    </svg>
                                                    Cancel Request
                                                </Link>
                                            </div>
                                        </div>
                                    ))
                            ) : (
                                <p className="rounded-xl bg-slate-50 py-10 text-center font-bold text-slate-500 italic">
                                    Wala kang pending na request.
                                </p>
                            ))}
                        {activeSubTab === 'track-status' &&
                            (myRequests.filter((r) =>
                                ['processing', 'for_interview', 'released'].includes(r.status)
                            ).length > 0 ? (
                                myRequests
                                    .filter((r) =>
                                        ['processing', 'for_interview', 'released'].includes(
                                            r.status
                                        )
                                    )
                                    .map((req) => (
                                        <div
                                            key={req.id}
                                            className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-all hover:shadow-md"
                                        >
                                            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                                <div>
                                                    <div className="mb-2 flex items-center gap-3">
                                                        <span className="text-lg font-black tracking-tighter text-slate-900 uppercase">
                                                            {req.queue_number}
                                                        </span>
                                                        <span
                                                            className={`rounded-md px-2 py-1 text-[10px] font-black tracking-widest uppercase shadow-sm ${req.status === 'processing' ? 'border border-blue-200 bg-blue-100 text-blue-700' : ''} ${req.status === 'for_interview' ? 'border border-purple-200 bg-purple-100 text-purple-700' : ''} ${req.status === 'released' ? 'border border-orange-200 bg-orange-100 text-orange-700' : ''}`}
                                                        >
                                                            {req.status.replace('_', ' ')}
                                                        </span>
                                                    </div>
                                                    <p className="text-sm font-bold text-slate-800">
                                                        {req.document_type?.name ?? 'Dokumento'}
                                                    </p>
                                                    <p className="mt-1 text-xs text-slate-500">
                                                        <span className="font-semibold">
                                                            Layunin:
                                                        </span>{' '}
                                                        {req.purpose}
                                                    </p>
                                                    <p className="text-xs text-slate-500">
                                                        <span className="font-semibold">
                                                            Petsa:
                                                        </span>{' '}
                                                        {new Date(req.created_at).toLocaleString()}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    ))
                            ) : (
                                <p className="rounded-xl bg-slate-50 py-10 text-center font-bold text-slate-500 italic">
                                    Walang request na pinoproseso sa ngayon.
                                </p>
                            ))}
                        {activeSubTab === 'track-history' &&
                            (myRequests.filter((r) => r.status === 'received').length > 0 ? (
                                myRequests
                                    .filter((r) => r.status === 'received')
                                    .map((req) => (
                                        <div
                                            key={req.id}
                                            className="rounded-xl border border-slate-200 bg-slate-50 p-5 opacity-80 transition-all hover:opacity-100"
                                        >
                                            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                                <div>
                                                    <div className="mb-2 flex items-center gap-3">
                                                        <span className="text-lg font-black tracking-tighter text-slate-600 uppercase">
                                                            {req.queue_number}
                                                        </span>
                                                        <span className="rounded-md border border-green-200 bg-green-100 px-2 py-1 text-[10px] font-black tracking-widest text-green-700 uppercase shadow-sm">
                                                            {' '}
                                                            {req.status}{' '}
                                                        </span>
                                                    </div>
                                                    <p className="text-sm font-bold text-slate-700">
                                                        {req.document_type?.name ?? 'Dokumento'}
                                                    </p>
                                                    <p className="mt-1 text-xs text-slate-500">
                                                        <span className="font-semibold">
                                                            Petsa:
                                                        </span>{' '}
                                                        {new Date(req.created_at).toLocaleString()}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    ))
                            ) : (
                                <p className="rounded-xl bg-slate-50 py-10 text-center font-bold text-slate-500 italic">
                                    Wala kang nakaraang transaksyon.
                                </p>
                            ))}
                        {activeSubTab === 'track-rejected' &&
                            (myRequests.filter((r) => ['rejected', 'canceled'].includes(r.status))
                                .length > 0 ? (
                                myRequests
                                    .filter((r) => ['rejected', 'canceled'].includes(r.status))
                                    .map((req) => (
                                        <div
                                            key={req.id}
                                            className="rounded-xl border border-red-200 bg-red-50 p-5 transition-all"
                                        >
                                            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                                <div>
                                                    <div className="mb-2 flex items-center gap-3">
                                                        <span className="text-lg font-black tracking-tighter text-slate-600 uppercase">
                                                            {req.queue_number}
                                                        </span>
                                                        <span className="rounded-md border border-red-300 bg-red-100 px-2 py-1 text-[10px] font-black tracking-widest text-red-700 uppercase shadow-sm">
                                                            {' '}
                                                            {req.status}{' '}
                                                        </span>
                                                    </div>
                                                    <p className="text-sm font-bold text-red-900">
                                                        {req.document_type?.name ?? 'Dokumento'}
                                                    </p>
                                                    <p className="mt-1 text-xs text-red-700">
                                                        <span className="font-semibold">
                                                            Layunin:
                                                        </span>{' '}
                                                        {req.purpose}
                                                    </p>
                                                    <p className="text-xs text-red-700">
                                                        <span className="font-semibold">
                                                            Petsa:
                                                        </span>{' '}
                                                        {new Date(req.created_at).toLocaleString()}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    ))
                            ) : (
                                <p className="rounded-xl bg-slate-50 py-10 text-center font-bold text-slate-500 italic">
                                    Wala kang rejected o canceled na request.
                                </p>
                            ))}
                    </div>
                </div>
            )}

            {/* --- TAB 3: SETTINGS (FULLY IMPLEMENTED) --- */}
            {activeTab === 'settings' && (
                <div className="animate-in fade-in duration-500">
                    <h1 className="mb-6 text-2xl font-bold text-slate-900">Account Settings</h1>

                    <div className="space-y-8 rounded-2xl border border-slate-100 bg-white p-6 shadow-sm md:p-8">
                        {/* Contact Number Section */}
                        <div>
                            <h2 className="mb-3 text-lg font-bold text-slate-900">
                                Primary Contact Number
                            </h2>
                            <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
                                <div className="block w-full cursor-not-allowed rounded-lg border border-slate-200 bg-slate-50 p-2.5 text-sm font-bold text-slate-700 sm:w-80">
                                    {auth?.user?.contact_number}
                                </div>
                                {auth?.user?.contact_verified_at ? (
                                    <>
                                        <span className="inline-flex w-full items-center justify-center gap-1 rounded-full bg-green-100 px-3 py-1.5 text-xs font-bold text-green-700 sm:w-auto">
                                            <svg
                                                className="h-4 w-4"
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
                                            </svg>{' '}
                                            Verified
                                        </span>
                                        {new Date() >
                                        new Date(
                                            new Date(auth.user.contact_verified_at).setDate(
                                                new Date(auth.user.contact_verified_at).getDate() +
                                                    30
                                            )
                                        ) ? (
                                            <button
                                                onClick={() => setSettingsModal('changeContact')}
                                                className="w-full rounded-lg border border-slate-300 bg-white px-6 py-2.5 text-sm font-bold text-slate-700 transition-all hover:bg-slate-50 active:scale-95 sm:w-auto"
                                            >
                                                Palitan
                                            </button>
                                        ) : (
                                            <span className="flex items-center justify-center gap-1 text-[10px] font-bold text-slate-400 italic sm:justify-start">
                                                Locked ng 30 Araw
                                            </span>
                                        )}
                                    </>
                                ) : (
                                    <>
                                        <span className="inline-flex w-full items-center justify-center gap-1 rounded-full bg-amber-100 px-3 py-1.5 text-xs font-bold text-amber-700 sm:w-auto">
                                            Unverified
                                        </span>
                                        <button
                                            onClick={() => setSettingsModal('verifyContact')}
                                            className="w-full animate-pulse rounded-lg bg-red-600 px-6 py-2.5 text-sm font-bold text-white transition-all hover:bg-red-700 active:scale-95 sm:w-auto"
                                        >
                                            Verify OTP
                                        </button>
                                        <button
                                            onClick={() => setSettingsModal('changeContact')}
                                            className="w-full rounded-lg border border-slate-300 bg-white px-6 py-2.5 text-sm font-bold text-slate-700 transition-all hover:bg-slate-50 active:scale-95 sm:w-auto"
                                        >
                                            Palitan
                                        </button>
                                    </>
                                )}
                            </div>
                            <p className="mt-2 text-xs text-slate-500">
                                Ito ang iyong login ID at channel para sa SMS updates.
                            </p>
                        </div>

                        <hr className="border-slate-100" />

                        {/* Email Address Section */}
                        <div>
                            <h2 className="mb-3 text-lg font-bold text-slate-900">Email Address</h2>
                            <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
                                <div
                                    className={`block w-full cursor-not-allowed rounded-lg border border-slate-200 bg-slate-50 p-2.5 text-sm sm:w-80 ${auth?.user?.email ? 'font-bold text-slate-700' : 'text-slate-400 italic'}`}
                                >
                                    {auth?.user?.email || 'Walang nakarehistrong email.'}
                                </div>
                                {auth?.user?.email ? (
                                    auth?.user?.email_verified_at ? (
                                        <>
                                            <span className="inline-flex w-full items-center justify-center gap-1 rounded-full bg-green-100 px-3 py-1.5 text-xs font-bold text-green-700 sm:w-auto">
                                                <svg
                                                    className="h-4 w-4"
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
                                                </svg>{' '}
                                                Verified
                                            </span>
                                            {new Date() >
                                            new Date(
                                                new Date(auth.user.email_verified_at).setDate(
                                                    new Date(
                                                        auth.user.email_verified_at
                                                    ).getDate() + 30
                                                )
                                            ) ? (
                                                <button
                                                    onClick={() => setSettingsModal('changeEmail')}
                                                    className="w-full rounded-lg border border-slate-300 bg-white px-6 py-2.5 text-sm font-bold text-slate-700 transition-all hover:bg-slate-50 active:scale-95 sm:w-auto"
                                                >
                                                    Palitan
                                                </button>
                                            ) : (
                                                <span className="flex items-center justify-center gap-1 text-[10px] font-bold text-slate-400 italic sm:justify-start">
                                                    Locked ng 30 Araw
                                                </span>
                                            )}
                                        </>
                                    ) : (
                                        <>
                                            <span className="inline-flex w-full items-center justify-center gap-1 rounded-full bg-amber-100 px-3 py-1.5 text-xs font-bold text-amber-700 sm:w-auto">
                                                Unverified
                                            </span>
                                            <button
                                                onClick={() => setSettingsModal('verifyEmail')}
                                                className="w-full animate-pulse rounded-lg bg-red-600 px-6 py-2.5 text-sm font-bold text-white transition-all hover:bg-red-700 active:scale-95 sm:w-auto"
                                            >
                                                Verify OTP
                                            </button>
                                        </>
                                    )
                                ) : (
                                    <button
                                        onClick={() => setSettingsModal('changeEmail')}
                                        className="w-full rounded-lg bg-slate-900 px-6 py-2.5 text-sm font-bold text-white transition-all hover:bg-slate-800 active:scale-95 sm:w-auto"
                                    >
                                        Magdagdag ng Email
                                    </button>
                                )}
                            </div>
                            <p className="mt-2 text-xs text-slate-500">
                                Optional: Para makatanggap ng kopya ng digital receipts.
                            </p>
                        </div>

                        {/* Notification Preferences */}
                        <div className="mt-8 border-t border-slate-100 pt-6">
                            <h2 className="mb-4 text-lg font-bold text-slate-900">
                                Notification Preferences
                            </h2>
                            <div className="space-y-3">
                                <label className="flex cursor-not-allowed items-center justify-between rounded-xl border border-slate-200 bg-slate-50 p-4 opacity-80">
                                    <div>
                                        <p className="text-sm font-bold text-slate-800">
                                            SMS Notifications
                                        </p>
                                        <p className="text-xs text-slate-500">
                                            Pangunahing channel para sa updates (Required).
                                        </p>
                                    </div>
                                    <div className="relative inline-flex cursor-not-allowed items-center">
                                        <input
                                            type="checkbox"
                                            checked
                                            disabled
                                            className="peer sr-only"
                                        />
                                        <div className="h-6 w-11 rounded-full bg-green-600 after:absolute after:top-[2px] after:left-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-white after:bg-white after:transition-all after:content-[''] peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                                    </div>
                                </label>

                                {auth?.user?.email_verified_at ? (
                                    <label className="flex cursor-pointer items-center justify-between rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition-all hover:bg-slate-50">
                                        <div>
                                            <p className="text-sm font-bold text-slate-800">
                                                Email / Digital Receipts
                                            </p>
                                            <p className="text-xs text-slate-500">
                                                Makatanggap ng kopya ng updates sa email.
                                            </p>
                                        </div>
                                        <div className="relative inline-flex cursor-pointer items-center">
                                            <input
                                                type="checkbox"
                                                onChange={toggleEmailPreference}
                                                checked={auth?.user?.wants_email_notification}
                                                className="peer sr-only"
                                            />
                                            <div className="h-6 w-11 rounded-full bg-red-500 transition-colors peer-checked:bg-green-600 after:absolute after:top-[2px] after:left-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:content-[''] peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                                        </div>
                                    </label>
                                ) : (
                                    <label className="flex cursor-not-allowed items-center justify-between rounded-xl border border-slate-200 bg-slate-50 p-4 opacity-80">
                                        <div>
                                            <p className="text-sm font-bold text-slate-800">
                                                Email / Digital Receipts
                                            </p>
                                            <p className="mt-1 text-xs font-bold text-amber-600">
                                                ⚠️ I-verify muna ang iyong email sa itaas upang
                                                magamit ito.
                                            </p>
                                        </div>
                                        <div className="relative inline-flex cursor-not-allowed items-center opacity-60">
                                            <input
                                                type="checkbox"
                                                disabled
                                                className="peer sr-only"
                                            />
                                            <div className="h-6 w-11 rounded-full bg-slate-400 after:absolute after:top-[2px] after:left-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:content-['']"></div>
                                        </div>
                                    </label>
                                )}
                            </div>
                        </div>

                        <hr className="border-slate-100" />

                        {/* Security Form */}
                        <div>
                            <h2 className="mb-4 text-lg font-bold text-slate-900">
                                Security Settings
                            </h2>
                            <form onSubmit={submitPasswordUpdate} className="max-w-md space-y-4">
                                <div>
                                    <label className="mb-1 block text-sm font-bold text-slate-700">
                                        Kasalukuyang Password
                                    </label>
                                    <input
                                        type="password"
                                        value={passwordForm.data.current_password}
                                        onChange={(e) =>
                                            passwordForm.setData('current_password', e.target.value)
                                        }
                                        required
                                        className={`block w-full rounded-lg border bg-slate-50 p-2.5 text-sm text-slate-900 transition-all outline-none focus:ring-2 focus:ring-slate-900 ${passwordForm.errors.current_password ? 'border-red-500 ring-1 ring-red-500' : 'border-slate-300'}`}
                                    />
                                    {passwordForm.errors.current_password && (
                                        <p className="mt-1 text-xs text-red-500">
                                            {passwordForm.errors.current_password}
                                        </p>
                                    )}
                                </div>
                                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <label className="mb-1 block text-sm font-bold text-slate-700">
                                            Bagong Password
                                        </label>
                                        <input
                                            type="password"
                                            value={passwordForm.data.password}
                                            onChange={(e) =>
                                                passwordForm.setData('password', e.target.value)
                                            }
                                            minLength="8"
                                            required
                                            className={`block w-full rounded-lg border bg-slate-50 p-2.5 text-sm text-slate-900 transition-all outline-none focus:ring-2 focus:ring-slate-900 ${passwordForm.errors.password ? 'border-red-500 ring-1 ring-red-500' : 'border-slate-300'}`}
                                        />
                                        {passwordForm.errors.password && (
                                            <p className="mt-1 text-xs text-red-500">
                                                {passwordForm.errors.password}
                                            </p>
                                        )}
                                    </div>
                                    <div>
                                        <label className="mb-1 block text-sm font-bold text-slate-700">
                                            Confirm Password
                                        </label>
                                        <input
                                            type="password"
                                            value={passwordForm.data.password_confirmation}
                                            onChange={(e) =>
                                                passwordForm.setData(
                                                    'password_confirmation',
                                                    e.target.value
                                                )
                                            }
                                            minLength="8"
                                            required
                                            className="block w-full rounded-lg border border-slate-300 bg-slate-50 p-2.5 text-sm text-slate-900 transition-all outline-none focus:ring-2 focus:ring-slate-900"
                                        />
                                    </div>
                                </div>
                                <button
                                    type="submit"
                                    disabled={passwordForm.processing}
                                    className="w-full rounded-lg bg-slate-900 px-8 py-3 text-xs font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-slate-800 active:scale-95 disabled:opacity-50 sm:w-auto"
                                >
                                    {passwordForm.processing ? 'Saving...' : 'I-save ang Password'}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            )}

            {/* --- SETTINGS MODALS --- */}
            {/* 1. Change Contact */}
            {settingsModal === 'changeContact' && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/80 p-4 backdrop-blur-sm">
                    <div className="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl">
                        <h3 className="mb-2 text-lg font-black text-slate-900 uppercase">
                            Palitan ang Numero
                        </h3>
                        <p className="mb-6 text-sm text-slate-500">
                            I-type ang iyong bagong 11-digit mobile number.
                        </p>
                        <form onSubmit={submitContactUpdate}>
                            <input
                                type="tel"
                                value={contactForm.data.contact_number}
                                onChange={(e) =>
                                    contactForm.setData(
                                        'contact_number',
                                        e.target.value.replace(/[^0-9]/g, '')
                                    )
                                }
                                required
                                pattern="09[1, 4-11]{9}"
                                maxLength="11"
                                placeholder="09XXXXXXXXX"
                                className="mb-6 w-full rounded-lg border border-slate-300 bg-slate-50 p-3 text-center font-mono text-xl font-bold tracking-widest outline-none focus:ring-2 focus:ring-slate-900"
                            />
                            {contactForm.errors.contact_number && (
                                <p className="-mt-4 mb-4 text-center text-xs font-bold text-red-500">
                                    {contactForm.errors.contact_number}
                                </p>
                            )}
                            <div className="flex gap-2">
                                <button
                                    type="button"
                                    onClick={() => {
                                        setSettingsModal(null);
                                        contactForm.reset();
                                    }}
                                    className="flex-1 rounded-xl bg-slate-200 py-3 text-xs font-black tracking-widest text-slate-700 uppercase transition-all hover:bg-slate-300 active:scale-95"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    disabled={contactForm.processing}
                                    className="flex-1 rounded-xl bg-slate-900 py-3 text-xs font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-slate-800 active:scale-95 disabled:opacity-50"
                                >
                                    I-Save
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* 2. Verify Contact OTP */}
            {settingsModal === 'verifyContact' && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/80 p-4 backdrop-blur-sm">
                    <div className="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl">
                        <h3 className="mb-2 text-lg font-black text-slate-900 uppercase">
                            Verify Number
                        </h3>
                        <p className="mb-4 text-sm text-slate-500">
                            I-enter ang 6-digit OTP na ipinadala sa {auth?.user?.contact_number}.
                        </p>
                        {errors.otp_error && (
                            <div className="mb-4 rounded-lg bg-red-50 p-3 text-xs font-bold text-red-600">
                                {errors.otp_error}
                            </div>
                        )}
                        {errors.contact_number && (
                            <div className="mb-4 rounded-lg bg-red-50 p-3 text-xs font-bold text-red-600">
                                {errors.contact_number}
                            </div>
                        )}

                        <form onSubmit={submitVerifyContact}>
                            <input
                                type="text"
                                value={verifyContactForm.data.otp_code}
                                onChange={(e) =>
                                    verifyContactForm.setData(
                                        'otp_code',
                                        e.target.value.replace(/[^0-9]/g, '')
                                    )
                                }
                                maxLength="6"
                                required
                                placeholder="000000"
                                className={`mb-4 w-full rounded-lg border ${errors.otp_error ? 'border-red-500 ring-1 ring-red-500' : 'border-slate-300'} bg-slate-50 p-3 text-center font-mono text-3xl font-bold tracking-[0.3em] outline-none focus:ring-2 focus:ring-slate-900`}
                            />
                            <div className="mb-4 flex gap-2">
                                <button
                                    type="button"
                                    onClick={() => {
                                        setSettingsModal(null);
                                        verifyContactForm.reset();
                                    }}
                                    className="flex-1 rounded-xl bg-slate-200 py-3 text-xs font-black tracking-widest text-slate-700 uppercase transition-all hover:bg-slate-300 active:scale-95"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    disabled={verifyContactForm.processing}
                                    className="flex-1 rounded-xl bg-red-600 py-3 text-xs font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-red-700 active:scale-95 disabled:opacity-50"
                                >
                                    Verify OTP
                                </button>
                            </div>
                        </form>
                        <div className="border-t border-slate-100 pt-4 text-center">
                            <button
                                onClick={resendContactOtp}
                                disabled={contactTimer > 0}
                                className="text-xs font-bold text-slate-500 transition-all hover:text-slate-900 hover:underline disabled:cursor-not-allowed disabled:no-underline"
                            >
                                Resend SMS Code{' '}
                                {contactTimer > 0 && (
                                    <span className="font-mono text-red-600">
                                        ({contactTimer}s)
                                    </span>
                                )}
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* 3. Change Email */}
            {settingsModal === 'changeEmail' && (
                <div className="fixed inset-0 z-[3] flex items-center justify-center bg-slate-900/80 p-4 backdrop-blur-sm">
                    <div className="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl">
                        <h3 className="mb-2 text-lg font-black text-slate-900 uppercase">
                            I-setup ang Email
                        </h3>
                        <p className="mb-6 text-sm text-slate-500">
                            Makatatanggap ka ng verification code sa email na ito.
                        </p>
                        <form onSubmit={submitEmailUpdate}>
                            <input
                                type="email"
                                value={emailForm.data.new_email}
                                onChange={(e) => emailForm.setData('new_email', e.target.value)}
                                required
                                placeholder="juan@email.com"
                                className="mb-6 w-full rounded-lg border border-slate-300 bg-slate-50 p-3 text-sm outline-none focus:ring-2 focus:ring-slate-900"
                            />
                            {emailForm.errors.new_email && (
                                <p className="-mt-4 mb-4 text-center text-xs font-bold text-red-500">
                                    {emailForm.errors.new_email}
                                </p>
                            )}
                            <div className="flex gap-2">
                                <button
                                    type="button"
                                    onClick={() => {
                                        setSettingsModal(null);
                                        emailForm.reset();
                                    }}
                                    className="flex-1 rounded-xl bg-slate-200 py-3 text-xs font-black tracking-widest text-slate-700 uppercase transition-all hover:bg-slate-300 active:scale-95"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    disabled={emailForm.processing}
                                    className="flex-1 rounded-xl bg-slate-900 py-3 text-xs font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-slate-800 active:scale-95 disabled:opacity-50"
                                >
                                    I-Save
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* 4. Verify Email OTP */}
            {settingsModal === 'verifyEmail' && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/80 p-4 backdrop-blur-sm">
                    <div className="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl">
                        <h3 className="mb-2 text-lg font-black text-slate-900 uppercase">
                            Verify Email
                        </h3>
                        <p className="mb-4 text-sm text-slate-500">
                            I-enter ang 6-digit code na ipinadala sa iyong email.
                        </p>
                        {errors.email_otp && (
                            <div className="mb-4 rounded-lg bg-red-50 p-3 text-xs font-bold text-red-600">
                                {errors.email_otp}
                            </div>
                        )}

                        <form onSubmit={submitVerifyEmail}>
                            <input
                                type="text"
                                value={verifyEmailForm.data.email_otp}
                                onChange={(e) =>
                                    verifyEmailForm.setData(
                                        'email_otp',
                                        e.target.value.replace(/[^0-9]/g, '')
                                    )
                                }
                                maxLength="6"
                                required
                                placeholder="000000"
                                className={`mb-4 w-full rounded-lg border ${errors.email_otp ? 'border-red-500 ring-1 ring-red-500' : 'border-slate-300'} bg-slate-50 p-3 text-center font-mono text-3xl font-bold tracking-[0.3em] outline-none focus:ring-2 focus:ring-slate-900`}
                            />
                            <div className="mb-4 flex gap-2">
                                <button
                                    type="button"
                                    onClick={() => {
                                        setSettingsModal(null);
                                        verifyEmailForm.reset();
                                    }}
                                    className="flex-1 rounded-xl bg-slate-200 py-3 text-xs font-black tracking-widest text-slate-700 uppercase transition-all hover:bg-slate-300 active:scale-95"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    disabled={verifyEmailForm.processing}
                                    className="flex-1 rounded-xl bg-red-600 py-3 text-xs font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-red-700 active:scale-95 disabled:opacity-50"
                                >
                                    Verify OTP
                                </button>
                            </div>
                        </form>
                        <div className="border-t border-slate-100 pt-4 text-center">
                            <button
                                onClick={resendEmailOtp}
                                disabled={emailTimer > 0}
                                className="text-xs font-bold text-slate-500 transition-all hover:text-slate-900 hover:underline disabled:cursor-not-allowed disabled:no-underline"
                            >
                                Resend Email Code{' '}
                                {emailTimer > 0 && (
                                    <span className="font-mono text-red-600">({emailTimer}s)</span>
                                )}
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* --- REQUEST MODALS --- */}
            <RequestModal
                isOpen={isRequestModalOpen}
                onClose={() => setIsRequestModalOpen(false)}
                documents={documents}
                auth={auth}
            />
            {isLiveKycModalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/90 p-4 backdrop-blur-sm transition-opacity">
                    <div className="w-full max-w-md rounded-2xl bg-white p-6 text-center shadow-2xl">
                        <h3 className="mb-4 text-xl font-black text-slate-900 uppercase">
                            Live Camera Scanner
                        </h3>

                        {!isScanning && !previewImage && (
                            <div className="mb-6">
                                <button
                                    type="button"
                                    onClick={() => setIsScanning(true)}
                                    className="w-full rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 py-12 text-center font-bold text-slate-500 transition-all hover:bg-slate-100 active:scale-95"
                                >
                                    <div className="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-slate-200 text-slate-500">
                                        <svg
                                            className="h-10 w-10"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth="2"
                                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"
                                            ></path>
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth="2"
                                                d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"
                                            ></path>
                                        </svg>
                                    </div>
                                    Buksan ang Live Scanner
                                </button>
                            </div>
                        )}

                        {isScanning && (
                            <div className="relative mb-6 overflow-hidden rounded-2xl border-2 border-slate-800 bg-black shadow-xl">
                                <Webcam
                                    audio={false}
                                    ref={webcamRef}
                                    screenshotFormat="image/jpeg"
                                    videoConstraints={{ facingMode: 'environment' }}
                                    className="w-full object-cover opacity-80"
                                />
                                <div className="pointer-events-none absolute inset-0 flex items-center justify-center">
                                    <div className="h-3/5 w-4/5 rounded-xl border-2 border-white/60 shadow-[0_0_0_9999px_rgba(0,0,0,0.6)]"></div>
                                </div>
                                <button
                                    onClick={captureId}
                                    className="absolute bottom-4 left-1/2 -translate-x-1/2 rounded-full bg-white px-6 py-3 font-black text-slate-900 uppercase shadow-xl transition-all hover:bg-slate-200 active:scale-95"
                                >
                                    📸 Capture
                                </button>
                            </div>
                        )}

                        {previewImage && (
                            <div className="mb-6">
                                <div className="relative overflow-hidden rounded-2xl border-2 border-slate-800 shadow-xl">
                                    <img
                                        src={previewImage}
                                        alt="Captured ID"
                                        className="w-full object-cover"
                                    />
                                </div>
                                <div className="mt-4 flex gap-2">
                                    <button
                                        type="button"
                                        onClick={() => {
                                            setPreviewImage(null);
                                            setIsScanning(true);
                                        }}
                                        className="flex-1 rounded-xl bg-slate-200 py-3 text-xs font-black tracking-widest text-slate-700 uppercase transition-all hover:bg-slate-300 active:scale-95"
                                    >
                                        Ulitin ang Pag-scan
                                    </button>
                                    <button
                                        type="button"
                                        disabled={kycForm.processing}
                                        onClick={() => kycForm.post(route('resident.verify_id'))}
                                        className="flex-1 rounded-xl bg-green-600 py-3 text-xs font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-green-700 active:scale-95 disabled:opacity-50"
                                    >
                                        {kycForm.processing ? 'Sino-submit...' : 'I-submit ang ID'}
                                    </button>
                                </div>
                            </div>
                        )}

                        <button
                            onClick={() => {
                                setIsLiveKycModalOpen(false);
                                setIsScanning(false);
                                setPreviewImage(null);
                            }}
                            className="w-full rounded-xl bg-red-600 px-8 py-3 text-sm font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-red-700 active:scale-95"
                        >
                            Isara muna
                        </button>
                    </div>
                </div>
            )}
        </ResidentLayout>
    );
}
