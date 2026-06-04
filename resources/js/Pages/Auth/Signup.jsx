import React, { useState, useRef, useEffect } from 'react';
import { Link, Head, useForm } from '@inertiajs/react';

export default function Signup() {
  // UI States: 1 = Form, 2 = ID Scan, 3 = Terms & Submission
  const [uiView, setUiView] = useState(1);
  const [previews, setPreviews] = useState({ id: null });
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  const [localErrors, setLocalErrors] = useState({});
  const [toast, setToast] = useState({ visible: false, message: '' });
  
  // T&C Scroll State
  const [isScrolledToBottom, setIsScrolledToBottom] = useState(false);

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
    address: '', // PINAGSAMA NA ANG TIRAHAN
    contact_number: '',
    email: '',
    password: '',
    password_confirmation: '',
    id_photo_path: null,
    terms: false, // Magiging true lang kapag nag-scroll at nag-click ng I Agree
  });

  const triggerToast = (message) => {
    setToast({ visible: true, message });
    setTimeout(() => setToast((prev) => ({ ...prev, visible: false })), 5000);
  };

  const handleInputChange = (field, value) => {
    setData(field, value);
    setLocalErrors((prev) => ({ ...prev, [field]: null }));
    clearErrors(field);
  };

  // VALIDATION BAGO PUMUNTA SA ID SCAN
  const validateForm = () => {
    const newErrors = {};
    const requiredMsg = 'Kailangan itong punan.';

    if (!data.first_name) newErrors.first_name = requiredMsg;
    if (!data.last_name) newErrors.last_name = requiredMsg;
    if (!data.sex) newErrors.sex = requiredMsg;
    if (!data.dob_month || !data.dob_day || !data.dob_year) newErrors.dob_year = 'Kumpletuhin ang petsa ng kapanganakan.';
    if (!data.address) newErrors.address = requiredMsg;
    
    if (!data.contact_number) newErrors.contact_number = requiredMsg;
    else if (!/^09\d{9}$/.test(data.contact_number)) newErrors.contact_number = 'Dapat magsimula sa 09 at may 11 numero.';

    if (!data.password) newErrors.password = requiredMsg;
    else if (data.password.length < 8) newErrors.password = 'Dapat ay hindi bababa sa 8 characters.';

    if (data.password !== data.password_confirmation) newErrors.password_confirmation = 'Hindi magtugma ang password.';

    setLocalErrors(newErrors);
    
    if (Object.keys(newErrors).length > 0) {
      triggerToast('Paki-kumpleto ang mga required fields sa form.');
      return false;
    }
    return true;
  };

  const goToIdScan = () => {
    if (validateForm()) setUiView(2);
  };

  const goToTerms = () => {
    if (!data.id_photo_path) {
      triggerToast('Kailangan mong i-scan ang iyong Valid ID bago magpatuloy.');
      return;
    }
    setUiView(3);
  };

  // HTML5 CAMERA CAPTURE LOGIC
  const handleIdCapture = (e) => {
    const file = e.target.files;
    if (!file) return;

    const maxSize = 5 * 1024 * 1024; // 5MB limit
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

  // T&C SCROLL DETECTOR
  const handleScroll = (e) => {
    const bottom = e.target.scrollHeight - e.target.scrollTop <= e.target.clientHeight + 20;
    if (bottom) {
      setIsScrolledToBottom(true);
    }
  };

  // FINAL SUBMISSION
  const submitRegistration = (e) => {
    e.preventDefault();
    setData('terms', true); // Auto-check terms upon clicking I Agree
    
    post(route('signup.post'), {
      onFinish: () => reset('password', 'password_confirmation'),
      onError: (errs) => {
        if (Object.keys(errs).length > 0) {
          triggerToast(Object.values(errs));
          // Auto-route back based on error
          if (errs.id_photo_path) setUiView(2);
          else if (errs.terms) setUiView(3);
          else setUiView(1);
        }
      },
    });
  };

  return (
    <div className="flex min-h-screen flex-col justify-center bg-slate-50 py-10 font-sans text-slate-900 antialiased">
      <Head title="Mag-Signup - Barangay Doña Lucia" />

      {/* Global Toast */}
      <div className="pointer-events-none fixed top-6 left-1/2 z-[1] flex w-full max-w-md -translate-x-1/2 transform flex-col gap-3 px-4">
        <div className={`pointer-events-auto flex items-center gap-4 rounded-xl border-l-4 border-red-500 bg-slate-900 px-6 py-4 text-white shadow-2xl transition-all duration-500 ${toast.visible ? 'translate-y-0 opacity-100' : '-translate-y-20 opacity-0'}`}>
          <svg className="h-6 w-6 shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
          </svg>
          <div>
            <p className="text-sm font-bold">Oops! May nakitang mali.</p>
            <p className="text-sm font-medium">{toast.message}</p>
          </div>
        </div>
      </div>

      <div className="mx-auto w-full max-w-3xl px-4">
        <div className="mb-6 flex items-center justify-between">
          <Link href="/" className="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-slate-500 hover:text-red-600 transition-all">
            <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            Bumalik sa Home
          </Link>
        </div>

        <div className="relative rounded-2xl border border-slate-100 bg-white p-6 shadow-xl md:p-10">
          
          {/* =========================================
              VIEW 1: ANG PINAGSAMANG REGISTRATION FORM 
              ========================================= */}
          {uiView === 1 && (
            <div className="animate-in fade-in duration-500">
              <div className="mb-8 text-center">
                <h1 className="text-3xl font-extrabold tracking-tight text-slate-900">Registration Form</h1>
                <p className="mt-2 text-slate-500">Kumpletuhin ang mga sumusunod na detalye.</p>
              </div>

              {/* DIVIDER 1: Personal Info */}
              <div className="mb-6 flex items-center gap-4">
                <div className="flex h-8 w-8 items-center justify-center rounded-full bg-red-100 font-bold text-red-600">1</div>
                <h2 className="text-lg font-black text-slate-800 uppercase tracking-widest">Personal Information</h2>
                <div className="h-px flex-1 bg-slate-200"></div>
              </div>

              <div className="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-4">
                <div className="md:col-span-2">
                  <label className="mb-1 block text-sm font-semibold text-slate-700">First Name <span className="text-red-500">*</span></label>
                  <input type="text" placeholder="First Name" value={data.first_name} onChange={(e) => handleInputChange('first_name', e.target.value)} className={`w-full rounded-lg border px-4 py-3 outline-none focus:ring-2 focus:ring-slate-900 ${localErrors.first_name ? 'border-red-500' : 'border-slate-300'}`} />
                </div>
                <div className="md:col-span-1">
                  <label className="mb-1 block text-sm font-semibold text-slate-700">Middle Name</label>
                  <input type="text" placeholder="Middle" value={data.middle_name} onChange={(e) => handleInputChange('middle_name', e.target.value)} className="w-full rounded-lg border border-slate-300 px-4 py-3 outline-none focus:ring-2 focus:ring-slate-900" />
                </div>
                <div className="md:col-span-1">
                  <label className="mb-1 block text-sm font-semibold text-slate-700">Suffix</label>
                  <select value={data.suffix} onChange={(e) => handleInputChange('suffix', e.target.value)} className="w-full rounded-lg border border-slate-300 px-4 py-3 outline-none focus:ring-2 focus:ring-slate-900">
                    <option value="">Wala</option>
                    <option value="Jr.">Jr.</option>
                    <option value="Sr.">Sr.</option>
                    <option value="II">II</option>
                    <option value="III">III</option>
                  </select>
                </div>
                <div className="md:col-span-2">
                  <label className="mb-1 block text-sm font-semibold text-slate-700">Last Name <span className="text-red-500">*</span></label>
                  <input type="text" placeholder="Last Name" value={data.last_name} onChange={(e) => handleInputChange('last_name', e.target.value)} className={`w-full rounded-lg border px-4 py-3 outline-none focus:ring-2 focus:ring-slate-900 ${localErrors.last_name ? 'border-red-500' : 'border-slate-300'}`} />
                </div>
                <div className="md:col-span-2">
                  <label className="mb-1 block text-sm font-semibold text-slate-700">Kasarian (Sex) <span className="text-red-500">*</span></label>
                  <select value={data.sex} onChange={(e) => handleInputChange('sex', e.target.value)} className={`w-full rounded-lg border px-4 py-3 outline-none focus:ring-2 focus:ring-slate-900 ${localErrors.sex ? 'border-red-500' : 'border-slate-300'}`}>
                    <option value="">-- Pumili ng Kasarian --</option>
                    <option value="Male">Lalaki (Male)</option>
                    <option value="Female">Babae (Female)</option>
                  </select>
                </div>
                <div className="md:col-span-4">
                  <label className="mb-1 block text-sm font-semibold text-slate-700">Date of Birth <span className="text-red-500">*</span></label>
                  <div className="grid grid-cols-3 gap-2">
                    <select value={data.dob_month} onChange={(e) => handleInputChange('dob_month', e.target.value)} className={`w-full rounded-lg border px-3 py-3 ${localErrors.dob_year ? 'border-red-500' : 'border-slate-300'}`}>
                      <option value="">Month</option>
                      <option value="01">Jan</option><option value="02">Feb</option><option value="03">Mar</option><option value="04">Apr</option>
                      <option value="05">May</option><option value="06">Jun</option><option value="07">Jul</option><option value="08">Aug</option>
                      <option value="09">Sep</option><option value="10">Oct</option><option value="11">Nov</option><option value="12">Dec</option>
                    </select>
                    <select value={data.dob_day} onChange={(e) => handleInputChange('dob_day', e.target.value)} className={`w-full rounded-lg border px-3 py-3 ${localErrors.dob_year ? 'border-red-500' : 'border-slate-300'}`}>
                      <option value="">Day</option>
                      {Array.from({ length: 31 }, (_, i) => i + 1).map((d) => (<option key={d} value={d.toString().padStart(2, '0')}>{d}</option>))}
                    </select>
                    <select value={data.dob_year} onChange={(e) => handleInputChange('dob_year', e.target.value)} className={`w-full rounded-lg border px-3 py-3 ${localErrors.dob_year ? 'border-red-500' : 'border-slate-300'}`}>
                      <option value="">Year</option>
                      {Array.from({ length: 100 }, (_, i) => new Date().getFullYear() - i).map((y) => (<option key={y} value={y}>{y}</option>))}
                    </select>
                  </div>
                </div>
              </div>

              {/* DIVIDER 2: Address */}
              <div className="mb-6 flex items-center gap-4">
                <div className="flex h-8 w-8 items-center justify-center rounded-full bg-red-100 font-bold text-red-600">2</div>
                <h2 className="text-lg font-black text-slate-800 uppercase tracking-widest">Tirahan (Address)</h2>
                <div className="h-px flex-1 bg-slate-200"></div>
              </div>

              <div className="mb-8">
                <input type="text" placeholder="Hal. 123 Purok 1, Brgy. Doña Lucia *" value={data.address} onChange={(e) => handleInputChange('address', e.target.value)} className={`w-full rounded-lg border px-4 py-3 outline-none focus:ring-2 focus:ring-slate-900 ${localErrors.address ? 'border-red-500' : 'border-slate-300'}`} />
              </div>

              {/* DIVIDER 3: Account Details */}
              <div className="mb-6 flex items-center gap-4">
                <div className="flex h-8 w-8 items-center justify-center rounded-full bg-red-100 font-bold text-red-600">3</div>
                <h2 className="text-lg font-black text-slate-800 uppercase tracking-widest">Account Details</h2>
                <div className="h-px flex-1 bg-slate-200"></div>
              </div>

              <div className="mb-8 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                  <label className="mb-1 block text-sm font-semibold text-slate-700">Mobile Number <span className="text-red-500">*</span></label>
                  <input type="tel" placeholder="09XXXXXXXXX" maxLength="11" value={data.contact_number} onChange={(e) => handleInputChange('contact_number', e.target.value.replace(/[^0-9]/g, ''))} className={`w-full rounded-lg border px-4 py-3 outline-none focus:ring-2 focus:ring-slate-900 ${localErrors.contact_number ? 'border-red-500' : 'border-slate-300'}`} />
                </div>
                <div>
                  <label className="mb-1 block text-sm font-semibold text-slate-700">Email Address (Optional)</label>
                  <input type="email" placeholder="juan@email.com" value={data.email} onChange={(e) => handleInputChange('email', e.target.value)} className="w-full rounded-lg border border-slate-300 px-4 py-3 outline-none focus:ring-2 focus:ring-slate-900" />
                </div>
                <div>
                  <label className="mb-1 block text-sm font-semibold text-slate-700">Password <span className="text-red-500">*</span></label>
                  <div className="relative">
                    <input type={showPassword ? 'text' : 'password'} value={data.password} onChange={(e) => handleInputChange('password', e.target.value)} className={`w-full rounded-lg border px-4 py-3 pr-20 outline-none focus:ring-2 focus:ring-slate-900 ${localErrors.password ? 'border-red-500' : 'border-slate-300'}`} />
                    <button type="button" onClick={() => setShowPassword(!showPassword)} className="absolute top-1/2 right-2 -translate-y-1/2 rounded-md p-3 text-xs font-bold text-slate-400 hover:text-slate-900">
                      {showPassword ? 'HIDE' : 'SHOW'}
                    </button>
                  </div>
                </div>
                <div>
                  <label className="mb-1 block text-sm font-semibold text-slate-700">Confirm Password <span className="text-red-500">*</span></label>
                  <div className="relative">
                    <input type={showConfirmPassword ? 'text' : 'password'} value={data.password_confirmation} onChange={(e) => handleInputChange('password_confirmation', e.target.value)} className={`w-full rounded-lg border px-4 py-3 pr-20 outline-none focus:ring-2 focus:ring-slate-900 ${localErrors.password_confirmation ? 'border-red-500' : 'border-slate-300'}`} />
                    <button type="button" onClick={() => setShowConfirmPassword(!showConfirmPassword)} className="absolute top-1/2 right-2 -translate-y-1/2 rounded-md p-3 text-xs font-bold text-slate-400 hover:text-slate-900">
                      {showConfirmPassword ? 'HIDE' : 'SHOW'}
                    </button>
                  </div>
                </div>
              </div>

              <div className="border-t border-slate-100 pt-6">
                <button type="button" onClick={goToIdScan} className="w-full rounded-xl bg-slate-900 px-8 py-4 font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-slate-800 active:scale-95">
                  Next: I-Scan ang ID
                </button>
              </div>
            </div>
          )}

          {/* =========================================
              VIEW 2: ON-THE-SPOT ID SCANNER 
              ========================================= */}
          {uiView === 2 && (
            <div className="animate-in fade-in slide-in-from-right-8 duration-500">
              <div className="mb-6 text-center">
                <h1 className="text-2xl font-extrabold tracking-tight text-slate-900">I-Scan ang Iyong ID</h1>
                <p className="mt-2 text-sm text-slate-500">Kailangan ito para sa KYC verification ng barangay.</p>
              </div>

              <div className="mb-8 rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 p-6 text-center">
                {/* 
                  Ito ang magic HTML5 attribute: capture="environment". 
                  Sa mga mobile phones, direkta nitong bubuksan ang rear camera!
                */}
                <input
                  type="file"
                  ref={idInputRef}
                  onChange={handleIdCapture}
                  accept="image/*"
                  capture="environment" 
                  className="sr-only"
                />
                
                {!previews.id ? (
                  <div className="flex flex-col items-center justify-center py-10">
                    <div className="mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-slate-200 text-slate-500">
                      <svg className="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      </svg>
                    </div>
                    <button type="button" onClick={() => idInputRef.current.click()} className="rounded-lg bg-slate-900 px-6 py-3 font-bold text-white shadow-md hover:bg-slate-800 active:scale-95">
                      Buksan ang Camera
                    </button>
                    <p className="mt-3 text-xs text-slate-400">Siguraduhing maliwanag at nababasa ang ID.</p>
                  </div>
                ) : (
                  <div className="flex flex-col items-center">
                    <img src={previews.id} alt="ID Preview" className="mb-4 max-h-64 w-full rounded-xl object-contain shadow-sm" />
                    <button type="button" onClick={() => idInputRef.current.click()} className="rounded-lg border border-slate-300 bg-white px-6 py-2 text-sm font-bold text-slate-700 shadow-sm hover:bg-slate-100 active:scale-95">
                      Ulitin ang Pag-scan
                    </button>
                  </div>
                )}
              </div>

              <div className="flex flex-col-reverse gap-4 sm:flex-row">
                <button type="button" onClick={() => setUiView(1)} className="w-full rounded-xl border border-slate-300 bg-transparent px-8 py-4 font-bold text-slate-700 transition-all hover:bg-slate-100 active:scale-95 sm:w-1/3">
                  Bumalik
                </button>
                <button type="button" onClick={goToTerms} className="w-full rounded-xl bg-slate-900 px-8 py-4 font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-slate-800 active:scale-95 sm:w-2/3">
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
                <h1 className="text-2xl font-extrabold tracking-tight text-slate-900">Review & Agreement</h1>
                <p className="mt-1 text-sm font-bold text-red-600">⚠️ Paki-scroll hanggang ibaba bago pumayag.</p>
              </div>

              <div 
                onScroll={handleScroll}
                className="mb-6 max-h-80 w-full overflow-y-auto rounded-xl border-2 border-slate-200 bg-slate-50 p-6 text-sm text-slate-700 shadow-inner"
              >
                <h3 className="mb-2 text-lg font-black text-slate-900">Patakaran sa Privacy ng BDLS</h3>
                <p className="mb-4 text-justify">Ang Barangay Doña Lucia ay nagpapahalaga sa iyong personal na impormasyon. Ang patakarang ito ay nagpapaliwanag kung paano namin ginagamit at pino-protektahan ang iyong data. Kukunin namin ang iyong Pangalan, Address, Contact Number, at ID para sa pag-verify ng account. Ang iyong data ay naka-encrypt at naka-imbak sa mga secure servers. HINDI namin ibebenta, ipagpapalit, o ibibigay ang iyong data sa mga taong walang awtorisasyon.</p>
                
                <h3 className="mb-2 mt-6 text-lg font-black text-slate-900">Mga Tuntunin at Kundisyon</h3>
                <p className="mb-2 text-justify"><strong className="text-slate-900">1. Tamang Impormasyon:</strong> Responsibilidad ng user na siguraduhing tama at totoo ang lahat ng impormasyon. Anumang maling impormasyon ay maaaring maging dahilan ng pagka-reject ng iyong request.</p>
                <p className="mb-2 text-justify"><strong className="text-slate-900">2. Bawal ang Spam:</strong> Mahigpit na ipinagbabawal ang paggamit ng system para mang-troll o mag-spam ng mga walang kwentang service requests.</p>
                <p className="mb-2 text-justify"><strong className="text-slate-900">3. Identity Theft:</strong> Ang paggamit ng pekeng pangalan o ID ng ibang tao ay labag sa RA 10175 (Cybercrime Prevention Act). Ire-report sa awtoridad ang sinumang mahuhuli.</p>
                <p className="mt-8 text-center text-xs font-bold text-slate-400 uppercase tracking-widest">- END OF DOCUMENT -</p>
              </div>

              <div className="flex flex-col-reverse gap-4 sm:flex-row">
                <button type="button" onClick={() => setUiView(2)} className="w-full rounded-xl border border-slate-300 bg-transparent px-8 py-4 font-bold text-slate-700 transition-all hover:bg-slate-100 active:scale-95 sm:w-1/3">
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

      {/* Global Processing Loader */}
      {processing && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm">
          <div className="flex flex-col items-center gap-4 rounded-2xl bg-white p-6 shadow-2xl">
            <svg className="h-10 w-10 animate-spin text-slate-900" fill="none" viewBox="0 0 24 24">
              <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
              <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p className="text-sm font-bold text-slate-800">Pinoproseso ang Iyong Account...</p>
          </div>
        </div>
      )}

    </div>
  );
}