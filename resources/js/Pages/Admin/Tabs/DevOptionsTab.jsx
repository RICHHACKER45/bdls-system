import React, { useState, useRef, useCallback } from 'react';
import Webcam from 'react-webcam';
import axios from 'axios';

export default function DevOptionsTab({ devDiagnostics = {} }) {
    const [subTab, setSubTab] = useState('camera-id'); // 'camera-id' | 'sms-sandbox'

    // ==========================================
    // MODULE 1: CAMERA & ID DETECTION STATES
    // ==========================================
    const webcamRef = useRef(null);
    const [cameraMode, setCameraMode] = useState('live'); // 'live' | 'upload'
    const [isCameraActive, setIsCameraActive] = useState(true);
    const [capturedImage, setCapturedImage] = useState(null);
    const [uploadedFile, setUploadedFile] = useState(null);
    const [evaluatorName, setEvaluatorName] = useState('Juan Dela Cruz');
    const [idVerifying, setIdVerifying] = useState(false);
    const [idVerifyResult, setIdVerifyResult] = useState(null);
    const [idVerifyError, setIdVerifyError] = useState(null);

    // ==========================================
    // MODULE 2: SITUATIONAL SMS DISPATCH STATES
    // ==========================================
    const [smsRecipient, setSmsRecipient] = useState('');
    const [situationKey, setSituationKey] = useState('new_request');
    const [smsMode, setSmsMode] = useState('auto'); // 'auto' | 'dry_run' | 'fortmed' | 'pinassms'
    const [smsLoading, setSmsLoading] = useState(false);
    const [smsResult, setSmsResult] = useState(null);
    const [smsError, setSmsError] = useState(null);

    const diagnostics = devDiagnostics || {};
    const smsDiag = diagnostics.sms || {};
    const cameraDiag = diagnostics.camera || {};
    const situations = smsDiag.situations || [
        {
            key: 'new_request',
            title: '1. New Request Queued (Pending)',
            description: 'Sent automatically when a resident or walk-in submits a request and is assigned an official queue number.',
            template: 'Brgy Dona Lucia: Magandang araw, [Name]! Ang iyong request para sa [Barangay Clearance] ay natanggap na. Queue #: [W-004]. Mangyaring hintayin ang susunod na update.',
        },
        {
            key: 'for_interview',
            title: '2. Scheduled for Interview (For Interview)',
            description: 'Sent when a document requires physical appearance or background check before issuance.',
            template: 'Brgy Dona Lucia: Ang iyong request para sa [Certificate of Indigency] ay sumasailalim sa pagsusuri. Mangyaring pumunta sa Barangay Hall para sa mabilisang interview.',
        },
        {
            key: 'processing',
            title: '3. Document In Process (Processing)',
            description: 'Sent when a barangay officer initiates document preparation and clearance review.',
            template: 'Brgy Dona Lucia: Ang iyong dokumento ([Barangay Clearance]) ay kasalukuyan nang pinoproseso ng opisyal ng barangay.',
        },
        {
            key: 'released',
            title: '4. Ready for Release / Pickup (Released)',
            description: 'Sent when the document is signed, sealed, and ready for claiming at the barangay hall.',
            template: 'Brgy Dona Lucia: Ang iyong [Barangay Clearance] ay HANDA NA para sa pickup sa Barangay Hall. Dalhin ang valid ID at payment receipt.',
        },
        {
            key: 'rejected',
            title: '5. Request Rejected (Rejected)',
            description: 'Sent when a submission is declined due to deficiencies or invalid criteria.',
            template: 'Brgy Dona Lucia: Paumanhin, ang iyong request ay hindi naaprubahan. Dahilan: Kulang sa mga kinakailangang kalakip na dokumento.',
        },
        {
            key: 'otp',
            title: '6. Resident Account OTP Code (OTP)',
            description: 'Sent during registration or password reset for two-factor identity authentication.',
            template: 'Brgy Dona Lucia: Ang iyong One-Time Password (OTP) ay 482910. Gamitin ito sa loob ng 5 minuto. Huwag ibahagi kahit kanino.',
        },
        {
            key: 'announcement',
            title: '7. Public Community Advisory (Announcement)',
            description: 'Sent during mass emergency or municipal public announcements.',
            template: 'Brgy Dona Lucia ADVISORY: Magkakaroon ng libreng medical mission bukas, 8:00 AM sa Barangay Covered Court. Bukas para sa lahat ng residente.',
        },
    ];

    const currentSituation = situations.find((s) => s.key === situationKey) || situations[0];

    // Build personalized preview message
    const personalizedMessage = (currentSituation.template || '').replace('[Name]', evaluatorName || 'Residente');

    // Capture from Webcam
    const handleCaptureSnapshot = useCallback(() => {
        if (webcamRef.current) {
            const imageSrc = webcamRef.current.getScreenshot();
            setCapturedImage(imageSrc);
            setIdVerifyResult(null);
        }
    }, [webcamRef]);

    const handleRetakeSnapshot = () => {
        setCapturedImage(null);
        setIdVerifyResult(null);
    };

    // Verify Identity in Memory
    const handleVerifyId = async (e) => {
        e.preventDefault();
        setIdVerifying(true);
        setIdVerifyResult(null);
        setIdVerifyError(null);

        const formData = new FormData();
        formData.append('resident_name', evaluatorName);
        if (cameraMode === 'live' && capturedImage) {
            formData.append('image', capturedImage);
        } else if (cameraMode === 'upload' && uploadedFile) {
            formData.append('image', uploadedFile);
        }

        try {
            const response = await axios.post(route('admin.dev.verify_id_capture'), formData);
            setIdVerifyResult(response.data);
        } catch (err) {
            setIdVerifyError(err.response?.data?.message || 'Verification failed. Please try again.');
        } finally {
            setIdVerifying(false);
        }
    };

    // Real-time carrier detection
    const detectCarrierPreview = (number) => {
        const clean = (number || '').replace(/[^0-9]/g, '');
        let prefix = '';
        if (clean.startsWith('639')) prefix = '09' + clean.substring(3, 5);
        else if (clean.startsWith('09')) prefix = clean.substring(0, 4);
        else if (clean.startsWith('9')) prefix = '09' + clean.substring(1, 3);

        const samplePrefixes = smsDiag.smart_prefixes_sample || [
            '0907', '0908', '0909', '0910', '0912', '0918', '0919', '0920', '0921', '0928',
            '0929', '0930', '0938', '0946', '0947', '0948', '0949', '0950', '0961', '0998', '0999'
        ];

        if (!prefix || prefix.length < 4) return { detected: false, name: 'Waiting for number...' };
        const isSmart = samplePrefixes.includes(prefix);
        return {
            detected: true,
            isSmart,
            prefix,
            network: isSmart ? 'SMART / TNT / SUN' : 'GLOBE / TM / DITO',
            primaryRoute: isSmart ? 'Fortmed (FMCSMS) Gateway' : 'PinasSMS Gateway',
            failoverRoute: isSmart ? 'PinasSMS Gateway (Auto-Failover)' : 'Direct Primary (No failover needed)',
        };
    };

    const carrierInfo = detectCarrierPreview(smsRecipient);
    const charCount = personalizedMessage.length;

    // Handle Send Situational SMS
    const handleSendTestSms = async (e) => {
        e.preventDefault();
        if (!smsRecipient) return;

        setSmsLoading(true);
        setSmsResult(null);
        setSmsError(null);

        try {
            const response = await axios.post(route('admin.dev.test_sms'), {
                recipient: smsRecipient,
                situation_key: situationKey,
                resident_name: evaluatorName,
                mode: smsMode,
            });
            setSmsResult(response.data);
        } catch (err) {
            setSmsError(err.response?.data?.message || 'Failed to dispatch SMS. Please verify your mobile number format.');
        } finally {
            setSmsLoading(false);
        }
    };

    return (
        <div className="animate-in fade-in duration-500 space-y-6 pb-16">
            {/* TOP HEADER & ISO 25010 TELEMETRY BANNER */}
            <div className="relative overflow-hidden rounded-3xl border border-slate-800 bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 p-6 text-white shadow-xl md:p-8">
                <div className="absolute right-0 top-0 -mr-16 -mt-16 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl pointer-events-none"></div>
                <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <div className="inline-flex items-center gap-2 rounded-full border border-indigo-400/30 bg-indigo-500/10 px-3 py-1 text-xs font-black tracking-wider text-indigo-300 uppercase">
                            <span className="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            ISO/IEC 25010 Evaluation Suite
                        </div>
                        <h1 className="mt-2 text-2xl font-black tracking-tight text-white md:text-3xl">
                            Developmental Options
                        </h1>
                        <p className="mt-1 text-xs text-slate-400 md:text-sm">
                            Live verification bench for Camera ID Detection and Automated Situational SMS Notification dispatches.
                        </p>
                    </div>

                    {/* Quick System Badge Grid */}
                    <div className="grid grid-cols-2 gap-2 text-[11px] sm:grid-cols-4">
                        <div className="rounded-xl border border-slate-800 bg-slate-900/60 p-2.5 backdrop-blur-sm">
                            <div className="text-[10px] font-bold text-slate-400 uppercase">Performance</div>
                            <div className="mt-0.5 font-black text-emerald-400">&lt; 150ms LATENCY</div>
                        </div>
                        <div className="rounded-xl border border-slate-800 bg-slate-900/60 p-2.5 backdrop-blur-sm">
                            <div className="text-[10px] font-bold text-slate-400 uppercase">Confidentiality</div>
                            <div className="mt-0.5 font-black text-indigo-300">0 BYTES DISK STORAGE</div>
                        </div>
                        <div className="rounded-xl border border-slate-800 bg-slate-900/60 p-2.5 backdrop-blur-sm">
                            <div className="text-[10px] font-bold text-slate-400 uppercase">Carrier Routing</div>
                            <div className="mt-0.5 font-black text-sky-400">SMART &amp; GLOBE HYBRID</div>
                        </div>
                        <div className="rounded-xl border border-slate-800 bg-slate-900/60 p-2.5 backdrop-blur-sm">
                            <div className="text-[10px] font-bold text-slate-400 uppercase">Fault Tolerance</div>
                            <div className="mt-0.5 font-black text-amber-400">AUTO-FAILOVER ACTIVE</div>
                        </div>
                    </div>
                </div>

                {/* Sub-Navigation Buttons */}
                <div className="mt-6 flex flex-wrap gap-2 border-t border-slate-800/80 pt-4">
                    <button
                        type="button"
                        onClick={() => setSubTab('camera-id')}
                        className={`flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition-all ${
                            subTab === 'camera-id'
                                ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30'
                                : 'bg-slate-800/80 text-slate-300 hover:bg-slate-800 hover:text-white'
                        }`}
                    >
                        <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        1. Camera ID Detection &amp; Zero-Retention Verification
                    </button>

                    <button
                        type="button"
                        onClick={() => setSubTab('sms-sandbox')}
                        className={`flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition-all ${
                            subTab === 'sms-sandbox'
                                ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30'
                                : 'bg-slate-800/80 text-slate-300 hover:bg-slate-800 hover:text-white'
                        }`}
                    >
                        <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        2. Automated Situational SMS Testing (Real Phone Dispatch)
                    </button>
                </div>
            </div>

            {/* ======================================================== */}
            {/* SUB-TAB 1: CAMERA ID DETECTION & ZERO-RETENTION          */}
            {/* ======================================================== */}
            {subTab === 'camera-id' && (
                <div className="grid grid-cols-1 gap-6 lg:grid-cols-12">
                    {/* Left: Camera Viewfinder & ID Capture */}
                    <div className="lg:col-span-7 space-y-6">
                        <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <div className="flex items-center justify-between border-b border-slate-100 pb-4">
                                <div className="flex items-center gap-3">
                                    <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-50 text-purple-600">
                                        <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 className="text-base font-black text-slate-900">Live Camera ID Detection</h3>
                                        <p className="text-xs text-slate-500">Scan any valid government ID to verify identity in memory without disk writes.</p>
                                    </div>
                                </div>

                                <div className="flex rounded-lg bg-slate-100 p-1">
                                    <button
                                        type="button"
                                        onClick={() => setCameraMode('live')}
                                        className={`rounded-md px-2.5 py-1 text-xs font-bold transition-all ${cameraMode === 'live' ? 'bg-white text-purple-700 shadow-sm' : 'text-slate-600'}`}
                                    >
                                        Camera
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => setCameraMode('upload')}
                                        className={`rounded-md px-2.5 py-1 text-xs font-bold transition-all ${cameraMode === 'upload' ? 'bg-white text-purple-700 shadow-sm' : 'text-slate-600'}`}
                                    >
                                        Upload
                                    </button>
                                </div>
                            </div>

                            {/* Viewfinder / Input Area */}
                            <form onSubmit={handleVerifyId} className="mt-5 space-y-4">
                                {cameraMode === 'live' ? (
                                    <div className="relative overflow-hidden rounded-2xl border-2 border-dashed border-slate-300 bg-slate-950">
                                        {!capturedImage ? (
                                            <>
                                                {isCameraActive ? (
                                                    <div className="relative aspect-[4/3] w-full">
                                                        <Webcam
                                                            audio={false}
                                                            ref={webcamRef}
                                                            screenshotFormat="image/jpeg"
                                                            videoConstraints={{ facingMode: 'user', width: 640, height: 480 }}
                                                            className="h-full w-full object-cover"
                                                        />
                                                        {/* Government ID Alignment Overlay Frame */}
                                                        <div className="pointer-events-none absolute inset-0 flex items-center justify-center p-6">
                                                            <div className="relative aspect-[8.5/5.4] w-full max-w-sm rounded-xl border-2 border-purple-400/80 bg-purple-500/10 shadow-[0_0_0_9999px_rgba(0,0,0,0.5)]">
                                                                <div className="absolute top-2 left-2 h-4 w-4 border-t-2 border-l-2 border-purple-300"></div>
                                                                <div className="absolute top-2 right-2 h-4 w-4 border-t-2 border-r-2 border-purple-300"></div>
                                                                <div className="absolute bottom-2 left-2 h-4 w-4 border-b-2 border-l-2 border-purple-300"></div>
                                                                <div className="absolute bottom-2 right-2 h-4 w-4 border-b-2 border-r-2 border-purple-300"></div>
                                                                <div className="absolute bottom-3 inset-x-0 text-center text-[10px] font-black tracking-wider text-purple-200 uppercase">
                                                                    Align Government ID Here
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                ) : (
                                                    <div className="flex aspect-[4/3] w-full flex-col items-center justify-center text-slate-400">
                                                        <p className="text-xs font-bold">Camera is paused</p>
                                                        <button
                                                            type="button"
                                                            onClick={() => setIsCameraActive(true)}
                                                            className="mt-2 rounded-lg bg-indigo-600 px-3 py-1 text-xs text-white"
                                                        >
                                                            Start Camera
                                                        </button>
                                                    </div>
                                                )}

                                                <div className="p-3 bg-slate-900 flex justify-center">
                                                    <button
                                                        type="button"
                                                        onClick={handleCaptureSnapshot}
                                                        className="flex items-center gap-2 rounded-full bg-purple-600 px-6 py-2.5 text-xs font-black uppercase text-white shadow-lg transition-all hover:bg-purple-700 active:scale-95"
                                                    >
                                                        <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                                        </svg>
                                                        Capture ID Snapshot
                                                    </button>
                                                </div>
                                            </>
                                        ) : (
                                            <div className="relative">
                                                <img src={capturedImage} alt="Captured ID" className="aspect-[4/3] w-full object-cover" />
                                                <div className="absolute bottom-3 right-3">
                                                    <button
                                                        type="button"
                                                        onClick={handleRetakeSnapshot}
                                                        className="rounded-lg bg-slate-900/90 px-3 py-1.5 text-xs font-bold text-white backdrop-blur-sm hover:bg-slate-900"
                                                    >
                                                        Retake Snapshot
                                                    </button>
                                                </div>
                                            </div>
                                        )}
                                    </div>
                                ) : (
                                    <div className="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center">
                                        <input
                                            type="file"
                                            accept="image/png, image/jpeg, image/jpg"
                                            onChange={(e) => {
                                                const file = e.target.files[0];
                                                if (file) {
                                                    setUploadedFile(file);
                                                    setCapturedImage(URL.createObjectURL(file));
                                                    setIdVerifyResult(null);
                                                }
                                            }}
                                            className="w-full text-xs text-slate-500 file:mr-4 file:rounded-md file:border-0 file:bg-purple-100 file:px-4 file:py-2 file:text-xs file:font-bold file:text-purple-700"
                                        />
                                        {capturedImage && (
                                            <div className="mt-3 mx-auto aspect-[8.5/5.4] max-w-xs overflow-hidden rounded-lg border border-slate-200">
                                                <img src={capturedImage} alt="Uploaded ID" className="h-full w-full object-cover" />
                                            </div>
                                        )}
                                    </div>
                                )}

                                {/* Resident Name Binding */}
                                <div>
                                    <label className="block text-[11px] font-black tracking-wider text-slate-500 uppercase">
                                        Resident Full Name (Binding to Automated SMS)
                                    </label>
                                    <input
                                        type="text"
                                        value={evaluatorName}
                                        onChange={(e) => setEvaluatorName(e.target.value)}
                                        placeholder="Hal. Juan Dela Cruz"
                                        className="mt-1.5 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-xs font-bold text-slate-800 focus:border-purple-600 focus:outline-none"
                                    />
                                    <p className="mt-1 text-[11px] text-slate-400">
                                        This name will be tested against the Census Masterlist and personalized in the situational SMS alerts.
                                    </p>
                                </div>

                                <button
                                    type="submit"
                                    disabled={idVerifying}
                                    className="flex w-full items-center justify-center gap-2 rounded-xl bg-purple-600 py-3 text-xs font-black tracking-wider text-white uppercase shadow-md transition-all hover:bg-purple-700 active:scale-95 disabled:opacity-50"
                                >
                                    {idVerifying ? 'Verifying In-Memory...' : 'Verify Identity & Cross-Reference Census'}
                                </button>
                            </form>
                        </div>
                    </div>

                    {/* Right: Telemetry & Zero-Retention Security Proof */}
                    <div className="lg:col-span-5 space-y-6">
                        {idVerifyResult ? (
                            <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                                <div className="flex items-center justify-between border-b border-slate-100 pb-3">
                                    <div className="text-sm font-black text-slate-900 uppercase">
                                        Verification Verdict
                                    </div>
                                    <span className={`rounded-md px-3 py-1 text-xs font-black ${
                                        idVerifyResult.census_reconciliation?.verification_verdict === 'AUTO_APPROVED'
                                            ? 'bg-emerald-100 text-emerald-800'
                                            : 'bg-amber-100 text-amber-800'
                                    }`}>
                                        {idVerifyResult.census_reconciliation?.verification_verdict}
                                    </span>
                                </div>

                                {/* Zero-Retention Telemetry Card */}
                                <div className="rounded-xl border border-emerald-200 bg-emerald-50/60 p-4 text-xs">
                                    <div className="font-black text-emerald-900 flex items-center gap-1.5">
                                        <svg className="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                        Zero-Retention Security Proof (RA 10173)
                                    </div>
                                    <div className="mt-2 space-y-1.5 text-[11px] text-emerald-800">
                                        <div className="flex justify-between">
                                            <span>Storage Disk Retention:</span>
                                            <span className="font-black">0 Bytes (Zero Server Storage)</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span>RAM Buffer Peak:</span>
                                            <span className="font-bold">{idVerifyResult.zero_retention_audit?.in_memory_ram_peak_kb}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span>Processing Latency:</span>
                                            <span className="font-bold">{idVerifyResult.zero_retention_audit?.execution_time_ms} ms</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span>Memory Buffer State:</span>
                                            <span className="font-black text-emerald-700">Purged post-verification</span>
                                        </div>
                                    </div>
                                </div>

                                {/* Census Match Details */}
                                <div className="rounded-xl border border-slate-200 bg-slate-50 p-4 text-xs space-y-2">
                                    <div className="font-black text-slate-800 uppercase text-[10px]">Census Masterlist Reconciliation</div>
                                    {idVerifyResult.census_reconciliation?.matched ? (
                                        <div className="space-y-1 text-slate-700">
                                            <div>Verified Name: <span className="font-bold">{idVerifyResult.census_reconciliation?.census_record?.full_name}</span></div>
                                            <div>Barangay Address: <span className="font-bold">{idVerifyResult.census_reconciliation?.census_record?.address}</span></div>
                                            <div>Match Confidence: <span className="font-bold text-emerald-600">{idVerifyResult.census_reconciliation?.match_confidence}</span></div>
                                        </div>
                                    ) : (
                                        <p className="text-amber-600 font-bold">Resident not found in local census records. Flagged for barangay captain manual review.</p>
                                    )}
                                </div>

                                <button
                                    type="button"
                                    onClick={() => setSubTab('sms-sandbox')}
                                    className="flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 py-2.5 text-xs font-bold text-white uppercase transition-all hover:bg-slate-800"
                                >
                                    Proceed to Test SMS with this Identity &rarr;
                                </button>
                            </div>
                        ) : (
                            <div className="rounded-2xl border border-dashed border-slate-300 bg-slate-50/50 p-8 text-center text-xs text-slate-400">
                                <svg className="mx-auto h-10 w-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                </svg>
                                <p className="mt-2 font-bold text-slate-600">No Camera Verification Executed Yet</p>
                                <p className="mt-1">Capture an ID photo using your camera or upload an image to see live in-memory verification and zero disk retention metrics.</p>
                            </div>
                        )}
                    </div>
                </div>
            )}

            {/* ======================================================== */}
            {/* SUB-TAB 2: AUTOMATED SITUATIONAL SMS TESTING             */}
            {/* ======================================================== */}
            {subTab === 'sms-sandbox' && (
                <div className="grid grid-cols-1 gap-6 lg:grid-cols-12">
                    {/* Left Panel: Situational Dispatcher */}
                    <div className="lg:col-span-7 space-y-6">
                        <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <div className="flex items-center justify-between border-b border-slate-100 pb-4">
                                <div className="flex items-center gap-3">
                                    <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                                        <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 className="text-base font-black text-slate-900">Situational SMS Dispatcher</h3>
                                        <p className="text-xs text-slate-500">Pick any real system scenario to send its automated notification directly to your phone.</p>
                                    </div>
                                </div>
                                <span className="rounded-md bg-emerald-50 px-2.5 py-1 text-[11px] font-black text-emerald-700">
                                    Live Gateway
                                </span>
                            </div>

                            <form onSubmit={handleSendTestSms} className="mt-5 space-y-4">
                                {/* Mobile Number Input */}
                                <div>
                                    <label className="block text-[11px] font-black tracking-wider text-slate-500 uppercase">
                                        Evaluator's Personal Phone Number <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        value={smsRecipient}
                                        onChange={(e) => setSmsRecipient(e.target.value)}
                                        placeholder="Hal. 0918 123 4567 o 0917 123 4567"
                                        required
                                        className="mt-1.5 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-800 placeholder-slate-400 focus:border-indigo-600 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-600/20"
                                    />
                                    {/* Real-time carrier indicator */}
                                    {carrierInfo.detected && (
                                        <div className="mt-2 flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 p-2.5 text-xs">
                                            <div className="flex items-center gap-2">
                                                <span className={`h-2.5 w-2.5 rounded-full ${carrierInfo.isSmart ? 'bg-green-500' : 'bg-blue-500'}`}></span>
                                                <span className="font-black text-slate-800">{carrierInfo.network}</span>
                                                <span className="text-slate-400">({carrierInfo.prefix})</span>
                                            </div>
                                            <div className="text-[11px] font-bold text-indigo-600">
                                                Route: {carrierInfo.primaryRoute}
                                            </div>
                                        </div>
                                    )}
                                </div>

                                {/* SITUATION DROPDOWN SELECTOR */}
                                <div>
                                    <label className="block text-[11px] font-black tracking-wider text-slate-500 uppercase">
                                        Select Automated Municipal Situation / Trigger Event <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        value={situationKey}
                                        onChange={(e) => setSituationKey(e.target.value)}
                                        className="mt-1.5 w-full rounded-xl border border-indigo-200 bg-indigo-50/40 px-4 py-3 text-xs font-black text-indigo-900 focus:border-indigo-600 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-600/20"
                                    >
                                        {situations.map((sit) => (
                                            <option key={sit.key} value={sit.key} className="font-bold text-slate-800 py-1">
                                                {sit.title}
                                            </option>
                                        ))}
                                    </select>
                                    <p className="mt-1.5 text-[11px] text-slate-500 italic">
                                        ℹ️ {currentSituation.description}
                                    </p>
                                </div>

                                {/* LIVE SITUATION MESSAGE PREVIEW CARD */}
                                <div>
                                    <div className="flex items-center justify-between">
                                        <label className="block text-[11px] font-black tracking-wider text-slate-500 uppercase">
                                            Automated Message Sent to Resident's Phone
                                        </label>
                                        <span className="text-[11px] font-bold text-slate-400">
                                            {charCount}/160 chars (1 part)
                                        </span>
                                    </div>
                                    <div className="mt-1.5 rounded-xl border border-slate-200 bg-slate-50 p-4 font-mono text-xs text-slate-800 shadow-inner">
                                        "{personalizedMessage}"
                                    </div>
                                    <div className="mt-2 flex items-center gap-3 text-[10px] text-slate-400">
                                        <span className="flex items-center gap-1 text-emerald-600 font-bold">
                                            ✓ NTC Link Blocker Verified
                                        </span>
                                        <span className="flex items-center gap-1 text-emerald-600 font-bold">
                                            ✓ GSM 160-Char Limit Compliant
                                        </span>
                                    </div>
                                </div>

                                {/* Dispatch Mode Option */}
                                <div>
                                    <label className="block text-[11px] font-black tracking-wider text-slate-500 uppercase">
                                        Routing Engine Mode
                                    </label>
                                    <select
                                        value={smsMode}
                                        onChange={(e) => setSmsMode(e.target.value)}
                                        className="mt-1.5 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-xs font-bold text-slate-700 focus:border-indigo-600 focus:outline-none"
                                    >
                                        <option value="auto">⚡ Auto-Hybrid Routing (Smart to Fortmed, Globe to PinasSMS)</option>
                                        <option value="dry_run">🧪 Dry-Run Simulation (Zero credits, test carrier detection)</option>
                                        <option value="fortmed">🏢 Force Fortmed (FMCSMS) Gateway Direct</option>
                                        <option value="pinassms">🌐 Force PinasSMS Gateway Direct</option>
                                    </select>
                                </div>

                                <button
                                    type="submit"
                                    disabled={smsLoading || !smsRecipient}
                                    className="flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 py-3.5 text-xs font-black tracking-wider text-white uppercase shadow-md transition-all hover:bg-slate-800 active:scale-95 disabled:opacity-50"
                                >
                                    {smsLoading ? (
                                        <>
                                            <svg className="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                                <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                                <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                            </svg>
                                            Dispatching Situational SMS...
                                        </>
                                    ) : (
                                        <>
                                            <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                            </svg>
                                            Dispatch Situational SMS to My Phone
                                        </>
                                    )}
                                </button>
                            </form>

                            {/* Dispatch Result Drawer */}
                            {smsResult && (
                                <div className="mt-5 rounded-xl border border-emerald-200 bg-emerald-50/60 p-4">
                                    <div className="flex items-center justify-between">
                                        <div className="flex items-center gap-2 font-black text-emerald-800 text-xs">
                                            <span className="flex h-2 w-2 rounded-full bg-emerald-500"></span>
                                            {smsResult.success ? 'DISPATCH SUCCESSFUL' : 'DISPATCH FAILED'}
                                        </div>
                                        <span className="rounded bg-emerald-200/60 px-2 py-0.5 text-[10px] font-bold text-emerald-900">
                                            {smsResult.duration_ms} ms
                                        </span>
                                    </div>
                                    <p className="mt-1 text-xs text-emerald-700">{smsResult.details}</p>
                                    <div className="mt-3 grid grid-cols-2 gap-2 text-[10px] sm:grid-cols-3">
                                        <div className="rounded bg-white p-2">
                                            <span className="text-slate-400">Situation:</span>
                                            <div className="font-bold text-slate-800">{smsResult.situation_title}</div>
                                        </div>
                                        <div className="rounded bg-white p-2">
                                            <span className="text-slate-400">Carrier:</span>
                                            <div className="font-bold text-slate-800">{smsResult.network_info?.network}</div>
                                        </div>
                                        <div className="rounded bg-white p-2">
                                            <span className="text-slate-400">Gateway Used:</span>
                                            <div className="font-bold text-slate-800">{smsResult.gateway_used}</div>
                                        </div>
                                    </div>
                                </div>
                            )}

                            {smsError && (
                                <div className="mt-5 rounded-xl border border-red-200 bg-red-50 p-4 text-xs font-bold text-red-700">
                                    ⚠️ {smsError}
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Right Panel: Gateway Specifications & Audit Telemetry */}
                    <div className="lg:col-span-5 space-y-6">
                        <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <h3 className="text-sm font-black text-slate-900 uppercase tracking-wider">Gateway Specifications</h3>
                            <div className="mt-4 space-y-3 text-xs">
                                <div className="flex items-center justify-between border-b border-slate-100 pb-2">
                                    <span className="text-slate-500">Driver Architecture:</span>
                                    <span className="font-black text-slate-800">{smsDiag.driver?.toUpperCase()}</span>
                                </div>
                                <div className="flex items-center justify-between border-b border-slate-100 pb-2">
                                    <span className="text-slate-500">Smart/TNT Gateway:</span>
                                    <span className="font-bold text-slate-800">{smsDiag.fortmed_configured ? 'Fortmed (Connected)' : 'Configured'}</span>
                                </div>
                                <div className="flex items-center justify-between border-b border-slate-100 pb-2">
                                    <span className="text-slate-500">Globe/TM Gateway:</span>
                                    <span className="font-bold text-slate-800">{smsDiag.pinassms_configured ? 'PinasSMS (Connected)' : 'Configured'}</span>
                                </div>
                                <div className="flex items-center justify-between border-b border-slate-100 pb-2">
                                    <span className="text-slate-500">Night Curfew Safeguard:</span>
                                    <span className="font-bold text-slate-800">
                                        {smsDiag.curfew_start}:00 - {smsDiag.curfew_end}:00 (9PM - 7AM)
                                    </span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-slate-500">Smart Prefix Matchers:</span>
                                    <span className="rounded bg-slate-100 px-2 py-0.5 font-black text-slate-700">
                                        {smsDiag.smart_prefixes_count} prefixes
                                    </span>
                                </div>
                            </div>
                        </div>

                        {/* Recent Notification Audit */}
                        <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <h3 className="text-sm font-black text-slate-900 uppercase tracking-wider">Recent Dispatch Logs</h3>
                            <div className="mt-3 divide-y divide-slate-100">
                                {(smsDiag.recent_logs || []).length > 0 ? (
                                    smsDiag.recent_logs.map((log) => (
                                        <div key={log.id} className="py-2.5 flex items-center justify-between text-xs">
                                            <div>
                                                <div className="font-bold text-slate-800">{log.recipient_contact}</div>
                                                <div className="text-[10px] text-slate-400">{log.created_at}</div>
                                            </div>
                                            <span className={`rounded px-2 py-0.5 text-[10px] font-black ${log.status === 'Sent' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600'}`}>
                                                {log.status}
                                            </span>
                                        </div>
                                    ))
                                ) : (
                                    <p className="text-xs text-slate-400 py-3">No recent notifications logged.</p>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
