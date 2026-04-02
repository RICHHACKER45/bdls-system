<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Mag-Signup - Barangay Doña Lucia</title>
    @vite (['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    class="bg-slate-50 font-sans text-slate-900 antialiased min-h-screen flex flex-col justify-center py-10"
>
    <!-- TOP NAVIGATION & FORM CONTROLS -->
    <div class="max-w-3xl mx-auto w-full px-4">
        <div class="flex justify-between items-center mb-2">
            <!-- BUMALIK SA HOME BUTTON (Intentional Exit = Clear Memory) -->
            <a
                href="/"
                onclick="sessionStorage.clear()"
                class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-red-600 active:bg-slate-200 active:scale-95 focus:outline-none focus:ring-4 focus:ring-slate-200 py-2 px-4 rounded-xl transition-all duration-200"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Bumalik sa Home
            </a>

            <!-- RESET FORM BUTTON (Subtle, malayo sa "Next" buttons, may Confirmation) -->
            <button
                type="button"
                onclick="
                    if (
                        confirm(
                            'Sigurado ka bang gusto mong burahin lahat ng tina-type mo at umpisahan muli?',
                        )
                    ) {
                        sessionStorage.clear();
                        location.reload();
                    }
                "
                class="inline-flex items-center gap-2 text-sm font-semibold text-slate-400 hover:text-slate-700 active:scale-95 focus:outline-none transition-all duration-200 py-2 px-4 rounded-xl"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                I-reset ang Form
            </button>
        </div>

        <div class="text-center mb-8">
            {{-- <div
                class="w-16 h-16 bg-red-600 rounded-full mx-auto flex items-center justify-center shadow-lg mb-4"
            >
                <span class="text-white font-bold text-xl">BDLS</span>
            </div> --}}
            <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Gumawa ng Account</h1>
            <p class="text-slate-600 mt-2">Kumpletuhin ang 4 steps upang magkaroon ng account.</p>
        </div>

        <!-- PROGRESS BAR UI -->
        <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-6 md:p-10 relative">
            <div class="mb-10 relative">
                <!-- LINES CONTAINER: Nagsisimula sa 12.5% (gitna ng Step 1) at may habang 75% (hanggang gitna ng Step 4) -->
                <div class="absolute left-[12.5%] top-5 w-[75%] h-1 bg-slate-200 z-0">
                    <!-- Red Fill Line (Nasa loob na ng container para saktong sumunod) -->
                    <div
                        id="progress-line"
                        class="absolute left-0 top-0 h-1 bg-red-600 z-0 transition-all duration-300 ease-in-out"
                        style="width: 0%"
                    ></div>
                </div>

                <!-- Steps Container -->
                <div class="flex justify-between relative z-10">
                    <!-- Step 1 -->
                    <div class="flex flex-col items-center w-1/4">
                        <div
                            class="w-10 h-10 rounded-full bg-red-600 text-white flex items-center justify-center font-bold shadow-md transition-colors duration-300 indicator"
                            id="ind-1"
                        >
                            1
                        </div>
                        <span
                            class="mt-2 text-xs md:text-sm font-semibold text-slate-500 text-center"
                            >Personal</span
                        >
                    </div>

                    <!-- Step 2 -->
                    <div class="flex flex-col items-center w-1/4">
                        <div
                            class="w-10 h-10 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center font-bold transition-colors duration-300 indicator"
                            id="ind-2"
                        >
                            2
                        </div>
                        <span
                            class="mt-2 text-xs md:text-sm font-semibold text-slate-500 text-center"
                            >Tirahan</span
                        >
                    </div>

                    <!-- Step 3 -->
                    <div class="flex flex-col items-center w-1/4">
                        <div
                            class="w-10 h-10 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center font-bold transition-colors duration-300 indicator"
                            id="ind-3"
                        >
                            3
                        </div>
                        <span
                            class="mt-2 text-xs md:text-sm font-semibold text-slate-500 text-center"
                            >Account</span
                        >
                    </div>

                    <!-- Step 4 -->
                    <div class="flex flex-col items-center w-1/4">
                        <div
                            class="w-10 h-10 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center font-bold transition-colors duration-300 indicator"
                            id="ind-4"
                        >
                            4
                        </div>
                        <span
                            class="mt-2 text-xs md:text-sm font-semibold text-slate-500 text-center"
                            >Verification</span
                        >
                    </div>
                </div>
            </div>
            <!-- Registration Form -->
            <form action="{{ route('signup.post') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <!-- ========================================================-->
                <!-- STEP 1: Personal Na Impormasyon (Personal Information) -->
                <!-- ========================================================-->
                <div id="step1">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Personal Information</h2>
                
                    <!-- GRID 1: PANGALAN (Ginawang 4-Columns para sa Suffix) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                        <!-- First Name (Required) -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">First Name <span class="text-red-500">*</span></label>
                            <input type="text" id="first_name" name="first_name" maxlength="255" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-slate-900 outline-none transition-colors duration-200" />
                            <p id="error-first_name" class="hidden text-red-500 text-sm mt-1">This field is required.</p>
                        </div>
                    
                        <!-- Middle Name -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Middle Name</label>
                            <input type="text" id="middle_name" name="middle_name" maxlength="255" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-slate-900 outline-none transition-colors duration-200" />
                        </div>
                    
                        <!-- Last Name (Required) -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Last Name <span class="text-red-500">*</span></label>
                            <input type="text" id="last_name" name="last_name" maxlength="255" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-slate-900 outline-none transition-colors duration-200" />
                            <p id="error-last_name" class="hidden text-red-500 text-sm mt-1">This field is required.</p>
                        </div>
                    
                        <!-- Suffix (Optional) -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Suffix (Optional)</label>
                            <select id="suffix" name="suffix" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-slate-900 outline-none transition-colors duration-200 cursor-pointer">
                                <option value="">Wala</option>
                                <option value="Jr.">Jr.</option>
                                <option value="Sr.">Sr.</option>
                                <option value="II">II</option>
                                <option value="III">III</option>
                                <option value="IV">IV</option>
                            </select>
                        </div>
                    </div>
                
                    <!-- GRID 2: SEX & DATE OF BIRTH -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

                        <!-- Sex (Required) -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Kasarian (Sex) <span class="text-red-500">*</span></label>
                            <select id="sex" name="sex" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-slate-900 outline-none transition-colors duration-200 cursor-pointer">
                                <option value="">-- Pumili ng Kasarian --</option>
                                <option value="Male">Lalaki (Male)</option>
                                <option value="Female">Babae (Female)</option>
                            </select>
                            <p id="error-sex" class="hidden text-red-500 text-sm mt-1">This field is required.</p>
                        </div>
                    
                        <!-- Date of Birth (Custom Dropdowns) -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Date of Birth <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-3 gap-2">
                                <!-- Month -->
                                <div>
                                    <select id="dob_month" name="dob_month" required class="w-full px-3 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-slate-900 outline-none transition-colors duration-200">
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
                                    <p id="error-dob_month" class="hidden text-red-500 text-xs mt-1">Required</p>
                                </div>
                            
                                <!-- Day -->
                                <div>
                                    <select id="dob_day" name="dob_day" required class="w-full px-3 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-slate-900 outline-none transition-colors duration-200">
                                        <option value="">Day</option>
                                        @for ($i = 1; $i <= 31; $i++)
                                            <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                    <p id="error-dob_day" class="hidden text-red-500 text-xs mt-1">Required</p>
                                </div>
                            
                                <!-- Year -->
                                <div>
                                    <select id="dob_year" name="dob_year" required class="w-full px-3 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-slate-900 outline-none transition-colors duration-200">
                                        <option value="">Year</option>
                                        @for ($i = date('Y'); $i >= 1900; $i--)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                    <p id="error-dob_year" class="hidden text-red-500 text-xs mt-1">Required</p>
                                </div>
                            </div>
                        </div>
                    </div>
                
                    <!-- BUTTON CONTAINER (Step 1 - Next Only) -->
                    <div class="w-full flex justify-end mt-8">
                        <button type="button" onclick="validateAndGo('step1', 'step2')" class="w-full md:w-auto bg-slate-900 hover:bg-slate-800 active:bg-slate-700 active:scale-95 text-white font-bold py-3 px-8 rounded-xl transition-all duration-200">
                            Next Step
                        </button>
                    </div>
                </div>
                <!-- CLOSING NG STEP 1 -->

                <!-- ========================================== -->
                <!-- STEP 2: TIRAHAN (Address)                  -->
                <!-- ========================================== -->
                <div id="step2" class="hidden">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Tirahan (Address)</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <!-- House Number (Required) -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1"
                                >House No. <span class="text-red-500">*</span></label
                            >
                            <input
                                type="text"
                                id="house_number"
                                name="house_number"
                                maxlength="50"
                                required
                                class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-slate-900 outline-none transition-colors duration-200"
                            />
                            <p id="error-house_number" class="hidden text-red-500 text-sm mt-1">This field is required.</p>
                        </div>

                        <!-- Purok / Street (Required) -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1"
                                >Purok / Street <span class="text-red-500">*</span></label
                            >
                            <input
                                type="text"
                                id="purok_street"
                                name="purok_street"
                                maxlength="255"
                                required
                                class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-slate-900 outline-none transition-colors duration-200"
                            />
                            <p id="error-purok_street" class="hidden text-red-500 text-sm mt-1">This field is required.</p>
                        </div>
                    </div>

                    <!-- BUTTON CONTAINER (Consistent with Step 4) -->
                    <div
                        class="w-full flex flex-col-reverse md:flex-row justify-between items-stretch gap-4 mt-8"
                    >
                        <!-- BACK BUTTON (Ghost Style with Mobile Tap Feedback) -->
                        <button
                            type="button"
                            onclick="goBack('step2', 'step1')"
                            class="w-full md:w-auto border-2 border-slate-300 bg-transparent text-slate-700 hover:bg-slate-100 active:bg-slate-200 active:scale-95 focus:outline-none focus:ring-4 focus:ring-slate-200 font-bold py-3 px-8 rounded-xl transition-all duration-200"
                        >
                            Back
                        </button>

                        <!-- NEXT BUTTON (Solid Style) -->
                        <button
                            type="button"
                            onclick="validateAndGo('step2', 'step3')"
                            class="w-full md:w-auto bg-slate-900 hover:bg-slate-800 active:bg-slate-700 active:scale-95 text-white font-bold py-3 px-8 rounded-xl transition-all duration-200"
                        >
                            Next Step
                        </button>
                    </div>
                </div>
                <!-- CLOSING NG STEP 2 -->

                <!-- ========================================== -->
                <!-- STEP 3: ACCOUNT DETAILS                    -->
                <!-- ========================================== -->
                <div id="step3" class="hidden">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Account Details</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <!-- Mobile Number (Required - Primary Channel) -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1"
                                >Mobile Number <span class="text-red-500">*</span></label
                            >
                            <input
                                type="tel"
                                id="contact_number"
                                name="contact_number"
                                required
                                maxlength="11"
                                attern="09{9}"
                                placeholder="09XXXXXXXXX"
                                class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-slate-900 outline-none transition-colors duration-200"
                            />
                            <p id="error-contact_number" class="hidden text-red-500 text-sm mt-1">This field is required.</p>
                        </div>

                        <!-- Email (Optional Fallback based on Chapter 1) -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1"
                                >Email Address (Optional)</label
                            >
                            <input
                                type="email"
                                id="email"
                                name="email"
                                maxlength="255"
                                class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-slate-900 outline-none transition-colors duration-200"
                            />
                            <!-- Walang error paragraph dahil hindi ito required -->
                        </div>
                    </div>

                    <!-- PASSSWORD SECURITY GRID -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <!-- Password (Required, Min 8) -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1"
                                >Password <span class="text-red-500">*</span></label
                            >
                            <div class="relative">
                                <!-- Dinagdagan ang pr-20 para may espasyo ang button -->
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    minlength="8"
                                    maxlength="64"
                                    required
                                    class="w-full px-4 py-3 pr-20 rounded-lg border border-slate-300 focus:ring-2 focus:ring-slate-900 outline-none transition-colors duration-200"
                                />

                                <!-- Ang Bagong Tactile Show/Hide Button na Naka-Gitna -->
                                <button
                                    type="button"
                                    onclick="togglePassword('password')"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 p-3 rounded-md text-xs font-bold text-slate-400 hover:text-slate-900 active:text-slate-900 active:bg-slate-200 active:scale-95 select-none transition-all duration-100 focus:outline-none"
                                >
                                    SHOW
                                </button>
                            </div>
                            <p id="error-password" class="hidden text-red-500 text-sm mt-1">This field is required.</p>
                        </div>

                        <!-- Confirm Password (Required) -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1"
                                >Confirm Password <span class="text-red-500">*</span></label
                            >
                            <div class="relative">
                                <!-- Dinagdagan ang pr-20 para may espasyo ang button -->
                                <input
                                    type="password"
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    minlength="8"
                                    maxlength="64"
                                    required
                                    class="w-full px-4 py-3 pr-20 rounded-lg border border-slate-300 focus:ring-2 focus:ring-slate-900 outline-none transition-colors duration-200"
                                />

                                <!-- Ang Bagong Tactile Show/Hide Button na Naka-Gitna -->
                                <button
                                    type="button"
                                    onclick="togglePassword('password_confirmation')"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 p-3 rounded-md text-xs font-bold text-slate-400 hover:text-slate-900 active:text-slate-900 active:bg-slate-200 active:scale-95 select-none transition-all duration-100 focus:outline-none"
                                >
                                    SHOW
                                </button>
                            </div>
                            <p id="error-password_confirmation" class="hidden text-red-500 text-sm mt-1">This field is required.</p>
                        </div>
                    </div>

                    <!-- BUTTON CONTAINER (Consistent with Step 4) -->
                    <div
                        class="w-full flex flex-col-reverse md:flex-row justify-between items-stretch gap-4 mt-8"
                    >
                        <!-- BACK BUTTON (Ghost Style with Mobile Tap Feedback) -->
                        <button
                            type="button"
                            onclick="goBack('step3', 'step2')"
                            class="w-full md:w-auto border-2 border-slate-300 bg-transparent text-slate-700 hover:bg-slate-100 active:bg-slate-200 active:scale-95 focus:outline-none focus:ring-4 focus:ring-slate-200 font-bold py-3 px-8 rounded-xl transition-all duration-200"
                        >
                            Back
                        </button>

                        <!-- NEXT BUTTON (Solid Style) -->
                        <button
                            type="button"
                            onclick="validateAndGo('step3', 'step4')"
                            class="w-full md:w-auto bg-slate-900 hover:bg-slate-800 active:bg-slate-700 active:scale-95 text-white font-bold py-3 px-8 rounded-xl transition-all duration-200"
                        >
                            Next Step
                        </button>
                    </div>
                </div>
                <!-- CLOSING NG STEP 3 -->

                <!-- ========================================== -->
                <!-- STEP 4: ACCOUNT VERIFICATION (Uploads)     -->
                <!-- ========================================== -->
                <div id="step4" class="hidden">
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Account Verification</h2>
                    <p class="text-sm text-slate-500 mb-6">Para sa seguridad ng iyong account, kailangan itong i-verify ng Barangay Administrator. Paki-upload ang mga sumusunod.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Valid ID Upload -->
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                            <label class="block text-sm font-semibold text-slate-700 mb-2"
                                >Upload Valid ID <span class="text-red-500">*</span></label
                            >

                            <!-- Nakatagong File Input (sr-only) -->
                            <input
                                type="file"
                                id="id_photo_path"
                                name="id_photo_path"
                                accept="image/jpeg, image/png, image/jpg"
                                required
                                class="sr-only"
                                onchange="previewImage(event, 'preview-id')"
                            />

                            <!-- Ang Tactile Button -->
                            <button
                                type="button"
                                onclick="document.getElementById('id_photo_path').click()"
                                class="bg-slate-900 hover:bg-slate-800 active:bg-slate-700 active:scale-95 text-white font-medium py-2 px-6 rounded-md transition-all duration-200 mb-4 w-full"
                            >
                                Choose ID Image
                            </button>

                            <!-- Preview Box -->
                            <div
                                class="w-full h-48 border-2 border-dashed border-slate-300 rounded-lg flex items-center justify-center overflow-hidden bg-white"
                            >
                                <span id="placeholder-id" class="text-slate-400 text-sm"
                                    >No image selected</span
                                >
                                <img
                                    id="preview-id"
                                    src=""
                                    alt="ID Preview"
                                    class="hidden object-cover w-full h-full"
                                />
                            </div>
                        </div>

                        <!-- Selfie with ID Upload -->
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                            <label class="block text-sm font-semibold text-slate-700 mb-2"
                                >Upload Selfie with ID <span class="text-red-500">*</span></label
                            >

                            <!-- Nakatagong File Input (sr-only) -->
                            <input
                                type="file"
                                id="selfie_photo_path"
                                name="selfie_photo_path"
                                accept="image/jpeg, image/png, image/jpg"
                                required
                                class="sr-only"
                                onchange="previewImage(event, 'preview-selfie')"
                            />

                            <!-- Ang Tactile Button -->
                            <button
                                type="button"
                                onclick="document.getElementById('selfie_photo_path').click()"
                                class="bg-slate-900 hover:bg-slate-800 active:bg-slate-700 active:scale-95 text-white font-medium py-2 px-6 rounded-md transition-all duration-200 mb-4 w-full"
                            >
                                Choose Selfie Image
                            </button>

                            <!-- Preview Box -->
                            <div
                                class="w-full h-48 border-2 border-dashed border-slate-300 rounded-lg flex items-center justify-center overflow-hidden bg-white"
                            >
                                <span id="placeholder-selfie" class="text-slate-400 text-sm"
                                    >No image selected</span
                                >
                                <img
                                    id="preview-selfie"
                                    src=""
                                    alt="Selfie Preview"
                                    class="hidden object-cover w-full h-full"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- BAGONG LEGAL AGREEMENT CHECKBOX (Poka-yoke) -->
                    <div
                        class="bg-slate-50 p-4 rounded-lg border border-slate-200 flex items-start gap-3 mb-8"
                    >
                        <input
                            type="checkbox"
                            id="terms"
                            name="terms"
                            required
                            onchange="document.getElementById('submitBtn').disabled = !this.checked"
                            class="mt-1 w-5 h-5 text-slate-900 border-slate-300 rounded cursor-pointer focus:ring-slate-900"
                        />
                        <label for="terms" class="text-sm text-slate-700 cursor-pointer">
                            Nabasa ko at sumasang-ayon ako sa
                            <button
                                type="button"
                                onclick="openLegalModal('privacyModal')"
                                class="text-red-600 font-bold hover:underline transition-all focus:outline-none"
                            >
                                Privacy Policy
                            </button>
                            at
                            <button
                                type="button"
                                onclick="openLegalModal('termsModal')"
                                class="text-red-600 font-bold hover:underline transition-all focus:outline-none"
                            >
                                Terms & Conditions
                            </button>
                            ng BDLS.
                        </label>
                    </div>

                    <!-- BUTTON CONTAINER (Responsive / Dynamic Resizing) -->
                    <div
                        class="w-full flex flex-col-reverse md:flex-row justify-between items-stretch gap-4 mt-8"
                    >
                        <!-- BACK BUTTON (Ghost Style with Mobile Tap Feedback) -->
                        <button
                            type="button"
                            onclick="goBack('step4', 'step3')"
                            class="w-full md:w-auto border-2 border-slate-300 bg-transparent text-slate-700 hover:bg-slate-100 active:bg-slate-200 active:scale-95 focus:outline-none focus:ring-4 focus:ring-slate-200 font-bold py-3 px-8 rounded-xl transition-all duration-200"
                        >
                            Back
                        </button>

                        <!-- SUBMIT BUTTON -->
                        <button
                            type="submit"
                            id="submitBtn"
                            disabled
                            class="w-full md:w-auto whitespace-nowrap bg-slate-900 hover:bg-slate-800 disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100 active:bg-slate-700 active:scale-95 text-white font-bold py-3 px-8 rounded-xl transition-all duration-200"
                        >
                            Submit Registration
                        </button>
                    </div>
                </div>
                <!-- CLOSING NG STEP 4 -->
            </form>
        </div>
    </div>
    
    
    <!-- ========================================== -->
    <!--    LEGAL MODALS (Privacy & Terms)          -->
    <!-- ========================================== -->
    
    <!-- 1. PRIVACY POLICY MODAL -->
    <div id="privacyModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 transition-opacity">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[80vh] flex flex-col overflow-hidden">
            <div class="p-6 border-b border-slate-200 flex justify-between items-center bg-slate-50">
                <h2 class="text-xl font-bold text-slate-900">Patakaran sa Privacy ng BDLS</h2>
                <button type="button" onclick="closeLegalModal('privacyModal')" class="text-slate-400 hover:text-slate-800 text-2xl font-bold">
                    &times;
                </button>
            </div>
            <div class="p-6 overflow-y-auto text-sm text-slate-700 space-y-4">
                <p>Ang Barangay Doña Lucia ay nagpapahalaga sa iyong personal na impormasyon. Ang patakarang ito ay nagpapaliwanag kung paano namin ginagamit at pino-protektahan ang iyong data.</p>
                <h3 class="font-bold text-slate-900">
                    1. Anong impormasyon ang kinokolekta namin?
                </h3>
                <p>Para makagawa ng account, kukunin namin ang iyong Pangalan, Address, Contact Number, Password, litrato ng iyong ID, at isang Selfie. Ang pagbibigay ng Email ay optional. Kukunin din namin ang iyong Edad at Kasarian para matukoy ng barangay kung anong mga benepisyo o programa ang nararapat sa iyong grupo.</p>
                <h3 class="font-bold text-slate-900">
                    2. Paano namin ito itatago at pino-protektahan?
                </h3>
                <p>Ang iyong Selfie ay gagamitin bilang iyong Profile Picture sa system. Ang litrato ng iyong ID ay itatago ng system upang hindi mo na kailangang mag-pasa ulit para sa mga susunod mong transaksyon. Para sa iyong kaligtasan, <strong class="text-slate-900">ang iyong data ay naka-encrypt at naka-imbak sa mga secure servers</strong> upang maiwasan ang pagnanakaw ng impormasyon.</p>
                <h3 class="font-bold text-slate-900">3. Kanino namin ito ibinabahagi?</h3>
                <p>HINDI namin ibebenta, ipagpapalit, o ibibigay ang iyong data sa mga taong walang awtorisasyon. Ipapasa lamang ang iyong Contact Number (at Email kung meron) sa aming awtomatikong Notification System para makapagpadala sa iyo ng updates tungkol sa iyong request. Ibabahagi lamang namin ang iyong impormasyon sa mga awtoridad kung may utos ng batas o may naganap na krimen.</p>
                <h3 class="font-bold text-slate-900">
                    4. Ang Iyong Karapatan sa Data (Data Rights)
                </h3>
                <p>Maaari mong hilingin na i-update o i-delete ang iyong impormasyon sa system anumang oras sa pamamagitan ng pagpapadala ng mensahe o paglapit nang personal sa aming barangay admin.</p>
            </div>
            <div class="p-4 border-t border-slate-200 bg-slate-50 text-right">
                <button
                type="button"
                onclick="closeLegalModal('privacyModal')"
                    class="bg-slate-900 hover:bg-slate-800 text-white font-bold py-2 px-6 rounded-lg text-sm transition-all active:scale-95"
                >
                    Naintindihan Ko
                </button>
            </div>
        </div>
    </div>
    
    <!-- 2. TERMS AND CONDITIONS MODAL -->
    <div id="termsModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 transition-opacity">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[80vh] flex flex-col overflow-hidden">
            <div class="p-6 border-b border-slate-200 flex justify-between items-center bg-slate-50">
                <h2 class="text-xl font-bold text-slate-900">Mga Tuntunin at Kundisyon</h2>
                <button type="button" onclick="closeLegalModal('termsModal')" class="text-slate-400 hover:text-slate-800 text-2xl font-bold">
                    &times;
                </button>
            </div>
            <div class="p-6 overflow-y-auto text-sm text-slate-700 space-y-4">
                <p>Sa paggawa ng account sa BDLS, sumasang-ayon ka sa mga sumusunod na patakaran ng aming barangay:</p>
                <h3 class="font-bold text-slate-900">1. Responsibilidad sa Tamang Impormasyon</h3>
                <p>Responsibilidad ng user na siguraduhing tama at totoo ang lahat ng impormasyong ibibigay sa system. Anumang maling impormasyon ay maaaring maging dahilan ng pagka-antala o pagka-reject ng iyong request.</p>
                <h3 class="font-bold text-slate-900">2. Seguridad ng Account</h3>
                <p>Huwag ibigay ang iyong password sa iba. Ikaw ang responsable sa pag-iingat ng iyong account. Anumang transaksyon o request na ginawa gamit ang iyong account ay ituturing na gawa mo.</p>
                <h3 class="font-bold text-slate-900">3. Bawal ang Spam at Panliligalig</h3>
                <p>Mahigpit na ipinagbabawal ang paggamit ng system para mang-harass, mang-troll, o mag-spam ng mga walang kwentang service requests na nakakaabala sa operasyon ng barangay hall.</p>
                <h3 class="font-bold text-slate-900">
                    4. Bawal ang Paggamit ng Pagkakakilanlan ng Iba (Identity Theft)
                </h3>
                <p>Ang paggamit ng pekeng pangalan, o pag-upload ng ID at mukha ng ibang tao nang walang pahintulot ay isang krimen. Ito ay labag sa RA 10175 (Cybercrime Prevention Act of 2012). Ang sinumang mahuhuli ay ire-report sa mga awtoridad para sa legal na aksyon at agad na iba-ban ang account.</p>
                <h3 class="font-bold text-slate-900">5. Bawal ang Pangha-hack at Kriminalidad</h3>
                <p>Anumang pagsubok na nakawin ang data ng ibang residente o sirain ang system ay may katumbas na kasong kriminal at agarang pagka-ban.</p>
                <h3 class="font-bold text-slate-900">
                    6. Patakaran sa Hindi Pagkuha ng Dokumento (No-Show Policy)
                </h3>
                <p>Kapag na-aprubahan at na-text ka na ang iyong dokumento ay "Ready for Release", mangyaring kunin ito agad. <br />
                    • Kung hindi mo ito makuha sa loob ng <strong>1 linggo</strong>, papadalhan ka namin ng isa pang paalala (2nd attempt).<br />
                • Kung lumipas ang <strong>2 linggo</strong> at hindi mo pa rin kinukuha, papatawan ang iyong account ng <strong>1-linggong penalty</strong> kung saan hindi ka muna makakapag-request ng bagong dokumento sa system. Gayunpaman, maaari mo pa ring kunin ang iyong nakabinbing dokumento nang personal sa barangay hall.</p>
            </div>
            <div class="p-4 border-t border-slate-200 bg-slate-50 text-right">
                <button type="button" onclick="closeLegalModal('termsModal')" class="bg-slate-900 hover:bg-slate-800 text-white font-bold py-2 px-6 rounded-lg text-sm transition-all active:scale-95">
                    Naintindihan Ko
                </button>
            </div>
        </div>
    </div>
    
    <!-- ========================================== -->
    <!-- 1. GLOBAL LOADING SCREEN (Z-50)            -->
    <!-- ========================================== -->
    <div id="global-loader" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center transition-opacity">
        <div class="bg-white p-6 rounded-2xl shadow-2xl flex flex-col items-center gap-4 animate-bounce-slight">
            <svg class="w-10 h-10 text-slate-900 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-sm font-bold text-slate-800">Pinoproseso...</p>
        </div>
    </div>
    
    <!-- ========================================== -->
    <!-- 2. CRITICAL SERVER ERROR MODAL (TIMEOUT)   -->
    <!-- ========================================== -->
    <div id="errorModal" class="fixed inset-0 z-50 hidden bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4 transition-opacity">
        <div class="bg-white w-full max-w-sm rounded-2xl shadow-2xl overflow-hidden flex flex-col items-center text-center p-8 border-t-4 border-red-500">
            <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-slate-800 mb-2">May Problema</h3>
            <p id="errorModalMessage" class="text-sm text-slate-600 mb-6"></p>
            <button type="button" onclick="closeErrorModal()" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg transition-all active:scale-95">
                Sige, Susubukan Ulit
            </button>
        </div>
    </div>
    
    <!-- ========================================== -->
    <!-- 3. TOAST NOTIFICATION SYSTEM (Z-60)        -->
    <!-- ========================================== -->
    <div id="toast-container" class="fixed top-6 left-1/2 transform -translate-x-1/2 z-[64] flex flex-col gap-3 w-full max-w-md px-4 pointer-events-none">

        <!-- A. Laravel Backend Error Toast (Duplicate Email, etc.) -->
        @if ($errors->any())
        <div id="backend-toast" class="bg-slate-900 text-white px-6 py-4 rounded-xl shadow-2xl flex items-center gap-4 transform -translate-y-20 opacity-0 transition-all duration-500 pointer-events-auto border-l-4 border-red-500">
                <svg class="w-6 h-6 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <div>
                    <p class="font-bold text-sm">Oops! May nakitang mali.</p>
                    <p class="text-sm font-medium">{{ $errors->first() }}</p>
                </div>
            </div>
            @endif
            
        <!-- B. Javascript Frontend Error Toast (5MB Error) -->
        <div id="frontend-toast" class="hidden bg-slate-900 text-white px-6 py-4 rounded-xl shadow-2xl items-center gap-4 transform -translate-y-20 opacity-0 transition-all duration-500 pointer-events-auto border-l-4 border-red-500">
            <svg class="w-6 h-6 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <div>
                <p class="font-bold text-sm">Oops! May nakitang mali.</p>
                <p id="frontend-toast-message" class="text-sm font-medium"></p>
            </div>
        </div>

    </div>
    
    <!--signup ui wizard-->
    <script src="{{ asset('js/signup.js') }}"></script>

    <!-- SMART ERROR ROUTER & TOAST ANIMATION -->
    @if ($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 1. Hanapin kung saan nagka-error para doon ibukas ang form
            let errorStep = 'step1';
            @if($errors->hasAny(['first_name', 'middle_name', 'last_name', 'suffix', 'sex', 'dob_month', 'dob_day', 'dob_year']))
                errorStep = 'step1';
            @elseif($errors->hasAny(['house_number', 'purok_street']))
                errorStep = 'step2';
            @elseif($errors->hasAny(['contact_number', 'email', 'password']))
                errorStep = 'step3';
            @elseif($errors->hasAny(['id_photo_path', 'selfie_photo_path', 'terms']))
                errorStep = 'step4';
            @endif

            // 2. Itago lahat ng steps at buksan lamang ang may error
            document.getElementById('step1').classList.add('hidden');
            document.getElementById('step2').classList.add('hidden');
            document.getElementById('step3').classList.add('hidden');
            document.getElementById('step4').classList.add('hidden');

            document.getElementById(errorStep).classList.remove('hidden');

            // 3. I-update ang Progress Bar UI
            let stepNum = parseInt(errorStep.replace('step', ''));
            updateProgressBar(stepNum);
            sessionStorage.setItem('bdls_active_step', errorStep);

            // 4. I-trigger ang Toast Animation
            const toast = document.getElementById('backend-toast');
            if (toast) {
                setTimeout(() => {
                    toast.classList.remove('-translate-y-20', 'opacity-0');
                    toast.classList.add('translate-y-0', 'opacity-100');
                }, 100);
                setTimeout(() => {
                    toast.classList.remove('translate-y-0', 'opacity-100');
                    toast.classList.add('-translate-y-20', 'opacity-0');
                }, 5000); // Mawawala matapos ang 5 segundo
            }
        });
    </script>
    @endif
</body>
</html>
