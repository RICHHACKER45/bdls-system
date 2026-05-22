import React, { useState, useEffect, useRef } from 'react';
import { Link, Head, useForm } from '@inertiajs/react';

const PrivacyModal = ({ isOpen, onClose }) => {
    if (!isOpen) return null;
    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm transition-opacity">
            <div className="flex max-h-[80vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
                <div className="flex items-center justify-between border-b border-slate-200 bg-slate-50 p-6">
                    <h2 className="text-xl font-bold text-slate-900">
                        Patakaran sa Privacy ng BDLS
                    </h2>
                    <button
                        type="button"
                        onClick={onClose}
                        className="text-2xl font-bold text-slate-400 hover:text-slate-800"
                    >
                        &times;
                    </button>
                </div>
                <div className="space-y-4 overflow-y-auto p-6 text-sm text-slate-700">
                    <p>
                        Ang Barangay Doña Lucia ay nagpapahalaga sa iyong personal na impormasyon.
                        Ang patakarang ito ay nagpapaliwanag kung paano namin ginagamit at
                        pino-protektahan ang iyong data.
                    </p>
                    <h3 className="font-bold text-slate-900">
                        1. Anong impormasyon ang kinokolekta namin?
                    </h3>
                    <p>
                        Para makagawa ng account, kukunin namin ang iyong Pangalan, Address, Contact
                        Number, Password, litrato ng iyong ID, at isang Selfie. Ang pagbibigay ng
                        Email ay optional. Kukunin din namin ang iyong Edad at Kasarian para matukoy
                        ng barangay kung anong mga benepisyo o programa ang nararapat sa iyong
                        grupo.
                    </p>
                    <h3 className="font-bold text-slate-900">
                        2. Paano namin ito itatago at pino-protektahan?
                    </h3>
                    <p>
                        Ang iyong Selfie ay gagamitin bilang iyong Profile Picture sa system. Ang
                        litrato ng iyong ID ay itatago ng system upang hindi mo na kailangang
                        mag-pasa ulit para sa mga susunod mong transaksyon. Para sa iyong
                        kaligtasan,{' '}
                        <strong className="text-slate-900">
                            ang iyong data ay naka-encrypt at naka-imbak sa mga secure servers
                        </strong>{' '}
                        upang maiwasan ang pagnanakaw ng impormasyon.
                    </p>
                    <h3 className="font-bold text-slate-900">3. Kanino namin ito ibinabahagi?</h3>
                    <p>
                        HINDI namin ibebenta, ipagpapalit, o ibibigay ang iyong data sa mga taong
                        walang awtorisasyon. Ipapasa lamang ang iyong Contact Number (at Email kung
                        meron) sa aming awtomatikong Notification System para makapagpadala sa iyo
                        ng updates tungkol sa iyong request. Ibabahagi lamang namin ang iyong
                        impormasyon sa mga awtoridad kung may utos ng batas o may naganap na krimen.
                    </p>
                    <h3 className="font-bold text-slate-900">
                        4. Ang Iyong Karapatan sa Data (Data Rights)
                    </h3>
                    <p>
                        Maaari mong hilingin na i-update o i-delete ang iyong impormasyon sa system
                        anumang oras sa pamamagitan ng pagpapadala ng mensahe o paglapit nang
                        personal sa aming barangay admin.
                    </p>
                </div>
                <div className="border-t border-slate-200 bg-slate-50 p-4 text-right">
                    <button
                        type="button"
                        onClick={onClose}
                        className="rounded-lg bg-slate-900 px-6 py-2 text-sm font-bold text-white transition-all hover:bg-slate-800 active:scale-95"
                    >
                        Naintindihan Ko
                    </button>
                </div>
            </div>
        </div>
    );
};

