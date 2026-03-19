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
                <!-- STEP 1: Personal Information -->
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
                            onclick="
                                document.getElementById('step2').classList.add('hidden');
                                document.getElementById('step1').classList.remove('hidden');
                            "
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

                <!-- STEP 3: Account -->
                <div id="step-3" class="form-step hidden">
                    <h3 class="text-lg font-bold text-slate-800 mb-4">3. Account Login Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1"
                                >Contact Number <span class="text-red-500">*</span></label
                            >
                            <input
                                type="text"
                                required
                                class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-red-600 outline-none"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1"
                                >Email
                                <span class="text-slate-400 font-normal">(Optional)</span></label
                            >
                            <input
                                type="email"
                                class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-red-600 outline-none"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1"
                                >Password <span class="text-red-500">*</span></label
                            >
                            <input
                                type="password"
                                id="password"
                                required
                                class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-red-600 outline-none"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1"
                                >Confirm Password <span class="text-red-500">*</span></label
                            >
                            <input
                                type="password"
                                id="password_confirmation"
                                required
                                class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-red-600 outline-none"
                            />
                        </div>
                    </div>
                    <!-- Step 3 Buttons -->
                    <div class="mt-8 flex justify-between">
                        <button
                            type="button"
                            onclick="goToStep(2)"
                            class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-3 px-6 rounded-xl transition-colors"
                        >
                            &larr; Back
                        </button>
                        <button
                            type="button"
                            onclick="validateAndGo(3, 4)"
                            class="bg-slate-900 hover:bg-slate-800 text-white font-bold py-3 px-8 rounded-xl transition-colors"
                        >
                            Next Step &rarr;
                        </button>
                    </div>
                </div>

                <!-- STEP 4: Verification -->
                <div id="step-4" class="form-step hidden">
                    <h3 class="text-lg font-bold text-slate-800 mb-4">
                        4. Pagpapatunay (ID & Selfie)
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div
                            class="border-2 border-dashed border-slate-300 rounded-xl p-6 text-center bg-slate-50"
                        >
                            <label class="block text-sm font-bold text-slate-700 mb-2"
                                >Upload Valid ID <span class="text-red-500">*</span></label
                            >
                            <input
                                type="file"
                                accept="image/*"
                                class="w-full text-sm text-slate-500"
                            />
                        </div>
                        <div
                            class="border-2 border-dashed border-slate-300 rounded-xl p-6 text-center bg-slate-50"
                        >
                            <label class="block text-sm font-bold text-slate-700 mb-2"
                                >Upload Selfie kasama ang ID
                                <span class="text-red-500">*</span></label
                            >
                            <input
                                type="file"
                                accept="image/*"
                                class="w-full text-sm text-slate-500"
                            />
                        </div>
                    </div>

                    <!-- Poka-yoke Defensive Design mo kanina -->
                    <div
                        class="bg-slate-50 p-4 rounded-lg border border-slate-200 flex items-start gap-3 mb-8"
                    >
                        <input
                            type="checkbox"
                            id="privacy"
                            required
                            onchange="document.getElementById('submitBtn').disabled = !this.checked"
                            class="mt-1 w-5 h-5 text-red-600 border-slate-300 rounded cursor-pointer"
                        />
                        <label for="privacy" class="text-sm text-slate-700 cursor-pointer">
                            Sumasang-ayon ako sa Data Privacy Terms.
                        </label>
                    </div>

                    <!-- Step 4 Buttons -->
                    <div class="flex justify-between items-center mt-4">
                        <button
                            type="button"
                            onclick="goToStep(3)"
                            class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-3 px-6 rounded-xl transition-colors"
                        >
                            &larr; Back
                        </button>

                        <button
                            type="submit"
                            id="submitBtn"
                            disabled
                            class="bg-red-600 hover:bg-red-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white font-bold py-3 px-8 rounded-xl shadow-md transition-colors text-lg"
                        >
                            I-submit Account
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- JAVASCRIPT LOGIC PARA SA WIZARD UI -->
    <script>
        /// BAGONG FUNCTION PARA I-CHECK KUNG MAY LAMAN AT TAMA BAGO UMABANTE
        function validateAndGo(currentStepId, nextStepId) {
            console.log('1. BUTTON PININDOT! Galing sa:', currentStepId, 'Pupunta sa:', nextStepId);

            let currentStepElement = document.getElementById(currentStepId);
            if (!currentStepElement) {
                console.error('CRITICAL ERROR: Hindi mahanap ang ' + currentStepId);
                return;
            }

            let isValid = true;
            let inputs = currentStepElement.querySelectorAll('input[required], select[required]');

            console.log('2. BILANG NG REQUIRED FIELDS NA NAKITA:', inputs.length);

            inputs.forEach((input) => {
                let errorMessage = document.getElementById('error-' + input.id);

                if (input.value.trim() === '') {
                    console.log('3. MAY BLANGKO NA FIELD! ID nito ay:', input.id); // CCTV natin ito!
                    isValid = false;
                    input.classList.add('border-red-500', 'ring-1', 'ring-red-500');
                    if (errorMessage) errorMessage.classList.remove('hidden');
                } else {
                    input.classList.remove('border-red-500', 'ring-1', 'ring-red-500');
                    if (errorMessage) errorMessage.classList.add('hidden');
                }
            });

            console.log('4. PUMASA BA SA VALIDATION? (True/False):', isValid);

            if (isValid) {
                console.log('5. LILIPAT NA SA NEXT STEP!');
                currentStepElement.classList.add('hidden');

                let nextElement = document.getElementById(nextStepId);
                if (nextElement) {
                    nextElement.classList.remove('hidden');
                    console.log('6. SUCCESS NA NAKALIPAT!');
                } else {
                    console.error(
                        'CRITICAL ERROR: Hindi ko mahanap ang susunod na step: ' + nextStepId,
                    );
                }
            }
        }

        // 2. Event Listener para sa Real-Time UI (Ito yung nawawala sa iyo)
        document.addEventListener('DOMContentLoaded', function () {
            // UPDATE: Hinahanap na ngayon ang parehong input AT select
            let requiredInputs = document.querySelectorAll('input[required], select[required]');

            requiredInputs.forEach((input) => {
                // 'input' event ay gumagana kapag nag-type sa textbox O namili sa dropdown
                input.addEventListener('input', function () {
                    let errorMessage = document.getElementById('error-' + this.id);

                    // Tanggalin agad ang pulang kulay kapag may ginawa ang user
                    this.classList.remove('border-red-500', 'ring-1', 'ring-red-500');

                    if (errorMessage) {
                        errorMessage.classList.add('hidden');
                    }
                });
            });
        });

        function goToStep(stepNumber) {
            // 1. Itago lahat ng steps
            document.querySelectorAll('.form-step').forEach(function (step) {
                step.classList.add('hidden');
            });

            // 2. Ipakita yung target step
            document.getElementById('step-' + stepNumber).classList.remove('hidden');

            // 3. I-update ang kulay ng 1-2-3-4 indicators
            for (let i = 1; i <= 4; i++) {
                let indicator = document.getElementById('ind-' + i);
                if (i <= stepNumber) {
                    // Tapos na o kasalukuyang step -> Kulay Pula
                    indicator.classList.remove('bg-slate-200', 'text-slate-500');
                    indicator.classList.add('bg-red-600', 'text-white', 'shadow-md');
                } else {
                    // Hindi pa nararating -> Kulay Gray
                    indicator.classList.add('bg-slate-200', 'text-slate-500');
                    indicator.classList.remove('bg-red-600', 'text-white', 'shadow-md');
                }
            }

            // 4. I-animate ang Red Progress Line (BAGONG DAGDAG ITO)
            // Computation: (Step 1 = 0%), (Step 2 = 33.3%), (Step 3 = 66.6%), (Step 4 = 100%)
            let progressPercentage = ((stepNumber - 1) / 3) * 100;
            document.getElementById('progress-line').style.width = progressPercentage + '%';
        }
    </script>
</body>
</html>
