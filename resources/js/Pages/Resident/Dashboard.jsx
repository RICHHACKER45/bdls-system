import React, { useState, useEffect, useRef, useCallback } from 'react';
import { Head, Link, useForm, usePage, router } from '@inertiajs/react';
import Webcam from 'react-webcam';
import ResidentLayout from '@/Layouts/ResidentLayout';

// ==========================================
// MODAL: CREATE SERVICE REQUEST
// ==========================================
const RequestModal = ({
    isOpen,
    onClose,
    documents,
    auth,
    activeQueueCount,
    currentBacklogMinutes,
}) => {
    const [requirements, setRequirements] = useState('');
    const [fee, setFee] = useState(0);

    const { data, setData, post, processing, errors, reset, clearErrors } = useForm({
        document_type_id: '',
        purpose: '',
        additional_details: '',
        payment_method: 'Cash',
        payment_receipt_path: null,
    });

    const handleDocumentChange = (id) => {
        const doc = documents.find((d) => d.id === parseInt(id));
        setData('document_type_id', id);

        if (doc) {
            setRequirements(doc.requirements_description);
            setFee(parseFloat(doc.processing_fee || 0));
        } else {
            setRequirements('');
            setFee(0);
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
                                            Mga Kinakailangang Dalhin sa Barangay:
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
                                Karagdagang Detalye (Optional)
                            </label>
                            <textarea
                                value={data.additional_details}
                                onChange={(e) => setData('additional_details', e.target.value)}
                                rows="2"
                                className="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 transition-all outline-none focus:ring-2 focus:ring-slate-900"
                            ></textarea>
                        </div>

                        <div className="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 p-4 shadow-inner">
                            <div className="flex items-center justify-between">
                                <div className="flex items-center gap-3">
                                    <div className="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
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
                                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            ></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p className="text-sm font-bold text-emerald-900">
                                            Processing Fee
                                        </p>
                                        <p className="text-[11px] font-medium text-emerald-600">
                                            Standard Barangay Document Rate
                                        </p>
                                    </div>
                                </div>
                                <div className="text-right">
                                    <span className="text-2xl font-black tracking-tight text-emerald-700">
                                        ₱{fee.toFixed(2)}
                                    </span>
                                    <p className="text-[9px] font-black tracking-widest text-emerald-600 uppercase">
                                        {fee === 0 ? 'Libre' : 'May Bayad'}
                                    </p>
                                </div>
                            </div>
                            <p className="mt-3 border-t border-emerald-200/60 pt-2 text-[10px] font-bold text-emerald-700 italic">
                                {fee === 0
                                    ? '* Ang pagkuha ng mga dokumento sa Barangay Doña Lucia ay kasalukuyang walang bayad.'
                                    : '* Mangyaring ihanda ang eksaktong halaga pagpunta sa barangay hall.'}
                            </p>
                        </div>

                        {/* HYBRID PAYMENT SYSTEM UI */}
                        {fee > 0 && (
                            <div className="mt-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                                <label className="mb-2 block text-sm font-bold text-slate-800">
                                    Paraan ng Pagbabayad <span className="text-red-500">*</span>
                                </label>
                                <select
                                    value={data.payment_method}
                                    onChange={(e) => setData('payment_method', e.target.value)}
                                    className="w-full cursor-pointer rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 transition-all outline-none focus:ring-2 focus:ring-slate-900"
                                >
                                    <option value="Cash">Cash (Mismong sa Barangay Hall)</option>
                                    <option value="GCash">GCash (Online Payment)</option>
                                </select>

                                {data.payment_method === 'GCash' && (
                                    <div className="animate-in fade-in mt-4 rounded-xl border border-blue-200 bg-blue-50 p-4">
                                        <p className="mb-3 text-center text-xs font-bold text-blue-900">
                                            I-scan ang QR Code o i-send ang bayad sa: <br />
                                            <span className="text-lg font-black tracking-widest text-slate-900">
                                                0912 345 6789
                                            </span>{' '}
                                            <br />
                                            <span className="text-[10px] text-blue-700 uppercase">
                                                Juan Dela Cruz - Brgy. Treasurer
                                            </span>
                                        </p>

                                        {/* Placeholder for QR Code */}
                                        <div className="mx-auto mb-4 flex h-32 w-32 items-center justify-center rounded-lg border-2 border-dashed border-blue-300 bg-white text-blue-400 shadow-sm">
                                            <span className="text-xs font-bold">[ GCASH QR ]</span>
                                        </div>

                                        <label className="mb-2 block text-xs font-bold text-slate-800">
                                            I-upload ang Screenshot ng Resibo{' '}
                                            <span className="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="file"
                                            accept="image/png, image/jpeg, image/jpg"
                                            onChange={(e) =>
                                                setData('payment_receipt_path', e.target.files)
                                            }
                                            required={data.payment_method === 'GCash'}
                                            className={`w-full cursor-pointer rounded-lg border bg-white px-3 py-2 text-sm file:mr-4 file:cursor-pointer file:rounded-md file:border-0 file:bg-blue-100 file:px-4 file:py-2 file:text-xs file:font-bold file:text-blue-700 hover:file:bg-blue-200 ${errors.payment_receipt_path ? 'border-red-500 ring-1 ring-red-500' : 'border-slate-300'}`}
                                        />
                                        {errors.payment_receipt_path && (
                                            <p className="mt-1 text-xs font-bold text-red-500">
                                                {errors.payment_receipt_path}
                                            </p>
                                        )}
                                    </div>
                                )}
                            </div>
                        )}

                        {/* DYNAMIC ESTIMATED WAITING TIME INDICATOR */}
                        {data.document_type_id && (
                            <div className="mt-4 rounded-xl border border-blue-200 bg-blue-50 p-4 shadow-inner">
                                <div className="flex items-start gap-3">
                                    <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-600">
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
                                    </div>
                                    <div>
                                        <p className="text-sm font-bold text-blue-900">
                                            Estimated Waiting Time
                                        </p>
                                        <p className="mt-1 text-[11px] font-medium text-blue-700">
                                            Tinatayang aabutin ng{' '}
                                            <span className="font-black text-blue-900">
                                                {(() => {
                                                    const docTime =
                                                        documents.find(
                                                            (d) =>
                                                                d.id ===
                                                                parseInt(data.document_type_id)
                                                        )?.processing_time_minutes || 0;
                                                    const maxMins = currentBacklogMinutes + docTime;
                                                    const minMins = Math.max(
                                                        15,
                                                        Math.floor(maxMins / 2)
                                                    );

                                                    const formatTime = (m) => {
                                                        const h = Math.floor(m / 60);
                                                        const r = m % 60;
                                                        if (h > 0)
                                                            return r > 0
                                                                ? `${h} hr at ${r} mins`
                                                                : `${h} hr`;
                                                        return `${m} mins`;
                                                    };

                                                    return `${formatTime(minMins)} - ${formatTime(maxMins)}`;
                                                })()}
                                            </span>{' '}
                                            bago mo makuha ang dokumentong ito.
                                        </p>
                                    </div>
                                </div>
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
// FAQ DATA
// ==========================================
const faqs = [
    { q: "📌 Paano ko malalaman kung ready na ang dokumento ko?", a: 'Pumunta lamang sa "Track Requests" tab. Makakatanggap ka rin ng awtomatikong text message mula sa amin kapag maaari mo na itong kunin sa Barangay Hall.' },
    { q: "📌 Mayroon bang babayaran sa pagkuha ng papel?", a: 'Makikita mo ang "Processing Fee" bago ka mag-submit ng request. Sa kasalukuyan, libre (₱0.00) ang pagkuha ng lahat ng dokumento sa Barangay Doña Lucia.' },
    { q: "📌 Paano kung nagkamali ako sa form na ipinasa ko?", a: 'Kung "Pending" pa lamang ang status, maaari mo itong i-cancel sa Track Requests tab at gumawa ng panibago. Kung "Processing" na, kailangan mong i-contact ang aming Admin.' },
    { q: "📌 Gaano katagal ang proseso ng mga dokumento?", a: 'Depende ito sa uri ng dokumento at sa dami ng nakapila. Halimbawa, ang Barangay Clearance ay karaniwang inaabot ng 30 minuto, habang ang First Time Jobseeker Certification ay maaaring umabot ng 1 oras dahil nangangailangan ito ng panayam at Oath of Undertaking.' },
    { q: "📌 Ano ang mga kailangang dalhin kapag kukunin na ang papel?", a: 'Karaniwan ay Valid ID lamang ang hahanapin. Ngunit para sa ilang dokumento, may karagdagang requirements (hal. RSBSA Form o Titulo ng lupa para sa BARC Certification, o Latest CTC para sa Clearance). Makikita mo ang listahan ng requirements sa mismong form bago ka mag-submit.' },
    { q: "📌 Pwede ba akong kumuha ng Barangay Clearance para sa Business (Mayor's) Permit?", a: 'Hindi na po. Ang pag-isyu ng barangay clearance bilang kinakailangan para sa municipal business permit ay nakatalaga na sa pamahalaang munisipyo at hindi na ini-isyu ng barangay.' },
    { q: "📌 Maaari bang ma-reject ang aking document request?", a: 'Opo. Maaaring ma-reject ang inyong request kung kulang ang iyong requirements, malabo ang in-upload na resibo/ID, may mismatch sa iyong pangalan sa record ng barangay, o kaya ay hindi balido ang ibinigay na dahilan (Purpose). Kung mangyari ito, makikita ninyo ang eksaktong dahilan ng pagkaka-reject sa "Track Requests" tab upang agad itong maitama.' }
];

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
        activeQueueCount = 0,
        currentBacklogMinutes = 0,
    } = usePage().props;

    // Main Tabs State
    const [activeTab, setActiveTab] = useState('dashboard');
    const [activeSubTab, setActiveSubTab] = useState('track-pending');

    // FAQ State
    const [openFaqIndex, setOpenFaqIndex] = useState(null);

    // Modals State
    const [isRequestModalOpen, setIsRequestModalOpen] = useState(false);
    const [isLiveKycModalOpen, setIsLiveKycModalOpen] = useState(false);
    const [isConcernModalOpen, setIsConcernModalOpen] = useState(false);
    const [concernMessage, setConcernMessage] = useState('');
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

    const triggerUnifiedEmail = () => {
        if (!concernMessage.trim()) return;
        const subject = encodeURIComponent("BDLS System Concern");
        const body = encodeURIComponent(concernMessage);
        
        // Detect if the user is on a mobile device
        const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);

        if (isMobile) {
            // Mobile: Force default mail app using invisible anchor
            const mailtoLink = document.createElement('a');
            mailtoLink.href = `mailto:barangaysec@bdlsgov.ph?subject=${subject}&body=${body}`;
            document.body.appendChild(mailtoLink);
            mailtoLink.click();
            document.body.removeChild(mailtoLink);
        } else {
            // Desktop: Force Web Gmail in a new tab
            const gmailUrl = `https://mail.google.com/mail/?view=cm&fs=1&to=barangaysec@bdlsgov.ph&su=${subject}&body=${body}`;
            window.open(gmailUrl, '_blank');
        }
        
        // Slight delay to prevent browser cancellation
        setTimeout(() => {
            setIsConcernModalOpen(false);
            setConcernMessage('');
        }, 800);
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

                                {/* QUICK HELP CARD */}
                                <div 
                                    onClick={() => setActiveTab('support')}
                                    className="rounded-2xl border border-blue-200 bg-blue-50 p-6 shadow-sm transition-all hover:bg-blue-100 cursor-pointer active:scale-95"
                                >
                                    <div className="flex items-center gap-4">
                                        <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-600 shadow-inner">
                                            <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 className="text-sm font-bold text-blue-900">Kailangan ng Tulong?</h3>
                                            <p className="mt-1 text-[11px] font-medium text-blue-700">Pindutin lamang ito para mapunta sa Tulong/Suporta.</p>
                                        </div>
                                    </div>
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
                                            onClick={(e) => {
                                                setSettingsModal('verifyContact');
                                                resendContactOtp(e);
                                            }}
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
                                                onClick={(e) => {
                                                    setSettingsModal('verifyEmail');
                                                    resendEmailOtp(e);
                                                }}
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

            {/* --- TAB 4: HELP & SUPPORT --- */}
            {activeTab === 'support' && (
                <div className="animate-in fade-in duration-500">
                    <h1 className="mb-6 text-2xl font-bold text-slate-900">Tulong at Suporta</h1>

                    <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                        {/* FAQs Section */}
                        <div className="space-y-4 lg:col-span-2">
                            <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm md:p-8">
                                <h2 className="mb-6 text-lg font-black tracking-tight text-slate-900 uppercase">Frequently Asked Questions (FAQs)</h2>
                                <div className="space-y-4">
                                    {faqs.map((faq, index) => (
                                        <div key={index} className="rounded-xl border border-slate-100 bg-slate-50 overflow-hidden transition-all duration-300">
                                            <button
                                                onClick={() => setOpenFaqIndex(openFaqIndex === index ? null : index)}
                                                className="flex w-full items-center justify-between p-5 text-left focus:outline-none"
                                            >
                                                <span className="font-bold text-slate-800">{faq.q}</span>
                                                <span className={`ml-4 shrink-0 transition-transform duration-300 ${openFaqIndex === index ? 'rotate-180' : ''}`}>
                                                    <svg className="h-5 w-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                                                    </svg>
                                                </span>
                                            </button>
                                            <div
                                                className={`transition-all duration-300 ease-in-out ${
                                                    openFaqIndex === index
                                                        ? 'max-h-40 opacity-100 px-5 pb-5'
                                                        : 'max-h-0 opacity-0 px-5 pb-0'
                                                }`}
                                            >
                                                <p className="text-sm leading-relaxed text-slate-600">{faq.a}</p>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>

                        {/* Direct Contact Section */}
                        <div className="space-y-4">
                            <div className="rounded-2xl border border-blue-200 bg-blue-50 p-6 shadow-sm">
                                <div className="mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-blue-100 text-blue-600 shadow-inner">
                                    <svg className="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <h2 className="text-lg font-black tracking-tight text-blue-900 uppercase">May concerns?</h2>
                                <p className="mt-2 text-sm font-medium text-blue-700">Mag-email dito o i-text ang mga number na ito para sa inyong mga katanungan.</p>

                                <div className="mt-8 flex flex-col gap-3">
                                    <button
                                        onClick={() => setIsConcernModalOpen(true)}
                                        className="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-3.5 text-sm font-bold tracking-widest text-white uppercase shadow-md transition-all hover:bg-blue-700 active:scale-95"
                                    >
                                        <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                        Mag-Email Dito
                                    </button>

                                    {/* STATIC NUMBERS LIST */}
                                    <div className="mt-3 rounded-xl border border-blue-200 bg-white p-5 shadow-sm">
                                        <p className="mb-4 text-center text-xs font-black tracking-widest text-slate-500 uppercase">Contact Numbers</p>
                                        <div className="flex flex-col gap-3">
                                            <div className="flex items-center justify-between border-b border-slate-100 pb-2">
                                                <span className="font-bold text-blue-900">Globe / TM</span>
                                                <span className="font-mono text-sm font-black tracking-widest text-slate-700">0917 123 4567</span>
                                            </div>
                                            <div className="flex items-center justify-between border-b border-slate-100 pb-2">
                                                <span className="font-bold text-blue-900">Smart / TNT</span>
                                                <span className="font-mono text-sm font-black tracking-widest text-slate-700">0918 123 4567</span>
                                            </div>
                                            <div className="flex items-center justify-between">
                                                <span className="font-bold text-blue-900">DITO</span>
                                                <span className="font-mono text-sm font-black tracking-widest text-slate-700">0991 123 4567</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                activeQueueCount={activeQueueCount}
                currentBacklogMinutes={currentBacklogMinutes}
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

            {/* ===============
            CUSTOM MODAL: EMAIL CONCERN
            ========================= */}
            {isConcernModalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/80 p-4 backdrop-blur-sm transition-opacity">
                    <div className="w-full max-w-md transform overflow-hidden rounded-2xl border border-blue-100 bg-white p-6 shadow-2xl transition-all">
                        <div className="mb-6 flex flex-col items-center text-center">
                            <div className="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                                <svg className="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <h3 className="text-xl font-black tracking-tight text-slate-900 uppercase">Magpadala ng Email</h3>
                            <p className="mt-2 px-2 text-sm font-medium text-slate-500">
                                I-type ang iyong mensahe sa ibaba. Awtomatiko itong ipapasa sa iyong email app pagka-click ng send.
                            </p>
                        </div>
                        <div className="flex flex-col gap-4">
                            <textarea
                                value={concernMessage}
                                onChange={(e) => setConcernMessage(e.target.value)}
                                rows="5"
                                placeholder="I-type ang iyong katanungan o concern dito bago pumili sa ibaba..."
                                className="w-full resize-none rounded-xl border border-slate-300 bg-slate-50 p-4 text-sm font-medium text-slate-800 transition-all outline-none focus:ring-2 focus:ring-blue-600"
                            ></textarea>
                            
                            {concernMessage.trim() === '' && (
                                <p className="text-center text-xs font-bold text-red-500">
                                    * Paki-type muna ang iyong mensahe sa kahon.
                                </p>
                            )}

                            <div className="mt-2">
                                <button
                                    type="button"
                                    onClick={triggerUnifiedEmail}
                                    disabled={!concernMessage.trim()}
                                    className="w-full rounded-xl bg-blue-600 py-3.5 text-xs font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-blue-700 active:scale-95 disabled:opacity-50"
                                >
                                    Proceed to Email
                                </button>
                            </div>
                            
                            <button
                                type="button"
                                onClick={() => {
                                    setIsConcernModalOpen(false);
                                    setConcernMessage('');
                                }}
                                className="mt-1 w-full rounded-xl bg-slate-200 py-3.5 text-xs font-black tracking-widest text-slate-700 uppercase transition-all hover:bg-slate-300 active:scale-95"
                            >
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </ResidentLayout>
    );
}