const TermsModal = ({ isOpen, onClose }) => {
    if (!isOpen) return null;
    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm transition-opacity">
            <div className="flex max-h-[80vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
                <div className="flex items-center justify-between border-b border-slate-200 bg-slate-50 p-6">
                    <h2 className="text-xl font-bold text-slate-900">Mga Tuntunin at Kundisyon</h2>
                    <button
                        type="button"
                        onClick={onClose}
                        className="text-2xl font-bold text-slate-400 hover:text-slate-800"
                    >
                        &times;
                    </button>
                </div>
                <div className="space-y-4 overflow-y-auto p-6 text-sm text-slate-700">
                    <p>
                        Sa paggawa ng account sa BDLS, sumasang-ayon ka sa mga sumusasunod na
                        patakaran ng aming barangay:
                    </p>
                    <h3 className="font-bold text-slate-900">
                        1. Responsibilidad sa Tamang Impormasyon
                    </h3>
                    <p>
                        Responsibilidad ng user na siguraduhing tama at totoo ang lahat ng
                        impormasyong ibibigay sa system. Anumang maling impormasyon ay maaaring
                        maging dahilan ng pagka-antala o pagka-reject ng iyong request.
                    </p>
                    <h3 className="font-bold text-slate-900">2. Seguridad ng Account</h3>
                    <p>
                        Huwag ibigay ang iyong password sa iba. Ikaw ang responsable sa pag-iingat
                        ng iyong account. Anumang transaksyon o request na ginawa gamit ang iyong
                        account ay ituturing na gawa mo.
                    </p>
                    <h3 className="font-bold text-slate-900">3. Bawal ang Spam at Panliligalig</h3>
                    <p>
                        Mahigpit na ipinagbabawal ang paggamit ng system para mang-harass,
                        mang-troll, o mag-spam ng mga walang kwentang service requests na
                        nakakaabala sa operasyon ng barangay hall.
                    </p>
                    <h3 className="font-bold text-slate-900">
                        4. Bawal ang Paggamit ng Pagkakakilanlan ng Iba (Identity Theft)
                    </h3>
                    <p>
                        Ang paggamit ng pekeng pangalan, o pag-upload ng ID at mukha ng ibang tao
                        nang walang pahintulot ay isang krimen. Ito ay labag sa RA 10175 (Cybercrime
                        Prevention Act of 2012). Ang sinumang mahuhuli ay ire-report sa mga
                        awtoridad para sa legal na aksyon at agad na iba-ban ang account.
                    </p>
                    <h3 className="font-bold text-slate-900">
                        5. Bawal ang Pangha-hack at Kriminalidad
                    </h3>
                    <p>
                        Anumang pagsubok na nakawin ang data ng ibang residente o sirain ang system
                        ay may katumbas na kasong kriminal at agarang pagka-ban.
                    </p>
                    <h3 className="font-bold text-slate-900">
                        6. Patakaran sa Hindi Pagkuha ng Dokumento (No-Show Policy)
                    </h3>
                    <p>
                        Kapag na-aprubahan at na-text ka na ang iyong dokumento ay "Ready for
                        Release", mangyaring kunin ito agad. <br />
                        • Kung hindi mo ito makuha sa loob ng 1 linggo, papadalhan ka namin ng isa
                        pang paalala (2nd attempt).
                        <br />• Kung lumipas ang 2 linggo at hindi mo pa rin kinukuha, papatawan ang
                        iyong account ng 1-linggong penalty kung saan hindi ka muna
                        makakapag-request ng bagong dokumento sa system.
                    </p>
                </div>
                <div className="border-t border-slate-200 bg-slate-50 p-4 text-right">
                    <button
                        type="button"
                        onClick={onClose}
                        className="rounded-lg bg-slate-900 px-6 py-2 text-sm font-bold text-white transition-all hover:bg-slate-800 active:scale-95"
                    >
                        Naintindihan Ko
                    </button>
                </div>
            </div>
        </div>
    );
};

