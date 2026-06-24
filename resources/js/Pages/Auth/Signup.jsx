import React, { useState, useRef, useEffect, useCallback } from 'react';
import { Link, Head, useForm } from '@inertiajs/react';
import Webcam from 'react-webcam';
import axios from 'axios';

export default function Signup() {
    // UI States: 1 = Form, 2 = ID Scan, 3 = Terms & Submission
    const [uiView, setUiView] = useState(1);
    const [isValidating, setIsValidating] = useState(false);
    const [previews, setPreviews] = useState({ id: null });
    const [showPassword, setShowPassword] = useState(false);
    const [showConfirmPassword, setShowConfirmPassword] = useState(false);
    const [localErrors, setLocalErrors] = useState({});
    const [toast, setToast] = useState({ visible: false, message: '' });

    // Custom Modal States
    const [isClearModalOpen, setIsClearModalOpen] = useState(false);

    // T&C Scroll State
    const [isScrolledToBottom, setIsScrolledToBottom] = useState(false);

    // WAIT PAGE / PROCESSING STATES
    const [timeLeft, setTimeLeft] = useState(180); // 3 Minutes (180 seconds)
    const [loadingTextIndex, setLoadingTextIndex] = useState(0);

    const idInputRef = useRef(null);

    const { data, setData, post, processing, errors, reset, clearErrors } = useForm({
        first_name: '',
        middle_name: '',
        last_name: '',
        suffix: '',
        sex: '',
        dob_month: '',
        dob_day: '',
        dob_year: '',
        address: '',
        contact_number: '',
        email: '',
        password: '',
        password_confirmation: '',
        id_photo_path: null,
        terms: false,
        ocr_attempt: 1, // 🛡️ BAGONG TRACKER PARA SA 5 ATTEMPTS
        simulate_error: false,
    });

    const loadingMessages = [
        'Ina-upload ang iyong impormasyon at Valid ID...',
        'Ina-analyze ang ID gamit ang AI (Optical Character Recognition)...',
        'Kino-krus-tsek ang pangalan sa Barangay Census Records...',
        'Pina-finalize ang iyong account. Mangyaring maghintay...',
    ];

    useEffect(() => {
        let timerInterval;
        let textInterval;

        if (processing) {
            timerInterval = setInterval(() => {
                setTimeLeft((prev) => (prev > 0 ? prev - 1 : 0));
            }, 1000);

            textInterval = setInterval(() => {
                setLoadingTextIndex((prev) => {
                    if (prev < loadingMessages.length - 1) return prev + 1;
                    return prev;
                });
            }, 4000);
        } else {
            setTimeLeft(180);
            setLoadingTextIndex(0);
        }

        return () => {
            clearInterval(timerInterval);
            clearInterval(textInterval);
        };
    }, [processing]);

    const formatTime = (seconds) => {
        const m = Math.floor(seconds / 60);
        const s = seconds % 60;
        return `${m}:${s.toString().padStart(2, '0')}`;
    };

    const triggerToast = (message) => {
        setToast({ visible: true, message });
        setTimeout(() => setToast((prev) => ({ ...prev, visible: false })), 5000);
    };

    const handleInputChange = (field, value) => {
        setData(field, value);
        setLocalErrors((prev) => ({ ...prev, [field]: null }));
        clearErrors(field);
    };

    // BAGONG CLEAR FORM LOGIC (Gamit ang Modal)
    const executeClearForm = () => {
        reset();
        setLocalErrors({});
        clearErrors();
        setIsClearModalOpen(false); // Isara ang modal pagkatapos mag-clear
    };

    const validateForm = () => {
        const newErrors = {};
        const requiredMsg = 'Kailangan itong punan.';

        if (!data.first_name) newErrors.first_name = requiredMsg;
        if (!data.last_name) newErrors.last_name = requiredMsg;
        if (!data.sex) newErrors.sex = requiredMsg;
        if (!data.dob_month || !data.dob_day || !data.dob_year)
            newErrors.dob_year = 'Kumpletuhin ang petsa ng kapanganakan.';
        if (!data.address) newErrors.address = requiredMsg;

        if (!data.contact_number) newErrors.contact_number = requiredMsg;
        else if (!/^09\d{9}$/.test(data.contact_number))
            newErrors.contact_number = 'Dapat magsimula sa 09 at may 11 numero.';

        // ========================================================
        // 🟢 THE FIX: STRICT PASSWORD REGEX SA LOOB NG SUBMIT/NEXT BUTTON
        // ========================================================
        // THE FIX: Pinalitan ang [A-Za-z\d] ng . para tumanggap ng kahit anong character,
        // at nagdagdag ng (?=.*[\W_]) para mag-require ng kahit isang symbol.
        const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;

        if (!data.password) {
            newErrors.password = requiredMsg;
        } else if (!passwordRegex.test(data.password)) {
            // Ito ang pipigil sa kanila kapag pinindot ang Next!
            newErrors.password = 'Sundin ang password requirements sa kahon.';
        }

        if (data.password !== data.password_confirmation) {
            newErrors.password_confirmation = 'Hindi magtugma ang password.';
        }

        setLocalErrors(newErrors);

        if (Object.keys(newErrors).length > 0) {
            triggerToast('Paki-kumpleto nang tama ang mga required fields sa form.');
            return false;
        }
        return true;
    };

    const goToIdScan = async () => {
        if (validateForm()) {
            setIsValidating(true);
            try {
                await axios.post(route('signup.validate_step'), data, {
                    headers: { Accept: 'application/json' }
                });
                setIsValidating(false);
                setUiView(2);
            } catch (error) {
                setIsValidating(false);
                if (error.response && error.response.status === 422) {
                    const backendErrors = error.response.data.errors;
                    const formattedErrors = {};
                    for (const key in backendErrors) {
                        formattedErrors[key] = backendErrors[key][0];
                    }
                    setLocalErrors((prev) => ({ ...prev, ...formattedErrors }));
                    triggerToast('Taken na ang detalye na ito. Paki-check ang mga pulang kahon.');
                } else {
                    triggerToast('May nangyaring error sa server. Subukan muli.');
                }
            }
        }
    };

    const goToTerms = () => {
        if (!data.id_photo_path) {
            triggerToast('Kailangan mong i-scan ang iyong Valid ID bago magpatuloy.');
            return;
        }
        setUiView(3);
    };

    const handleIdCapture = (e) => {
        const file = e.target.files;
        if (!file || file.length === 0) return;

        const maxSize = 5 * 1024 * 1024; // 5MB
        if (file.size > maxSize) {
            triggerToast('Ang file ay masyadong malaki. Maximum size ay 5MB.');
            e.target.value = '';
            return;
        }

        setData('id_photo_path', file);
        setLocalErrors((prev) => ({ ...prev, id_photo_path: null }));
        clearErrors('id_photo_path');

        const reader = new FileReader();
        reader.onload = (e) => setPreviews({ id: e.target.result });
        reader.readAsDataURL(file);
    };

    const handleScroll = (e) => {
        const bottom = e.target.scrollHeight - e.target.scrollTop <= e.target.clientHeight + 20;
        if (bottom) {
            setIsScrolledToBottom(true);
        }
    };

    const submitRegistration = (e) => {
        e.preventDefault();
        setData('terms', true);

        post(route('signup.post'), {
            preserveState: true,
            preserveScroll: true,
            // 🛑 BUG FIX: Inalis natin ang `reset('password')` sa onFinish para hindi mabura ang tinype!
            onError: (errs) => {
                if (Object.keys(errs).length > 0) {
                    if (errs.id_photo_path) {
                        triggerToast(errs.id_photo_path); // Ipakita ang "Attempt X of 5" error
                        setData('ocr_attempt', data.ocr_attempt + 1); // Dagdagan ang bilang ng attempt
                        setData('id_photo_path', null); // Burahin ang malabong litrato
                        setPreviewImage(null); // I-reset ang Camera Preview
                        setIsScanning(true); // Buksan ulit ang Live Scanner
                        setUiView(2); // 🔄 I-bounce back ang user sa View 2 (Camera)
                    } else if (errs.terms) setUiView(3);
                    else {
                        triggerToast('Mayroong error sa iyong form.');
                        setUiView(1);
                    }
                }
            },
        });
    };

    // ==========================================
    // 📸 LIVE ID SCANNER LOGIC (NASA LABAS NA!)
    // ==========================================
    const webcamRef = useRef(null);
    const [isScanning, setIsScanning] = useState(false);
    const [previewImage, setPreviewImage] = useState(null);

    const captureId = useCallback(() => {
        const imageSrc = webcamRef.current.getScreenshot();
        setPreviewImage(imageSrc);
        setIsScanning(false);

        fetch(imageSrc)
            .then((res) => res.blob())
            .then((blob) => {
                const file = new File([blob], 'live_id_capture.jpg', { type: 'image/jpeg' });
                setData('id_photo_path', file);
            });
    }, [webcamRef, setData]);

    // ==========================================
    // 🚦 PASSWORD STRENGTH CALCULATOR 🚦
    // ==========================================
    const getPasswordStrength = (pass) => {
        let score = 0;
        if (pass.length >= 8) score += 1;
        if (/[A-Z]/.test(pass)) score += 1;
        if (/[a-z]/.test(pass)) score += 1;
        if (/\d/.test(pass)) score += 1;
        return score;
    };

    const strengthScore = getPasswordStrength(data.password);
    let strengthLabel = '';
    let strengthColor = 'bg-slate-200';
    let strengthTextColor = 'text-slate-500';

    if (data.password.length > 0) {
        if (strengthScore <= 2) {
            strengthLabel = 'WEAK';
            strengthColor = 'bg-red-500';
            strengthTextColor = 'text-red-500';
        } else if (strengthScore === 3) {
            strengthLabel = 'MEDIUM';
            strengthColor = 'bg-amber-500'; // Tailwind's perfect yellow-orange
            strengthTextColor = 'text-amber-500';
        } else if (strengthScore === 4) {
            strengthLabel = 'STRONG';
            strengthColor = 'bg-green-500';
            strengthTextColor = 'text-green-500';
        }
    }

    return (
        <div className="flex min-h-screen flex-col justify-center bg-slate-50 py-10 font-sans text-slate-900 antialiased">
            <Head title="Mag-Signup - Barangay Doña Lucia" />

            {/* Global Toast */}
            <div className="pointer-events-none fixed top-6 left-1/2 z-[60] flex w-full max-w-md -translate-x-1/2 transform flex-col gap-3 px-4">
                <div
                    className={`pointer-events-auto flex items-center gap-4 rounded-xl border-l-4 border-red-500 bg-slate-900 px-6 py-4 text-white shadow-2xl transition-all duration-500 ${toast.visible ? 'translate-y-0 opacity-100' : '-translate-y-20 opacity-0'}`}
                >
                    <svg
                        className="h-6 w-6 shrink-0 text-red-500"
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
                    <div>
                        <p className="text-sm font-bold">Oops! May nakitang mali.</p>
                        <p className="text-sm font-medium">{toast.message}</p>
                    </div>
                </div>
            </div>

            <div className="mx-auto w-full max-w-3xl px-4">
                <div className="mb-6 flex items-center justify-between">
                    <Link
                        href="/"
                        className="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-slate-500 transition-all duration-200 hover:text-red-600 focus:ring-4 focus:ring-slate-200 focus:outline-none active:scale-95 active:bg-slate-200"
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
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"
                            />
                        </svg>
                        Bumalik sa Home
                    </Link>
                </div>

                <div className="relative rounded-2xl border border-slate-100 bg-white p-6 shadow-xl md:p-10">
                    {/* =========================================
              VIEW 1: ANG PINAGSAMANG REGISTRATION FORM 
              ========================================= */}
                    {uiView === 1 && (
                        <div className="animate-in fade-in duration-500">
                            <div className="relative mb-8 text-center">
                                {/* INILIPAT SA ITAAS: Clear Form Button */}
                                <button
                                    type="button"
                                    onClick={() => setIsClearModalOpen(true)}
                                    className="absolute top-0 right-0 hidden items-center gap-1 rounded-lg px-3 py-2 text-xs font-bold text-slate-400 transition-all hover:bg-slate-100 hover:text-red-600 active:scale-95 md:inline-flex"
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
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                        ></path>
                                    </svg>
                                    Clear Form
                                </button>

                                <h1 className="text-3xl font-extrabold tracking-tight text-slate-900">
                                    Registration Form
                                </h1>
                                <p className="mt-2 text-slate-500">
                                    Kumpletuhin ang mga sumusunod na detalye.
                                </p>

                                {/* Mobile version ng clear form button (lalabas lang sa maliliit na screen) */}
                                <button
                                    type="button"
                                    onClick={() => setIsClearModalOpen(true)}
                                    className="mt-4 inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-bold text-slate-500 active:scale-95 md:hidden"
                                >
                                    <svg
                                        className="h-3 w-3"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                        ></path>
                                    </svg>
                                    Clear Form
                                </button>
                            </div>

                            {/* DIVIDER 1: Personal Info */}
                            <div className="mb-6 flex items-center gap-4">
                                <div className="flex h-8 w-8 items-center justify-center rounded-full bg-red-100 font-bold text-red-600">
                                    1
                                </div>
                                <h2 className="text-lg font-black tracking-widest text-slate-800 uppercase">
                                    Personal Information
                                </h2>
                                <div className="h-px flex-1 bg-slate-200"></div>
                            </div>

                            <div className="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-4">
                                <div className="md:col-span-2">
                                    <label className="mb-1 block text-sm font-semibold text-slate-700">
                                        First Name <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        placeholder="John Lloyd"
                                        autoComplete="off"
                                        value={data.first_name}
                                        onChange={(e) =>
                                            handleInputChange('first_name', e.target.value)
                                        }
                                        className={`w-full rounded-lg border px-4 py-3 outline-none focus:ring-2 focus:ring-slate-900 ${localErrors.first_name || errors.first_name ? 'border-red-500 ring-1 ring-red-500' : 'border-slate-300'}`}
                                    />
                                    {(localErrors.first_name || errors.first_name) && (
                                        <p className="mt-1 text-xs font-bold text-red-500">
                                            {localErrors.first_name || errors.first_name}
                                        </p>
                                    )}
                                </div>
                                <div className="md:col-span-1">
                                    <label className="mb-1 block text-sm font-semibold text-slate-700">
                                        Middle Name
                                    </label>
                                    <input
                                        type="text"
                                        placeholder="Angeles"
                                        autoComplete="off"
                                        value={data.middle_name}
                                        onChange={(e) =>
                                            handleInputChange('middle_name', e.target.value)
                                        }
                                        className="w-full rounded-lg border border-slate-300 px-4 py-3 outline-none focus:ring-2 focus:ring-slate-900"
                                    />
                                    {errors.middle_name && (
                                        <p className="mt-1 text-xs font-bold text-red-500">
                                            {errors.middle_name}
                                        </p>
                                    )}
                                </div>
                                <div className="md:col-span-1">
                                    <label className="mb-1 block text-sm font-semibold text-slate-700">
                                        Suffix
                                    </label>
                                    <select
                                        value={data.suffix}
                                        onChange={(e) =>
                                            handleInputChange('suffix', e.target.value)
                                        }
                                        className="w-full rounded-lg border border-slate-300 px-4 py-3 outline-none focus:ring-2 focus:ring-slate-900"
                                    >
                                        <option value="">Wala</option>
                                        <option value="Jr.">Jr.</option>
                                        <option value="Sr.">Sr.</option>
                                        <option value="II">II</option>
                                        <option value="III">III</option>
                                    </select>
                                </div>
                                <div className="md:col-span-2">
                                    <label className="mb-1 block text-sm font-semibold text-slate-700">
                                        Last Name <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        placeholder="Dela Cruz"
                                        autoComplete="off"
                                        value={data.last_name}
                                        onChange={(e) =>
                                            handleInputChange('last_name', e.target.value)
                                        }
                                        className={`w-full rounded-lg border px-4 py-3 outline-none focus:ring-2 focus:ring-slate-900 ${localErrors.last_name || errors.last_name ? 'border-red-500 ring-1 ring-red-500' : 'border-slate-300'}`}
                                    />
                                    {(localErrors.last_name || errors.last_name) && (
                                        <p className="mt-1 text-xs font-bold text-red-500">
                                            {localErrors.last_name || errors.last_name}
                                        </p>
                                    )}
                                </div>
                                <div className="md:col-span-2">
                                    <label className="mb-1 block text-sm font-semibold text-slate-700">
                                        Kasarian (Sex) <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        value={data.sex}
                                        onChange={(e) => handleInputChange('sex', e.target.value)}
                                        className={`w-full rounded-lg border px-4 py-3 outline-none focus:ring-2 focus:ring-slate-900 ${localErrors.sex || errors.sex ? 'border-red-500 ring-1 ring-red-500' : 'border-slate-300'}`}
                                    >
                                        <option value="">-- Pumili ng Kasarian --</option>
                                        <option value="Male">Lalaki (Male)</option>
                                        <option value="Female">Babae (Female)</option>
                                    </select>
                                    {(localErrors.sex || errors.sex) && (
                                        <p className="mt-1 text-xs font-bold text-red-500">
                                            {localErrors.sex || errors.sex}
                                        </p>
                                    )}
                                </div>
                                <div className="md:col-span-4">
                                    <label className="mb-1 block text-sm font-semibold text-slate-700">
                                        Date of Birth <span className="text-red-500">*</span>
                                    </label>
                                    <div className="grid grid-cols-3 gap-2">
                                        <select
                                            value={data.dob_month}
                                            onChange={(e) =>
                                                handleInputChange('dob_month', e.target.value)
                                            }
                                            className={`w-full rounded-lg border px-3 py-3 ${localErrors.dob_year || errors.dob_month ? 'border-red-500 ring-1 ring-red-500' : 'border-slate-300'}`}
                                        >
                                            <option value="">Month</option>
                                            <option value="01">Jan</option>
                                            <option value="02">Feb</option>
                                            <option value="03">Mar</option>
                                            <option value="04">Apr</option>
                                            <option value="05">May</option>
                                            <option value="06">Jun</option>
                                            <option value="07">Jul</option>
                                            <option value="08">Aug</option>
                                            <option value="09">Sep</option>
                                            <option value="10">Oct</option>
                                            <option value="11">Nov</option>
                                            <option value="12">Dec</option>
                                        </select>
                                        <select
                                            value={data.dob_day}
                                            onChange={(e) =>
                                                handleInputChange('dob_day', e.target.value)
                                            }
                                            className={`w-full rounded-lg border px-3 py-3 ${localErrors.dob_year || errors.dob_day ? 'border-red-500 ring-1 ring-red-500' : 'border-slate-300'}`}
                                        >
                                            <option value="">Day</option>
                                            {Array.from({ length: 31 }, (_, i) => i + 1).map(
                                                (d) => (
                                                    <option
                                                        key={d}
                                                        value={d.toString().padStart(2, '0')}
                                                    >
                                                        {d}
                                                    </option>
                                                )
                                            )}
                                        </select>
                                        <select
                                            value={data.dob_year}
                                            onChange={(e) =>
                                                handleInputChange('dob_year', e.target.value)
                                            }
                                            className={`w-full rounded-lg border px-3 py-3 ${localErrors.dob_year || errors.dob_year ? 'border-red-500 ring-1 ring-red-500' : 'border-slate-300'}`}
                                        >
                                            <option value="">Year</option>
                                            {Array.from(
                                                { length: 100 },
                                                (_, i) => new Date().getFullYear() - i
                                            ).map((y) => (
                                                <option key={y} value={y}>
                                                    {y}
                                                </option>
                                            ))}
                                        </select>
                                    </div>
                                    {(localErrors.dob_year || errors.dob_year) && (
                                        <p className="mt-1 text-xs font-bold text-red-500">
                                            {localErrors.dob_year ||
                                                errors.dob_year ||
                                                'Pakikumpleto ang iyong kapanganakan.'}
                                        </p>
                                    )}
                                </div>
                            </div>

                            {/* DIVIDER 2: Address */}
                            <div className="mb-6 flex items-center gap-4">
                                <div className="flex h-8 w-8 items-center justify-center rounded-full bg-red-100 font-bold text-red-600">
                                    2
                                </div>
                                <h2 className="text-lg font-black tracking-widest text-slate-800 uppercase">
                                    Tirahan (Address)
                                </h2>
                                <div className="h-px flex-1 bg-slate-200"></div>
                            </div>

                            <div className="mb-8">
                                <label className="mb-1 block text-sm font-semibold text-slate-700">
                                    Kumpletong Tirahan (Address){' '}
                                    <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    placeholder="Hal. 123 Purok 1, Brgy. Doña Lucia *"
                                    autoComplete="off"
                                    value={data.address}
                                    onChange={(e) => handleInputChange('address', e.target.value)}
                                    className={`w-full rounded-lg border px-4 py-3 outline-none focus:ring-2 focus:ring-slate-900 ${localErrors.address || errors.address ? 'border-red-500 ring-1 ring-red-500' : 'border-slate-300'}`}
                                />
                                {(localErrors.address || errors.address) && (
                                    <p className="mt-1 text-xs font-bold text-red-500">
                                        {localErrors.address || errors.address}
                                    </p>
                                )}
                            </div>

                            {/* DIVIDER 3: Account Details */}
                            <div className="mb-6 flex items-center gap-4">
                                <div className="flex h-8 w-8 items-center justify-center rounded-full bg-red-100 font-bold text-red-600">
                                    3
                                </div>
                                <h2 className="text-lg font-black tracking-widest text-slate-800 uppercase">
                                    Account Details
                                </h2>
                                <div className="h-px flex-1 bg-slate-200"></div>
                            </div>

                            {/* mobile number & email address */}
                            <div className="mb-8 grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label className="mb-1 block text-sm font-semibold text-slate-700">
                                        Mobile Number <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="tel"
                                        placeholder="09XXXXXXXXX"
                                        maxLength="11"
                                        autoComplete="off"
                                        value={data.contact_number}
                                        onChange={(e) =>
                                            handleInputChange(
                                                'contact_number',
                                                e.target.value.replace(/[^0-9]/g, '')
                                            )
                                        }
                                        className={`w-full rounded-lg border px-4 py-3 outline-none focus:ring-2 focus:ring-slate-900 ${localErrors.contact_number || errors.contact_number ? 'border-red-500 ring-1 ring-red-500' : 'border-slate-300'}`}
                                    />
                                    {(localErrors.contact_number || errors.contact_number) && (
                                        <p className="mt-1 text-xs font-bold text-red-500">
                                            {localErrors.contact_number || errors.contact_number}
                                        </p>
                                    )}
                                </div>
                                <div>
                                    <label className="mb-1 block text-sm font-semibold text-slate-700">
                                        Email Address (Optional)
                                    </label>
                                    <input
                                        type="email"
                                        placeholder="juan@email.com"
                                        autoComplete="off"
                                        value={data.email}
                                        onChange={(e) => handleInputChange('email', e.target.value)}
                                        className={`w-full rounded-lg border px-4 py-3 outline-none focus:ring-2 focus:ring-slate-900 ${errors.email ? 'border-red-500 ring-1 ring-red-500' : 'border-slate-300'}`}
                                    />
                                    {errors.email && (
                                        <p className="mt-1 text-xs font-bold text-red-500">
                                            {errors.email}
                                        </p>
                                    )}
                                </div>

                                {/* password */}
                                <div>
                                    <label className="mb-1 block text-sm font-semibold text-slate-700">
                                        Password <span className="text-red-500">*</span>
                                    </label>
                                    <div className="relative">
                                        <input
                                            type={showPassword ? 'text' : 'password'}
                                            placeholder="********"
                                            autoComplete="off"
                                            value={data.password}
                                            onChange={(e) =>
                                                handleInputChange('password', e.target.value)
                                            }
                                            className={`w-full rounded-lg border px-4 py-3 pr-20 outline-none focus:ring-2 focus:ring-slate-900 ${localErrors.password || errors.password ? 'border-red-500 ring-1 ring-red-500' : 'border-slate-300'}`}
                                        />
                                        <button
                                            type="button"
                                            onClick={() => setShowPassword(!showPassword)}
                                            className="absolute top-1/2 right-2 -translate-y-1/2 rounded-md p-3 text-xs font-bold text-slate-400 hover:text-slate-900"
                                        >
                                            {showPassword ? 'HIDE' : 'SHOW'}
                                        </button>
                                    </div>
                                    {(localErrors.password || errors.password) && (
                                        <p className="mt-1 text-xs font-bold text-red-500">
                                            {localErrors.password || errors.password}
                                        </p>
                                    )}

                                    {/* 🚦 PASSWORD STRENGTH METER 🚦 */}
                                    {data.password.length > 0 && (
                                        <div className="mt-3">
                                            <div className="mb-1 flex justify-between">
                                                <span className="text-[10px] font-black tracking-widest text-slate-400 uppercase">
                                                    Strength:
                                                </span>
                                                <span
                                                    className={`text-[10px] font-black tracking-widest uppercase transition-colors duration-300 ${strengthTextColor}`}
                                                >
                                                    {strengthLabel}
                                                </span>
                                            </div>
                                            {/* The 3 Segment Bars */}
                                            <div className="flex h-1.5 w-full gap-1">
                                                <div
                                                    className={`h-full flex-1 rounded-full transition-colors duration-500 ${data.password.length > 0 ? strengthColor : 'bg-slate-200'}`}
                                                ></div>
                                                <div
                                                    className={`h-full flex-1 rounded-full transition-colors duration-500 ${strengthScore >= 3 ? strengthColor : 'bg-slate-200'}`}
                                                ></div>
                                                <div
                                                    className={`h-full flex-1 rounded-full transition-colors duration-500 ${strengthScore === 4 ? strengthColor : 'bg-slate-200'}`}
                                                ></div>
                                            </div>
                                        </div>
                                    )}

                                    {/* 🟢 LIVE VISUAL PASSWORD TRACKER (NO SYMBOLS) 🟢 */}
                                    <div className="flex flex-col gap-1.5 text-[11px] font-bold">
                                        <span
                                            className={`flex items-center gap-2 transition-colors ${data.password.length >= 8 ? 'text-green-600' : 'text-slate-400'}`}
                                        >
                                            {data.password.length >= 8 ? '✅' : '○'} Walong (8)
                                            characters o higit pa
                                        </span>
                                        <span
                                            className={`flex items-center gap-2 transition-colors ${/[A-Z]/.test(data.password) ? 'text-green-600' : 'text-slate-400'}`}
                                        >
                                            {/[A-Z]/.test(data.password) ? '✅' : '○'} May isang
                                            malaking letra (A-Z)
                                        </span>
                                        <span
                                            className={`flex items-center gap-2 transition-colors ${/[a-z]/.test(data.password) ? 'text-green-600' : 'text-slate-400'}`}
                                        >
                                            {/[a-z]/.test(data.password) ? '✅' : '○'} May isang
                                            maliit na letra (a-z)
                                        </span>
                                        <span
                                            className={`flex items-center gap-2 transition-colors ${/\d/.test(data.password) ? 'text-green-600' : 'text-slate-400'}`}
                                        >
                                            {/\d/.test(data.password) ? '✅' : '○'} May isang numero
                                            (0-9)
                                        </span>
                                        {/* 🟢 IDAGDAG ITO: Requirement para sa Symbol 🟢 */}
                                        <span
                                            className={`flex items-center gap-2 transition-colors ${/[\W_]/.test(data.password) ? 'text-green-600' : 'text-slate-400'}`}
                                        >
                                            {/[\W_]/.test(data.password) ? '✅' : '○'} May isang
                                            symbol (hal. @, !, #)
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <label className="mb-1 block text-sm font-semibold text-slate-700">
                                        Confirm Password <span className="text-red-500">*</span>
                                    </label>
                                    <div className="relative">
                                        <input
                                            type={showConfirmPassword ? 'text' : 'password'}
                                            placeholder="********"
                                            autoComplete="off"
                                            value={data.password_confirmation}
                                            onChange={(e) =>
                                                handleInputChange(
                                                    'password_confirmation',
                                                    e.target.value
                                                )
                                            }
                                            className={`w-full rounded-lg border px-4 py-3 pr-20 outline-none focus:ring-2 focus:ring-slate-900 ${localErrors.password_confirmation || errors.password_confirmation ? 'border-red-500 ring-1 ring-red-500' : 'border-slate-300'}`}
                                        />
                                        <button
                                            type="button"
                                            onClick={() =>
                                                setShowConfirmPassword(!showConfirmPassword)
                                            }
                                            className="absolute top-1/2 right-2 -translate-y-1/2 rounded-md p-3 text-xs font-bold text-slate-400 hover:text-slate-900"
                                        >
                                            {showConfirmPassword ? 'HIDE' : 'SHOW'}
                                        </button>
                                    </div>
                                    {(localErrors.password_confirmation ||
                                        errors.password_confirmation) && (
                                        <p className="mt-1 text-xs font-bold text-red-500">
                                            {localErrors.password_confirmation ||
                                                errors.password_confirmation}
                                        </p>
                                    )}
                                </div>
                            </div>

                            {/* Tanging ang "Next" button na lang ang nandito sa ibaba */}
                            <div className="border-t border-slate-100 pt-6">
                                <button
                                    type="button"
                                    onClick={goToIdScan}
                                    disabled={isValidating}
                                    className={`w-full rounded-xl bg-slate-900 px-8 py-4 font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-slate-800 active:scale-95 ${isValidating ? 'opacity-50 cursor-not-allowed' : ''}`}
                                >
                                    {isValidating ? 'Checking...' : 'Next: I-Scan ang ID'}
                                </button>
                            </div>
                        </div>
                    )}

                    {/* =========================================
              VIEW 2: ON-THE-SPOT ID SCANNER (ENTERPRISE WEBCAM
              ========================================= */}
                    {uiView === 2 && (
                        <div className="animate-in fade-in slide-in-from-right-8 duration-500">
                            <div className="mb-6 text-center">
                                <h1 className="text-2xl font-extrabold tracking-tight text-slate-900">
                                    I-Scan ang Iyong ID
                                </h1>
                                <p className="mt-2 text-sm text-slate-500">
                                    Kailangan ito para sa Automated ID Verification ng barangay.
                                </p>
                            </div>

                            <div className="mb-8">
                                {/* DEV MODE BYPASS */}
                                {!isScanning && !previewImage && (
                                    <button
                                        type="button"
                                        onClick={() => {
                                            const dummyFile = new File(["dummy content"], "mock_id.jpg", { type: "image/jpeg" });
                                            setData('id_photo_path', dummyFile);
                                            setPreviewImage("https://via.placeholder.com/400x250.png?text=MOCK+ID+BYPASS");
                                            setIsScanning(false);
                                            triggerToast("Dev Mode: Simulated ID Capture!");
                                        }}
                                        className="mb-4 w-full rounded-xl bg-purple-600 px-8 py-3 font-bold text-white shadow-md transition-all hover:bg-purple-700 active:scale-95"
                                    >
                                        🛠️ DEV MODE: Bypass Camera & Scanner
                                    </button>
                                )}

                                {/* DEV MODE FORCED ERROR */}
                                {!isScanning && !previewImage && (
                                    <button
                                        type="button"
                                        onClick={() => {
                                            const errorFile = new File(["dummy"], "error.jpg", { type: "image/jpeg" });
                                            setData(data => ({ ...data, id_photo_path: errorFile, simulate_error: true, ocr_attempt: 5 }));
                                            setPreviewImage("https://via.placeholder.com/400x250.png?text=FORCED+ERROR");
                                            setIsScanning(false);
                                            triggerToast("Dev Mode: Simulated Error Active. Proceed to Terms.");
                                        }}
                                        className="mb-4 w-full rounded-xl bg-red-600 px-8 py-3 font-bold text-white shadow-md transition-all hover:bg-red-700 active:scale-95"
                                    >
                                        🚨 DEV MODE: Force Failed Scan (Test Lockout)
                                    </button>
                                )}

                                {/* BUTTON PARA BUKSAN ANG CAMERA */}
                                {!isScanning && !previewImage && (
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
                                )}

                                {/* ANG LIVE CAMERA FRAME */}
                                {isScanning && (
                                    <div className="relative overflow-hidden rounded-2xl border-2 border-slate-800 bg-black shadow-xl">
                                        <Webcam
                                            audio={false}
                                            ref={webcamRef}
                                            screenshotFormat="image/jpeg"
                                            videoConstraints={{ facingMode: 'environment' }}
                                            className="w-full object-cover opacity-80"
                                        />

                                        {/* ID Frame Guide (Kahon sa gitna para sa user) */}
                                        <div className="pointer-events-none absolute inset-0 flex items-center justify-center">
                                            <div className="h-3/5 w-4/5 rounded-xl border-2 border-white/60 shadow-[0_0_0_9999px_rgba(0,0,0,0.6)]"></div>
                                        </div>
                                        <p className="absolute top-4 w-full text-center text-xs font-bold tracking-widest text-white uppercase drop-shadow-md">
                                            I-posisyon ang ID sa loob ng kahon
                                        </p>

                                        <button
                                            type="button"
                                            onClick={captureId}
                                            className="absolute bottom-4 left-1/2 -translate-x-1/2 rounded-full bg-red-600 px-8 py-3 text-sm font-black tracking-widest text-white uppercase shadow-lg transition-all active:scale-95"
                                        >
                                            📸 Capture
                                        </button>
                                    </div>
                                )}

                                {/* PREVIEW NG NAKUHAANG ID */}
                                {previewImage && (
                                    <div className="flex flex-col items-center">
                                        <div className="relative w-full overflow-hidden rounded-2xl border border-slate-300 shadow-sm">
                                            <img
                                                src={previewImage}
                                                alt="ID Preview"
                                                className="mb-4 max-h-64 w-full rounded-xl bg-slate-100 object-contain shadow-sm"
                                            />
                                            <button
                                                type="button"
                                                onClick={() => {
                                                    setPreviewImage(null);
                                                    setIsScanning(true);
                                                    setData('id_photo_path', null);
                                                }}
                                                className="absolute right-3 bottom-6 rounded-lg bg-slate-900 px-4 py-2 text-[10px] font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-slate-800 active:scale-95"
                                            >
                                                Ulitin ang Pag-scan
                                            </button>
                                        </div>
                                    </div>
                                )}

                                {/* ERROR MESSAGE FALLBACK */}
                                {errors?.id_photo_path && (
                                    <p className="mt-2 text-center text-xs font-bold text-red-500">
                                        {errors.id_photo_path}
                                    </p>
                                )}
                            </div>

                            {/* NAVIGATION BUTTONS */}
                            <div className="flex flex-col-reverse gap-4 sm:flex-row">
                                <button
                                    type="button"
                                    onClick={() => setUiView(1)}
                                    className="w-full rounded-xl border border-slate-300 bg-transparent px-8 py-4 font-bold text-slate-700 transition-all hover:bg-slate-100 active:scale-95 sm:w-1/3"
                                >
                                    Bumalik
                                </button>
                                <button
                                    type="button"
                                    onClick={goToTerms}
                                    disabled={!previewImage}
                                    className="w-full rounded-xl bg-slate-900 px-8 py-4 font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-slate-800 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50 sm:w-2/3"
                                >
                                    Next: Terms & Conditions
                                </button>
                            </div>
                        </div>
                    )}

                    {/* =========================================
              VIEW 3: INLINE TERMS & CONDITIONS (FORCED SCROLL)
              ========================================= */}
                    {uiView === 3 && (
                        <div className="animate-in fade-in slide-in-from-right-8 duration-500">
                            <div className="mb-4">
                                <h1 className="text-2xl font-extrabold tracking-tight text-slate-900">
                                    Review & Agreement
                                </h1>
                                <p className="mt-1 text-sm font-bold text-red-600">
                                    ⚠️ Paki-scroll hanggang ibaba bago pumayag.
                                </p>
                            </div>

                            <div
                                onScroll={handleScroll}
                                className="mb-6 max-h-80 w-full overflow-y-auto rounded-xl border-2 border-slate-200 bg-slate-50 p-6 text-sm text-slate-700 shadow-inner"
                            >
                                <h3 className="mb-4 text-lg font-black text-slate-900">
                                    Patakaran sa Privacy ng BDLS (Privacy Policy)
                                </h3>
                                <p className="mb-4 text-justify">
                                    Ang Barangay Doña Lucia ay nagpapahalaga sa iyong personal na
                                    impormasyon. Ang patakarang ito ay nagpapaliwanag kung paano
                                    namin ginagamit at pino-protektahan ang iyong data.
                                </p>

                                <h4 className="font-bold text-slate-900">
                                    1. Anong impormasyon ang kinokolekta namin?
                                </h4>
                                <p className="mb-4 text-justify">
                                    Para makagawa ng account, kukunin namin ang iyong Pangalan,
                                    Address, Contact Number, Password, at litrato ng iyong ID. Ang
                                    pagbibigay ng Email ay optional. Kukunin din namin ang iyong
                                    Edad at Kasarian para matukoy ng barangay kung anong mga
                                    benepisyo o programa ang nararapat sa iyong grupo.
                                </p>

                                <h4 className="font-bold text-slate-900">
                                    2. Paano namin ito itatago at pino-protektahan?
                                </h4>
                                <p className="mb-4 text-justify">
                                    Ang litrato ng iyong ID ay itatago ng system upang hindi mo na
                                    kailangang mag-pasa ulit para sa mga susunod mong transaksyon.
                                    Para sa iyong kaligtasan, ang iyong data ay naka-encrypt at
                                    naka-imbak sa mga secure servers upang maiwasan ang pagnanakaw
                                    ng impormasyon.
                                </p>

                                <h4 className="font-bold text-slate-900">
                                    3. Kanino namin ito ibinabahagi?
                                </h4>
                                <p className="mb-4 text-justify">
                                    HINDI namin ibebenta, ipagpapalit, o ibibigay ang iyong data sa
                                    mga taong walang awtorisasyon. Ipapasa lamang ang iyong Contact
                                    Number (at Email kung meron) sa aming awtomatikong Notification
                                    System para makapagpadala sa iyo ng updates tungkol sa iyong
                                    request. Ibabahagi lamang namin ang iyong impormasyon sa mga
                                    awtoridad kung may utos ng batas o may naganap na krimen.
                                </p>

                                <h4 className="font-bold text-slate-900">
                                    4. Ang Iyong Karapatan sa Data (Data Rights)
                                </h4>
                                <p className="mb-8 text-justify">
                                    Maaari mong hilingin na i-update o i-delete ang iyong
                                    impormasyon sa system anumang oras sa pamamagitan ng pagpapadala
                                    ng mensahe o paglapit nang personal sa aming barangay admin.
                                </p>

                                <hr className="my-6 border-slate-200" />

                                <h3 className="mb-4 text-lg font-black text-slate-900">
                                    Mga Tuntunin at Kundisyon (Terms & Conditions)
                                </h3>
                                <p className="mb-4 text-justify">
                                    Sa paggawa ng account sa BDLS, sumasang-ayon ka sa mga sumusunod
                                    na patakaran ng aming barangay:
                                </p>

                                <h4 className="font-bold text-slate-900">
                                    1. Responsibilidad sa Tamang Impormasyon
                                </h4>
                                <p className="mb-4 text-justify">
                                    Responsibilidad ng user na siguraduhing tama at totoo ang lahat
                                    ng impormasyong ibibigay sa system. Anumang maling impormasyon
                                    ay maaaring maging dahilan ng pagka-antala o pagka-reject ng
                                    iyong request.
                                </p>

                                <h4 className="font-bold text-slate-900">
                                    2. Seguridad ng Account
                                </h4>
                                <p className="mb-4 text-justify">
                                    Huwag ibigay ang iyong password sa iba. Ikaw ang responsable sa
                                    pag-iingat ng iyong account. Anumang transaksyon o request na
                                    ginawa gamit ang iyong account ay ituturing na gawa mo.
                                </p>

                                <h4 className="font-bold text-slate-900">
                                    3. Bawal ang Spam at Panliligalig
                                </h4>
                                <p className="mb-4 text-justify">
                                    Mahigpit na ipinagbabawal ang paggamit ng system para
                                    mang-harass, mang-troll, o mag-spam ng mga walang kwentang
                                    service requests na nakakaabala sa operasyon ng barangay hall.
                                </p>

                                <h4 className="font-bold text-slate-900">
                                    4. Bawal ang Paggamit ng Pagkakakilanlan ng Iba (Identity Theft)
                                </h4>
                                <p className="mb-4 text-justify">
                                    Ang paggamit ng pekeng pangalan, o pag-upload ng ID ng ibang tao
                                    nang walang pahintulot ay isang krimen. Ito ay labag sa RA 10175
                                    (Cybercrime Prevention Act of 2012). Ang sinumang mahuhuli ay
                                    ire-report sa mga awtoridad para sa legal na aksyon at agad na
                                    iba-ban ang account.
                                </p>

                                <h4 className="font-bold text-slate-900">
                                    5. Bawal ang Pangha-hack at Kriminalidad
                                </h4>
                                <p className="mb-4 text-justify">
                                    Anumang pagsubok na nakawin ang data ng ibang residente o sirain
                                    ang system ay may katumbas na kasong kriminal at agarang
                                    pagka-ban.
                                </p>

                                <p className="mt-8 text-center text-xs font-bold tracking-widest text-slate-400 uppercase">
                                    - END OF DOCUMENT -
                                </p>
                            </div>

                            <div className="flex flex-col-reverse gap-4 sm:flex-row">
                                <button
                                    type="button"
                                    onClick={() => setUiView(2)}
                                    className="w-full rounded-xl border border-slate-300 bg-transparent px-8 py-4 font-bold text-slate-700 transition-all hover:bg-slate-100 active:scale-95 sm:w-1/3"
                                >
                                    Bumalik
                                </button>
                                <button
                                    type="button"
                                    onClick={submitRegistration}
                                    disabled={!isScrolledToBottom || processing}
                                    className={`w-full rounded-xl px-8 py-4 font-black tracking-widest text-white uppercase shadow-md transition-all sm:w-2/3 ${!isScrolledToBottom ? 'cursor-not-allowed bg-slate-300' : 'bg-red-600 hover:bg-red-700 active:scale-95'}`}
                                >
                                    {processing ? 'Pinoproseso...' : 'I Agree & Register'}
                                </button>
                            </div>
                        </div>
                    )}
                </div>
            </div>

            {/* =========================================
          VIEW 4: THE "LIVE KYC" PROCESSING WAIT PAGE
          ========================================= */}
            {processing && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/95 p-4 backdrop-blur-md">
                    <div className="flex w-full max-w-lg flex-col items-center gap-6 rounded-3xl bg-white p-10 text-center shadow-2xl">
                        {/* The Animated Loader Ring */}
                        <div className="relative flex h-28 w-28 items-center justify-center">
                            <svg
                                className="absolute h-full w-full animate-spin text-red-600"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    className="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    strokeWidth="3"
                                ></circle>
                                <path
                                    className="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                ></path>
                            </svg>
                            {/* Dynamic Timer Inside */}
                            <div className="font-mono text-xl font-black tracking-widest text-slate-900">
                                {formatTime(timeLeft)}
                            </div>
                        </div>

                        {/* Dynamic Status Text */}
                        <div className="space-y-2">
                            <h2 className="text-xl font-black tracking-widest text-slate-900 uppercase">
                                Automated ID Verification
                            </h2>
                            <p className="min-h-[40px] animate-pulse text-sm font-bold text-slate-500">
                                {loadingMessages[loadingTextIndex]}
                            </p>
                        </div>

                        <div className="mt-4 w-full rounded-full bg-slate-100 p-1">
                            <div
                                className="h-2 rounded-full bg-red-600 transition-all duration-[4000ms] ease-linear"
                                style={{
                                    width: `${((loadingTextIndex + 1) / loadingMessages.length) * 100}%`,
                                }}
                            ></div>
                        </div>

                        <p className="text-[10px] font-bold tracking-widest text-slate-400 uppercase">
                            Maximum Waiting Time: 3:00 Mins
                        </p>
                    </div>
                </div>
            )}

            {/* =========================================
          CUSTOM MODAL: CLEAR FORM CONFIRMATION
          ========================================= */}
            {isClearModalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/80 p-4 backdrop-blur-sm transition-opacity">
                    <div className="w-full max-w-sm transform overflow-hidden rounded-2xl border border-red-100 bg-white p-6 shadow-2xl transition-all">
                        <div className="mb-6 flex flex-col items-center text-center">
                            <div className="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-red-100 text-red-600">
                                <svg
                                    className="h-8 w-8"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                    ></path>
                                </svg>
                            </div>
                            <h3 className="text-xl font-black tracking-tight text-slate-900 uppercase">
                                Clear Form?
                            </h3>
                            <p className="mt-2 px-4 text-sm font-medium text-slate-500">
                                Sigurado ka bang gusto mong burahin ang lahat ng inilagay mo sa
                                form? Hindi na ito maibabalik.
                            </p>
                        </div>
                        <div className="flex flex-col gap-3">
                            <button
                                type="button"
                                onClick={executeClearForm}
                                className="w-full rounded-xl bg-red-600 py-3 text-xs font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-red-700 active:scale-95"
                            >
                                Oo, Burahin Lahat
                            </button>
                            <button
                                type="button"
                                onClick={() => setIsClearModalOpen(false)}
                                className="w-full py-2 text-[10px] font-black tracking-widest text-slate-400 uppercase transition-all hover:text-slate-600"
                            >
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
