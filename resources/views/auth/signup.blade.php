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
    <!-- Back Button -->
    <div class="absolute top-4 left-4 md:top-8 md:left-8">
        <a
            href="/"
            class="flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-red-600 transition-colors"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Bumalik sa Home
        </a>
    </div>

    <div class="max-w-3xl mx-auto w-full px-4">
        <div class="text-center mb-8">
            <div
                class="w-16 h-16 bg-red-600 rounded-full mx-auto flex items-center justify-center shadow-lg mb-4"
            >
                <span class="text-white font-bold text-xl">BDLS</span>
            </div>
            <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Gumawa ng Account</h1>
            <p class="text-slate-600 mt-2">Kumpletuhin ang 4 steps upang magkaroon ng account.</p>
        </div>

        <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-6 md:p-10 relative">
            <!-- PROGRESS BAR UI (FIXED ALIGNMENT) -->
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

            <form action="#" method="POST" enctype="multipart/form-data">
                @csrf
                <!-- ========================================================-->
                <!-- STEP 1: Personal Na Impormasyon (Personal Information) -->
                <!-- ========================================================-->
                <div id="step1">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Personal Information</h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <!-- First Name (Required) -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1"
                                >First Name <span class="text-red-500">*</span></label
                            >
                            <input
                                type="text"
                                id="first_name"
                                name="first_name"
                                required
                                class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-slate-900 outline-none transition-colors duration-200"
                            />
                            <p id="error-first_name" class="hidden text-red-500 text-sm mt-1">This field is required.</p>
                        </div>

                        <!-- Middle Name (Nullable, No Error Tag) -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1"
                                >Middle Name</label
                            >
                            <input
                                type="text"
                                id="middle_name"
                                name="middle_name"
                                class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-slate-900 outline-none transition-colors duration-200"
                            />
                        </div>

                        <!-- Last Name (Required) -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1"
                                >Last Name <span class="text-red-500">*</span></label
                            >
                            <input
                                type="text"
                                id="last_name"
                                name="last_name"
                                required
                                class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-slate-900 outline-none transition-colors duration-200"
                            />
                            <p id="error-last_name" class="hidden text-red-500 text-sm mt-1">This field is required.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <!-- Suffix (Nullable, No Error Tag) -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1"
                                >Suffix (e.g., Jr., Sr.)</label
                            >
                            <input
                                type="text"
                                id="suffix"
                                name="suffix"
                                class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-slate-900 outline-none transition-colors duration-200"
                            />
                        </div>

                        <!-- Date of Birth (Custom Dropdowns) -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-1"
                                >Date of Birth <span class="text-red-500">*</span></label
                            >
                            <div class="grid grid-cols-3 gap-2">
                                <!-- Month -->
                                <div>
                                    <select
                                        id="dob_month"
                                        name="dob_month"
                                        required
                                        class="w-full px-3 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-slate-900 outline-none transition-colors duration-200"
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
                                    <p id="error-dob_month" class="hidden text-red-500 text-xs mt-1">Required</p>
                                </div>

                                <!-- Day (Gamit ang Laravel Blade For Loop) -->
                                <div>
                                    <select
                                        id="dob_day"
                                        name="dob_day"
                                        required
                                        class="w-full px-3 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-slate-900 outline-none transition-colors duration-200"
                                    >
                                        <option value="">Day</option>
                                        @for ($i = 1; $i <= 31; $i++)
                                            <!-- Ang str_pad ay naglalagay ng '0' sa unahan ng 1-9 -->
                                            <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">
                                                {{ $i }}
                                            </option>
                                        @endfor
                                    </select>
                                    <p id="error-dob_day" class="hidden text-red-500 text-xs mt-1">Required</p>
                                </div>

                                <!-- Year (Gamit ang Laravel Blade For Loop) -->
                                <div>
                                    <select
                                        id="dob_year"
                                        name="dob_year"
                                        required
                                        class="w-full px-3 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-slate-900 outline-none transition-colors duration-200"
                                    >
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
                    <!-- Flex justify-end ang magtutulak sa button papunta sa kanan -->
                    <div class="flex justify-end mt-8">
                        <button
                            type="button"
                            onclick="validateAndGo('step1', 'step2')"
                            class="bg-slate-900 hover:bg-slate-800 active:bg-slate-700 active:scale-95 text-white font-bold py-3 px-8 rounded-xl transition-all duration-200"
                        >
                            Next Step
                        </button>
                    </div>
                </div>
                <!-- ITO YUNG IDINAGDAG MO NA CLOSING NG STEP 1 -->

                <!-- ========================================== -->
                <!-- STEP 2: TIRAHAN (Address)                  -->
                <!-- ========================================== -->
                <div id="step2" class="hidden">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Tirahan (Address)</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <!-- House Number (Required) -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1"
                                >House / Block / Lot No. <span class="text-red-500">*</span></label
                            >
                            <input
                                type="text"
                                id="house_number"
                                name="house_number"
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
                                required
                                class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-slate-900 outline-none transition-colors duration-200"
                            />
                            <p id="error-purok_street" class="hidden text-red-500 text-sm mt-1">This field is required.</p>
                        </div>
                    </div>

                    <!-- BUTTON CONTAINER (Back at Next) -->
                    <div class="w-full flex justify-between mt-8">
                        <!-- BACK BUTTON -->
                        <button
                            type="button"
                            onclick="goBack('step2', 'step1')"
                            class="bg-slate-500 hover:bg-slate-600 active:bg-slate-700 active:scale-95 text-white font-bold py-3 px-8 rounded-xl transition-all duration-200"
                        >
                            Back
                        </button>

                        <!-- NEXT BUTTON -->
                        <button
                            type="button"
                            onclick="validateAndGo('step2', 'step3')"
                            class="bg-slate-900 hover:bg-slate-800 active:bg-slate-700 active:scale-95 text-white font-bold py-3 px-8 rounded-xl transition-all duration-200"
                        >
                            Next Step
                        </button>
                    </div>
                </div>

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

                    <!-- BUTTON CONTAINER (Back at Next) -->
                    <div class="w-full flex justify-between mt-8">
                        <button
                            type="button"
                            onclick="goBack('step3', 'step2')"
                            class="bg-slate-500 hover:bg-slate-600 active:bg-slate-700 active:scale-95 text-white font-bold py-3 px-8 rounded-xl transition-all duration-200"
                        >
                            Back
                        </button>
                        <button
                            type="button"
                            onclick="validateAndGo('step3', 'step4')"
                            class="bg-slate-900 hover:bg-slate-800 active:bg-slate-700 active:scale-95 text-white font-bold py-3 px-8 rounded-xl transition-all duration-200"
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

                    <!-- Poka-yoke Defensive Design: Data Privacy Consent -->
                    <div
                        class="bg-slate-50 p-4 rounded-lg border border-slate-200 flex items-start gap-3 mb-8"
                    >
                        <input
                            type="checkbox"
                            id="privacy"
                            name="privacy"
                            required
                            onchange="document.getElementById('submitBtn').disabled = !this.checked"
                            class="mt-1 w-5 h-5 text-slate-900 border-slate-300 rounded cursor-pointer focus:ring-slate-900"
                        />
                        <label for="privacy" class="text-sm text-slate-700 cursor-pointer">
                            Sumasang-ayon ako na ang aking mga personal na impormasyon, kalakip ang
                            aking ID at selfie, ay kokolektahin at ipoproseso lamang para sa account
                            verification at serbisyo ng system, alinsunod sa
                            <button
                                type="button"
                                onclick="openPrivacyModal()"
                                class="text-red-600 font-semibold hover:underline focus:outline-none focus:ring-2 focus:ring-red-400 rounded"
                            >
                                Data Privacy Terms
                            </button>
                            ng Barangay Doña Lucia.
                        </label>
                    </div>

                    <!-- BUTTON CONTAINER (Back at Submit) -->
                    <div class="w-full flex justify-between mt-8">
                        <!-- BACK BUTTON -->
                        <button
                            type="button"
                            onclick="goBack('step4', 'step3')"
                            class="bg-slate-500 hover:bg-slate-600 active:bg-slate-700 active:scale-95 text-white font-bold py-3 px-8 rounded-xl transition-all duration-200"
                        >
                            Back
                        </button>

                        <!-- SUBMIT BUTTON (Naka-disable sa simula, may id="submitBtn") -->
                        <button
                            type="submit"
                            id="submitBtn"
                            disabled
                            class="bg-slate-900 hover:bg-slate-800 disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100 active:bg-slate-700 active:scale-95 text-white font-bold py-3 px-8 rounded-xl transition-all duration-200"
                        >
                            Submit Registration
                        </button>
                    </div>
                </div>
                <!-- CLOSING NG STEP 4 -->
            </form>
        </div>
    </div>

    <!-- JAVASCRIPT LOGIC PARA SA WIZARD UI -->
    <script>
        // 1. Custom function para sa live image preview
        function previewImage(event, previewId) {
            let input = event.target;
            let previewImg = document.getElementById(previewId);
            let placeholder = document.getElementById(previewId.replace('preview', 'placeholder'));

            // Kunin ang unang file gamit ang .item(0)
            let file = input.files.item(0);

            // DEFENSIVE DESIGN (Galing sa Stack Overflow):
            // Kung nag-cancel ang user at walang file (undefined/null), tapusin agad ang function
            if (!file) {
                console.log('Nag-cancel ang user, walang file na napili.');
                return;
            }

            console.log('File na nakuha:', file.name);

            let reader = new FileReader();

            reader.onload = function (e) {
                previewImg.src = e.target.result;
                previewImg.classList.remove('hidden');
                placeholder.classList.add('hidden');
            };

            // IPASA ANG FILE BLOB NANG LIGTAS
            reader.readAsDataURL(file);
        }

        // 2. Tagapag-alaga ng Progress Bar at Indicators
        function updateProgressBar(stepNumber) {
            for (let i = 1; i <= 4; i++) {
                let indicator = document.getElementById('ind-' + i);
                if (indicator) {
                    if (i <= stepNumber) {
                        indicator.classList.remove('bg-slate-200', 'text-slate-500');
                        indicator.classList.add('bg-red-600', 'text-white', 'shadow-md');
                    } else {
                        indicator.classList.add('bg-slate-200', 'text-slate-500');
                        indicator.classList.remove('bg-red-600', 'text-white', 'shadow-md');
                    }
                }
            }
            let progressLine = document.getElementById('progress-line');
            if (progressLine) {
                let progressPercentage = ((stepNumber - 1) / 3) * 100;
                progressLine.style.width = progressPercentage + '%';
            }
        }

        // 3. Ang "Next" Button Function
        function validateAndGo(currentStepId, nextStepId) {
            let currentStepElement = document.getElementById(currentStepId);
            if (!currentStepElement) return;

            let isValid = true;
            let inputs = currentStepElement.querySelectorAll('input[required], select[required]');

            inputs.forEach((input) => {
                let errorMessage = document.getElementById('error-' + input.id);
                let hasError = false;

                // 1. Check kung blangko
                if (input.value.trim() === '') {
                    hasError = true;
                    if (errorMessage) errorMessage.textContent = 'This field is required.';
                }
                // 2. SPECIAL RULE: Kung password field at less than 8 chars
                else if (
                    (input.id === 'password' || input.id === 'password_confirmation') &&
                    input.value.length < 8
                ) {
                    hasError = true;
                    if (errorMessage)
                        errorMessage.textContent = 'Password must be at least 8 characters.';
                }
                // 3. SPECIAL RULE: Kung confirm password at hindi match sa password
                else if (
                    input.id === 'password_confirmation' &&
                    input.value !== document.getElementById('password').value
                ) {
                    hasError = true;
                    if (errorMessage) errorMessage.textContent = 'Passwords do not match.';
                }

                // Ipakita o itago ang error base sa mga rules sa itaas
                if (hasError) {
                    isValid = false;
                    input.classList.add('border-red-500', 'ring-1', 'ring-red-500');
                    if (errorMessage) errorMessage.classList.remove('hidden');
                } else {
                    input.classList.remove('border-red-500', 'ring-1', 'ring-red-500');
                    if (errorMessage) errorMessage.classList.add('hidden');
                }
            });

            if (isValid) {
                currentStepElement.classList.add('hidden');
                let nextElement = document.getElementById(nextStepId);
                if (nextElement) {
                    nextElement.classList.remove('hidden');
                    let stepNum = parseInt(nextStepId.replace('step', ''));
                    updateProgressBar(stepNum);
                }
            }
        }

        // 4. Ang "Back" Button Function
        function goBack(currentStepId, prevStepId) {
            document.getElementById(currentStepId).classList.add('hidden');
            document.getElementById(prevStepId).classList.remove('hidden');
            let stepNum = parseInt(prevStepId.replace('step', ''));
            updateProgressBar(stepNum);
        }

        // 5. Real-Time Error Removal
        document.addEventListener('DOMContentLoaded', function () {
            let requiredInputs = document.querySelectorAll('input[required], select[required]');
            requiredInputs.forEach((input) => {
                input.addEventListener('input', function () {
                    let errorMessage = document.getElementById('error-' + this.id);
                    this.classList.remove('border-red-500', 'ring-1', 'ring-red-500');
                    if (errorMessage) errorMessage.classList.add('hidden');
                });
            });
        });

        // 6. Function para sa Show/Hide Password
        function togglePassword(inputId) {
            let input = document.getElementById(inputId);
            let button = input.nextElementSibling; // Kukunin yung mismong SHOW button

            if (input.type === 'password') {
                input.type = 'text';
                button.innerText = 'HIDE';
            } else {
                input.type = 'password';
                button.innerText = 'SHOW';
            }
        }

        // 7. Modal Functions
        function openPrivacyModal() {
            document.getElementById('privacyModal').classList.remove('hidden');
        }

        function closePrivacyModal() {
            document.getElementById('privacyModal').classList.add('hidden');
        }
    </script>

    <!-- DATA PRIVACY MODAL -->
    <div
        id="privacyModal"
        class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
    >
        <!-- Modal Card -->
        <div
            class="bg-white w-full max-w-lg rounded-2xl shadow-xl overflow-hidden flex flex-col max-h-[80vh]"
        >
            <!-- Header -->
            <div
                class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-slate-50"
            >
                <h3 class="text-lg font-bold text-slate-800">Data Privacy Terms</h3>
                <button
                    type="button"
                    onclick="closePrivacyModal()"
                    class="text-slate-400 hover:text-slate-700 focus:outline-none text-2xl font-bold"
                >
                    &times;
                </button>
            </div>

            <!-- Body (Scrollable kung mahaba) -->
            <div class="px-6 py-4 overflow-y-auto text-sm text-slate-600 space-y-4">
                <p>Alinsunod sa <strong>Data Privacy Act of 2012</strong>, ang Barangay Doña Lucia ay nangangakong poprotektahan ang iyong personal na impormasyon.</p>
                <p><strong>1. Pangongolekta ng Data:</strong> Kinokolekta namin ang iyong pangalan, contact number, valid ID, at selfie upang ma-verify ang iyong pagkakakilanlan.</p>
                <p><strong>2. Paggamit ng Data:</strong> Gagamitin lamang ang iyong impormasyon para sa pagproseso ng iyong mga service requests at pagpapadala ng SMS/Email notifications.</p>
                <p><strong>3. Pag-iingat:</strong> Ang iyong mga dokumento ay ligtas na iimbakin sa aming system at tanging mga awtorisadong barangay admin lamang ang makakakita nito.</p>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end">
                <button
                    type="button"
                    onclick="closePrivacyModal()"
                    class="bg-slate-900 hover:bg-slate-800 text-white font-bold py-2 px-6 rounded-lg transition-all active:scale-95"
                >
                    I Understand
                </button>
            </div>
        </div>
    </div>
</body>
</html>