export default function Signup() {
    const [step, setStep] = useState(1);
    const [isPrivacyOpen, setIsPrivacyOpen] = useState(false);
    const [isTermsOpen, setIsTermsOpen] = useState(false);
    const [previews, setPreviews] = useState({ id: null, selfie: null });
    const [showPassword, setShowPassword] = useState(false);
    const [showConfirmPassword, setShowConfirmPassword] = useState(false);
    const [localErrors, setLocalErrors] = useState({});
    const [toast, setToast] = useState({ visible: false, message: '' });

    const idInputRef = useRef(null);
    const selfieInputRef = useRef(null);

    const { data, setData, post, processing, errors, reset, clearErrors } = useForm({
        first_name: '',
        middle_name: '',
        last_name: '',
        suffix: '',
        sex: '',
        dob_month: '',
        dob_day: '',
        dob_year: '',
        house_number: '',
        purok_street: '',
        contact_number: '',
        email: '',
        password: '',
        password_confirmation: '',
        id_photo_path: null,
        selfie_photo_path: null,
        terms: false,
    });

    // Sticky Form Logic (Restore from sessionStorage)
    useEffect(() => {
        const savedDraft = sessionStorage.getItem('bdls_signup_draft');
        if (savedDraft) {
            const draft = JSON.parse(savedDraft);
            const { password, password_confirmation, id_photo_path, selfie_photo_path, ...rest } =
                draft;
            Object.keys(rest).forEach((key) => setData(key, rest[key]));
        }

        const savedStep = sessionStorage.getItem('bdls_signup_step');
        if (savedStep) {
            setStep(parseInt(savedStep));
        }
    }, []);

    // Save Draft to sessionStorage
    useEffect(() => {
        const { id_photo_path, selfie_photo_path, ...serializableData } = data;
        sessionStorage.setItem('bdls_signup_draft', JSON.stringify(serializableData));
        sessionStorage.setItem('bdls_signup_step', step.toString());
    }, [data, step]);

    const triggerToast = (message) => {
        setToast({ visible: true, message });
        setTimeout(() => setToast((prev) => ({ ...prev, visible: false })), 5000);
    };

    const handleInputChange = (field, value) => {
        setData(field, value);
        setLocalErrors((prev) => ({ ...prev, [field]: null }));
        clearErrors(field);
    };

    const validateStep = (currentStep) => {
        const newErrors = {};
        const requiredMsg = 'Kailangan itong punan.';

        if (currentStep === 1) {
            if (!data.first_name) newErrors.first_name = requiredMsg;
            if (!data.last_name) newErrors.last_name = requiredMsg;
            if (!data.sex) newErrors.sex = requiredMsg;
            if (!data.dob_month) newErrors.dob_month = requiredMsg;
            if (!data.dob_day) newErrors.dob_day = requiredMsg;
            if (!data.dob_year) newErrors.dob_year = requiredMsg;
        } else if (currentStep === 2) {
            if (!data.house_number) newErrors.house_number = requiredMsg;
            if (!data.purok_street) newErrors.purok_street = requiredMsg;
        } else if (currentStep === 3) {
            if (!data.contact_number) newErrors.contact_number = requiredMsg;
            else if (!/^09\d{9}$/.test(data.contact_number))
                newErrors.contact_number = 'Dapat magsimula sa 09 at may 11 numero.';

            if (!data.password) newErrors.password = requiredMsg;
            else if (data.password.length < 8)
                newErrors.password = 'Dapat ay hindi bababa sa 8 characters.';

            if (data.password !== data.password_confirmation)
                newErrors.password_confirmation = 'Hindi magtugma ang password.';
        }

        setLocalErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };

    const nextStep = () => {
        if (validateStep(step)) {
            setStep(step + 1);
        }
    };

    const prevStep = () => {
        setStep(step - 1);
    };

    const handleFileChange = (e, field) => {
        const file = e.target.files[0];
        if (!file) return;

        const maxSize = 5 * 1024 * 1024; // 5MB
        if (file.size > maxSize) {
            triggerToast('Ang file ay masyadong malaki. Maximum size ay 5MB.');
            e.target.value = '';
            return;
        }

        setData(field, file);
        setLocalErrors((prev) => ({ ...prev, [field]: null }));
        clearErrors(field);

        const reader = new FileReader();
        reader.onload = (e) => {
            setPreviews((prev) => ({
                ...prev,
                [field === 'id_photo_path' ? 'id' : 'selfie']: e.target.result,
            }));
        };
        reader.readAsDataURL(file);
    };

    const submit = (e) => {
        e.preventDefault();
        post(route('signup.post'), {
            onFinish: () => {
                reset('password', 'password_confirmation');
            },
            onSuccess: () => {
                sessionStorage.removeItem('bdls_signup_draft');
                sessionStorage.removeItem('bdls_signup_step');
            },
            onError: (errs) => {
                // BACKEND ERROR ROUTER & TOAST NOTIFICATION (ONLY RUNS ON SUBMIT)
                if (Object.keys(errs).length > 0) {
                    triggerToast(Object.values(errs)[0]);

                    if (
                        errs.first_name ||
                        errs.middle_name ||
                        errs.last_name ||
                        errs.suffix ||
                        errs.sex ||
                        errs.dob_month ||
                        errs.dob_day ||
                        errs.dob_year
                    ) {
                        setStep(1);
                    } else if (errs.house_number || errs.purok_street) {
                        setStep(2);
                    } else if (
                        errs.contact_number ||
                        errs.email ||
                        errs.password ||
                        errs.password_confirmation
                    ) {
                        setStep(3);
                    } else if (errs.id_photo_path || errs.selfie_photo_path || errs.terms) {
                        setStep(4);
                    }
                }
            },
        });
    };

    return (
        <div className="flex min-h-screen flex-col justify-center bg-slate-50 py-10 font-sans text-slate-900 antialiased">
            <Head title="Mag-Signup - Barangay Doña Lucia" />

            {/* Toast Container - ALWAYS RENDERED with transition classes */}
            <div className="pointer-events-none fixed top-6 left-1/2 z-[64] flex w-full max-w-md -translate-x-1/2 transform flex-col gap-3 px-4">
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
                <div className="mb-2 flex items-center justify-between">
                    <Link
                        href="/"
                        onClick={() => sessionStorage.clear()}
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

                    <button
                        type="button"
                        onClick={() => {
                            if (
                                confirm(
                                    'Sigurado ka bang gusto mong burahin lahat ng tina-type mo at umpisahan muli?'
                                )
                            ) {
                                sessionStorage.clear();
                                window.location.reload();
                            }
                        }}
                        className="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-slate-400 transition-all duration-200 hover:text-slate-700 focus:outline-none active:scale-95"
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
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                            ></path>
                        </svg>
                        I-reset ang Form
                    </button>
                </div>

                <div className="mb-8 text-center">
                    <h1 className="text-3xl font-extrabold tracking-tight text-slate-900">
                        Gumawa ng Account
                    </h1>
                    <p className="mt-2 text-slate-600">
                        Kumpletuhin ang 4 steps upang magkaroon ng account.
                    </p>
                </div>

                <div className="relative rounded-2xl border border-slate-100 bg-white p-6 shadow-xl md:p-10">
                    {/* Progress Bar */}
                    <div className="relative mb-10">
                        <div className="absolute top-5 left-[12.5%] z-0 h-1 w-[75%] bg-slate-200">
                            <div
                                className="absolute top-0 left-0 z-0 h-1 bg-red-600 transition-all duration-300 ease-in-out"
                                style={{ width: `${((step - 1) / 3) * 100}%` }}
                            ></div>
                        </div>

                        <div className="relative z-10 flex justify-between">
                            {[1, 2, 3, 4].map((s) => (
                                <div key={s} className="flex w-1/4 flex-col items-center">
                                    <div
                                        className={`flex h-10 w-10 items-center justify-center rounded-full font-bold shadow-md transition-colors duration-300 ${step >= s ? 'bg-red-600 text-white' : 'bg-slate-200 text-slate-500'}`}
                                    >
                                        {s}
                                    </div>
                                    <span className="mt-2 text-center text-xs font-semibold text-slate-500 md:text-sm">
                                        {s === 1 && 'Personal'}
                                        {s === 2 && 'Tirahan'}
                                        {s === 3 && 'Account'}
                                        {s === 4 && 'Verification'}
                                    </span>
                                </div>
                            ))}
                        </div>
                    </div>

                    <form onSubmit={submit} novalidate>
                        {/* Step 1 */}
                        {step === 1 && (
                            <div id="step1">
                                <h2 className="mb-6 text-2xl font-bold text-gray-800">
                                    Personal Information
                                </h2>
                                <div className="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-4">
                                    <div>
                                        <label className="mb-1 block text-sm font-semibold text-slate-700">
                                            First Name <span className="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            placeholder="First Name *"
                                            autoComplete="given-name"
                                            value={data.first_name}
                                            onChange={(e) =>
                                                handleInputChange('first_name', e.target.value)
                                            }
                                            className={`w-full rounded-lg border px-4 py-3 outline-none focus:ring-2 focus:ring-slate-900 ${localErrors.first_name || errors.first_name ? 'border-red-500 ring-1 ring-red-500' : 'border-slate-300'}`}
                                        />
                                        {(localErrors.first_name || errors.first_name) && (
                                            <p className="mt-1 text-sm text-red-500">
                                                {localErrors.first_name || errors.first_name}
                                            </p>
                                        )}
                                    </div>
                                    <div>
                                        <label className="mb-1 block text-sm font-semibold text-slate-700">
                                            Middle Name
                                        </label>
                                        <input
                                            type="text"
                                            placeholder="Middle Name"
                                            value={data.middle_name}
                                            onChange={(e) =>
                                                handleInputChange('middle_name', e.target.value)
                                            }
                                            className="w-full rounded-lg border border-slate-300 px-4 py-3 outline-none focus:ring-2 focus:ring-slate-900"
                                        />
                                    </div>
                                    <div>
                                        <label className="mb-1 block text-sm font-semibold text-slate-700">
                                            Last Name <span className="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            placeholder="Last Name *"
                                            autoComplete="family-name"
                                            value={data.last_name}
                                            onChange={(e) =>
                                                handleInputChange('last_name', e.target.value)
                                            }
                                            className={`w-full rounded-lg border px-4 py-3 outline-none focus:ring-2 focus:ring-slate-900 ${localErrors.last_name || errors.last_name ? 'border-red-500 ring-1 ring-red-500' : 'border-slate-300'}`}
                                        />
                                        {(localErrors.last_name || errors.last_name) && (
                                            <p className="mt-1 text-sm text-red-500">
                                                {localErrors.last_name || errors.last_name}
                                            </p>
                                        )}
                                    </div>
                                    <div>
                                        <label className="mb-1 block text-sm font-semibold text-slate-700">
                                            Suffix (Optional)
                                        </label>
                                        <select
                                            value={data.suffix}
                                            onChange={(e) =>
                                                handleInputChange('suffix', e.target.value)
                                            }
                                            className="w-full cursor-pointer rounded-lg border border-slate-300 px-4 py-3 outline-none focus:ring-2 focus:ring-slate-900"
                                        >
                                            <option value="">Wala</option>
                                            <option value="Jr.">Jr.</option>
                                            <option value="Sr.">Sr.</option>
                                            <option value="II">II</option>
                                            <option value="III">III</option>
                                            <option value="IV">IV</option>
                                        </select>
                                    </div>
                                </div>

                                <div className="mb-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <div>
                                        <label className="mb-1 block text-sm font-semibold text-slate-700">
                                            Kasarian (Sex) <span className="text-red-500">*</span>
                                        </label>
                                        <select
                                            value={data.sex}
                                            onChange={(e) =>
                                                handleInputChange('sex', e.target.value)
                                            }
                                            className={`w-full cursor-pointer rounded-lg border px-4 py-3 outline-none focus:ring-2 focus:ring-slate-900 ${localErrors.sex || errors.sex ? 'border-red-500 ring-1 ring-red-500' : 'border-slate-300'}`}
                                        >
                                            <option value="">-- Pumili ng Kasarian --</option>
                                            <option value="Male">Lalaki (Male)</option>
                                            <option value="Female">Babae (Female)</option>
                                        </select>
                                        {(localErrors.sex || errors.sex) && (
                                            <p className="mt-1 text-sm text-red-500">
                                                {localErrors.sex || errors.sex}
                                            </p>
                                        )}
                                    </div>

                                    <div>
                                        <label className="mb-1 block text-sm font-semibold text-slate-700">
                                            Date of Birth <span className="text-red-500">*</span>
                                        </label>
                                        <div className="grid grid-cols-3 gap-2">
                                            <select
                                                value={data.dob_month}
                                                onChange={(e) =>
                                                    handleInputChange('dob_month', e.target.value)
                                                }
                                                className={`w-full rounded-lg border px-3 py-3 outline-none focus:ring-2 focus:ring-slate-900 ${localErrors.dob_month || errors.dob_month ? 'border-red-500 ring-1 ring-red-500' : 'border-slate-300'}`}
                                            >
                                                <option value="">Month</option>
                                                <option value="01">January</option>
                                                <option value="02">February</option>
                                                <option value="03">March</option>
                                                <option value="04">April</option>
                                                <option value="05">May</option>
                                                <option value="06">June</option>
                                                <option value="07">July</option>
                                                <option value="08">August</option>
                                                <option value="09">September</option>
                                                <option value="10">October</option>
                                                <option value="11">November</option>
                                                <option value="12">December</option>
                                            </select>
                                            <select
                                                value={data.dob_day}
                                                onChange={(e) =>
                                                    handleInputChange('dob_day', e.target.value)
                                                }
                                                className={`w-full rounded-lg border px-3 py-3 outline-none focus:ring-2 focus:ring-slate-900 ${localErrors.dob_day || errors.dob_day ? 'border-red-500 ring-1 ring-red-500' : 'border-slate-300'}`}
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
                                                className={`w-full rounded-lg border px-3 py-3 outline-none focus:ring-2 focus:ring-slate-900 ${localErrors.dob_year || errors.dob_year ? 'border-red-500 ring-1 ring-red-500' : 'border-slate-300'}`}
                                            >
                                                <option value="">Year</option>
                                                {Array.from(
                                                    { length: 126 },
                                                    (_, i) => new Date().getFullYear() - i
                                                ).map((y) => (
                                                    <option key={y} value={y}>
                                                        {y}
                                                    </option>
                                                ))}
                                            </select>
                                        </div>
                                        {(localErrors.dob_month ||
                                            localErrors.dob_day ||
                                            localErrors.dob_year ||
                                            errors.dob_month ||
                                            errors.dob_day ||
                                            errors.dob_year) && (
                                            <p className="mt-1 text-sm text-red-500">
                                                Kailangan itong punan.
                                            </p>
                                        )}
                                    </div>
                                </div>

                                <div className="mt-8 flex w-full justify-end">
                                    <button
                                        type="button"
                                        onClick={nextStep}
                                        className="w-full rounded-xl bg-slate-900 px-8 py-3 font-bold text-white transition-all duration-200 hover:bg-slate-800 active:scale-95 md:w-auto"
                                    >
                                        Next Step
                                    </button>
                                </div>
                            </div>
                        )}

                        {/* Step 2 */}
                        {step === 2 && (
                            <div id="step2">
                                <h2 className="mb-6 text-2xl font-bold text-gray-800">
                                    Tirahan (Address)
                                </h2>
                                <div className="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div>
                                        <label className="mb-1 block text-sm font-semibold text-slate-700">
                                            House No. <span className="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            placeholder="House No. *"
                                            autoComplete="off"
                                            value={data.house_number}
                                            onChange={(e) =>
                                                handleInputChange('house_number', e.target.value)
                                            }
                                            className={`w-full rounded-lg border px-4 py-3 outline-none focus:ring-2 focus:ring-slate-900 ${localErrors.house_number || errors.house_number ? 'border-red-500 ring-1 ring-red-500' : 'border-slate-300'}`}
                                        />
                                        {(localErrors.house_number || errors.house_number) && (
                                            <p className="mt-1 text-sm text-red-500">
                                                {localErrors.house_number || errors.house_number}
                                            </p>
                                        )}
                                    </div>
                                    <div>
                                        <label className="mb-1 block text-sm font-semibold text-slate-700">
                                            Purok / Street <span className="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            placeholder="Purok/Street *"
                                            autoComplete="off"
                                            value={data.purok_street}
                                            onChange={(e) =>
                                                handleInputChange('purok_street', e.target.value)
                                            }
                                            className={`w-full rounded-lg border px-4 py-3 outline-none focus:ring-2 focus:ring-slate-900 ${localErrors.purok_street || errors.purok_street ? 'border-red-500 ring-1 ring-red-500' : 'border-slate-300'}`}
                                        />
                                        {(localErrors.purok_street || errors.purok_street) && (
                                            <p className="mt-1 text-sm text-red-500">
                                                {localErrors.purok_street || errors.purok_street}
                                            </p>
                                        )}
                                    </div>
                                </div>

                                <div className="mt-8 flex w-full flex-col-reverse items-stretch justify-between gap-4 md:flex-row">
                                    <button
                                        type="button"
                                        onClick={prevStep}
                                        className="w-full rounded-xl border-2 border-slate-300 bg-transparent px-8 py-3 font-bold text-slate-700 transition-all duration-200 hover:bg-slate-100 active:scale-95 md:w-auto"
                                    >
                                        Back
                                    </button>
                                    <button
                                        type="button"
                                        onClick={nextStep}
                                        className="w-full rounded-xl bg-slate-900 px-8 py-3 font-bold text-white transition-all duration-200 hover:bg-slate-800 active:scale-95 md:w-auto"
                                    >
                                        Next Step
                                    </button>
                                </div>
                            </div>
                        )}

                        {/* Step 3 */}
                        {step === 3 && (
                            <div id="step3">
                                <h2 className="mb-6 text-2xl font-bold text-gray-800">
                                    Account Details
                                </h2>
                                <div className="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div>
                                        <label className="mb-1 block text-sm font-semibold text-slate-700">
                                            Mobile Number <span className="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="tel"
                                            placeholder="09XXXXXXXXX"
                                            autoComplete="tel"
                                            value={data.contact_number}
                                            onChange={(e) =>
                                                handleInputChange(
                                                    'contact_number',
                                                    e.target.value.replace(/[^0-9]/g, '')
                                                )
                                            }
                                            maxLength="11"
                                            className={`w-full rounded-lg border px-4 py-3 outline-none focus:ring-2 focus:ring-slate-900 ${localErrors.contact_number || errors.contact_number ? 'border-red-500 ring-1 ring-red-500' : 'border-slate-300'}`}
                                        />
                                        {(localErrors.contact_number || errors.contact_number) && (
                                            <p className="mt-1 text-sm text-red-500">
                                                {localErrors.contact_number ||
                                                    errors.contact_number}
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
                                            autoComplete="email"
                                            value={data.email}
                                            onChange={(e) =>
                                                handleInputChange('email', e.target.value)
                                            }
                                            className="w-full rounded-lg border border-slate-300 px-4 py-3 outline-none focus:ring-2 focus:ring-slate-900"
                                        />
                                        {errors.email && (
                                            <p className="mt-1 text-sm text-red-500">
                                                {errors.email}
                                            </p>
                                        )}
                                    </div>
                                </div>

                                <div className="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div>
                                        <label className="mb-1 block text-sm font-semibold text-slate-700">
                                            Password <span className="text-red-500">*</span>
                                        </label>
                                        <div className="relative">
                                            <input
                                                type={showPassword ? 'text' : 'password'}
                                                value={data.password}
                                                onChange={(e) =>
                                                    handleInputChange('password', e.target.value)
                                                }
                                                autoComplete="new-password"
                                                className={`w-full rounded-lg border px-4 py-3 pr-20 outline-none focus:ring-2 focus:ring-slate-900 ${localErrors.password || errors.password ? 'border-red-500 ring-1 ring-red-500' : 'border-slate-300'}`}
                                            />
                                            <button
                                                type="button"
                                                onClick={() => setShowPassword(!showPassword)}
                                                className="absolute top-1/2 right-2 -translate-y-1/2 rounded-md p-3 text-xs font-bold text-slate-400 transition-all hover:text-slate-900 active:scale-95 active:bg-slate-200"
                                            >
                                                {showPassword ? 'HIDE' : 'SHOW'}
                                            </button>
                                        </div>
                                        {(localErrors.password || errors.password) && (
                                            <p className="mt-1 text-sm text-red-500">
                                                {localErrors.password || errors.password}
                                            </p>
                                        )}
                                    </div>
                                    <div>
                                        <label className="mb-1 block text-sm font-semibold text-slate-700">
                                            Confirm Password <span className="text-red-500">*</span>
                                        </label>
                                        <div className="relative">
                                            <input
                                                type={showConfirmPassword ? 'text' : 'password'}
                                                value={data.password_confirmation}
                                                onChange={(e) =>
                                                    handleInputChange(
                                                        'password_confirmation',
                                                        e.target.value
                                                    )
                                                }
                                                autoComplete="new-password"
                                                className={`w-full rounded-lg border px-4 py-3 pr-20 outline-none focus:ring-2 focus:ring-slate-900 ${localErrors.password_confirmation || errors.password_confirmation ? 'border-red-500 ring-1 ring-red-500' : 'border-slate-300'}`}
                                            />
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    setShowConfirmPassword(!showConfirmPassword)
                                                }
                                                className="absolute top-1/2 right-2 -translate-y-1/2 rounded-md p-3 text-xs font-bold text-slate-400 transition-all hover:text-slate-900 active:scale-95 active:bg-slate-200"
                                            >
                                                {showConfirmPassword ? 'HIDE' : 'SHOW'}
                                            </button>
                                        </div>
                                        {(localErrors.password_confirmation ||
                                            errors.password_confirmation) && (
                                            <p className="mt-1 text-sm text-red-500">
                                                {localErrors.password_confirmation ||
                                                    errors.password_confirmation}
                                            </p>
                                        )}
                                    </div>
                                </div>

                                <div className="mt-8 flex w-full flex-col-reverse items-stretch justify-between gap-4 md:flex-row">
                                    <button
                                        type="button"
                                        onClick={prevStep}
                                        className="w-full rounded-xl border-2 border-slate-300 bg-transparent px-8 py-3 font-bold text-slate-700 transition-all duration-200 hover:bg-slate-100 active:scale-95 md:w-auto"
                                    >
                                        Back
                                    </button>
                                    <button
                                        type="button"
                                        onClick={nextStep}
                                        className="w-full rounded-xl bg-slate-900 px-8 py-3 font-bold text-white transition-all duration-200 hover:bg-slate-800 active:scale-95 md:w-auto"
                                    >
                                        Next Step
                                    </button>
                                </div>
                            </div>
                        )}

                        {/* Step 4 */}
                        {step === 4 && (
                            <div id="step4">
                                <h2 className="mb-2 text-2xl font-bold text-gray-800">
                                    Account Verification
                                </h2>
                                <p className="mb-6 text-sm text-slate-500">
                                    Para sa seguridad ng iyong account, kailangan itong i-verify ng
                                    Barangay Administrator. Paki-upload ang mga sumusunod.
                                </p>

                                <div className="mb-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <div className="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                        <label className="mb-2 block text-sm font-semibold text-slate-700">
                                            Upload Valid ID <span className="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="file"
                                            ref={idInputRef}
                                            onChange={(e) => handleFileChange(e, 'id_photo_path')}
                                            accept="image/*"
                                            className="sr-only"
                                        />
                                        <button
                                            type="button"
                                            onClick={() => idInputRef.current.click()}
                                            className="mb-4 w-full rounded-md bg-slate-900 px-6 py-2 font-medium text-white transition-all duration-200 hover:bg-slate-800 active:scale-95"
                                        >
                                            Choose ID Image
                                        </button>
                                        <div className="flex h-48 w-full items-center justify-center overflow-hidden rounded-lg border-2 border-dashed border-slate-300 bg-white">
                                            {!previews.id ? (
                                                <span className="text-sm text-slate-400">
                                                    No image selected
                                                </span>
                                            ) : (
                                                <img
                                                    src={previews.id}
                                                    className="h-full w-full object-cover"
                                                />
                                            )}
                                        </div>
                                        {errors.id_photo_path && (
                                            <p className="mt-1 text-sm text-red-500">
                                                {errors.id_photo_path}
                                            </p>
                                        )}
                                    </div>

                                    <div className="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                        <label className="mb-2 block text-sm font-semibold text-slate-700">
                                            Upload Selfie <span className="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="file"
                                            ref={selfieInputRef}
                                            onChange={(e) =>
                                                handleFileChange(e, 'selfie_photo_path')
                                            }
                                            accept="image/*"
                                            className="sr-only"
                                        />
                                        <button
                                            type="button"
                                            onClick={() => selfieInputRef.current.click()}
                                            className="mb-4 w-full rounded-md bg-slate-900 px-6 py-2 font-medium text-white transition-all duration-200 hover:bg-slate-800 active:scale-95"
                                        >
                                            Choose Selfie Image
                                        </button>
                                        <div className="flex h-48 w-full items-center justify-center overflow-hidden rounded-lg border-2 border-dashed border-slate-300 bg-white">
                                            {!previews.selfie ? (
                                                <span className="text-sm text-slate-400">
                                                    No image selected
                                                </span>
                                            ) : (
                                                <img
                                                    src={previews.selfie}
                                                    className="h-full w-full object-cover"
                                                />
                                            )}
                                        </div>
                                        {errors.selfie_photo_path && (
                                            <p className="mt-1 text-sm text-red-500">
                                                {errors.selfie_photo_path}
                                            </p>
                                        )}
                                    </div>
                                </div>

                                <div className="mb-8 flex items-start gap-3 rounded-lg border border-slate-200 bg-slate-50 p-4">
                                    <input
                                        type="checkbox"
                                        id="terms"
                                        checked={data.terms}
                                        onChange={(e) =>
                                            handleInputChange('terms', e.target.checked)
                                        }
                                        className="mt-1 h-5 w-5 cursor-pointer rounded border-slate-300 text-slate-900 focus:ring-slate-900"
                                    />
                                    <label
                                        htmlFor="terms"
                                        className="cursor-pointer text-sm text-slate-700"
                                    >
                                        Nabasa ko at sumasang-ayon ako sa{' '}
                                        <button
                                            type="button"
                                            onClick={() => setIsPrivacyOpen(true)}
                                            className="font-bold text-red-600 hover:underline"
                                        >
                                            Privacy Policy
                                        </button>{' '}
                                        at{' '}
                                        <button
                                            type="button"
                                            onClick={() => setIsTermsOpen(true)}
                                            className="font-bold text-red-600 hover:underline"
                                        >
                                            Terms & Conditions
                                        </button>{' '}
                                        ng BDLS.
                                    </label>
                                </div>

                                <div className="mt-8 flex w-full flex-col-reverse items-stretch justify-between gap-4 md:flex-row">
                                    <button
                                        type="button"
                                        onClick={prevStep}
                                        className="w-full rounded-xl border-2 border-slate-300 bg-transparent px-8 py-3 font-bold text-slate-700 transition-all duration-200 hover:bg-slate-100 active:scale-95 md:w-auto"
                                    >
                                        Back
                                    </button>
                                    <button
                                        type="submit"
                                        disabled={!data.terms || processing}
                                        className="w-full rounded-xl bg-slate-900 px-8 py-3 font-bold text-white transition-all duration-200 hover:bg-slate-800 active:scale-95 disabled:opacity-50 md:w-auto"
                                    >
                                        {processing ? 'Submitting...' : 'Submit Registration'}
                                    </button>
                                </div>
                            </div>
                        )}
                    </form>
                </div>
            </div>

            <PrivacyModal isOpen={isPrivacyOpen} onClose={() => setIsPrivacyOpen(false)} />
            <TermsModal isOpen={isTermsOpen} onClose={() => setIsTermsOpen(false)} />

            {/* Global Loader */}
            {processing && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm">
                    <div className="flex flex-col items-center gap-4 rounded-2xl bg-white p-6 shadow-2xl">
                        <svg
                            className="h-10 w-10 animate-spin text-slate-900"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                className="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                strokeWidth="4"
                            ></circle>
                            <path
                                className="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                            ></path>
                        </svg>
                        <p className="text-sm font-bold text-slate-800">Pinoproseso...</p>
                    </div>
                </div>
            )}
        </div>
    );
}
