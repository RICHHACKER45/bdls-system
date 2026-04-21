<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Mag-Signup - Barangay Doña Lucia</title>
    @vite (['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col justify-center bg-slate-50 py-10 font-sans text-slate-900 antialiased">
    <!-- TOP NAVIGATION & FORM CONTROLS -->
    <div class="mx-auto w-full max-w-3xl px-4">
        <div class="mb-2 flex items-center justify-between">
            <!-- BUMALIK SA HOME BUTTON (Intentional Exit = Clear Memory) -->
            <a href="/" onclick="sessionStorage.clear()" class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-slate-500 transition-all duration-200 hover:text-red-600 focus:ring-4 focus:ring-slate-200 focus:outline-none active:scale-95 active:bg-slate-200">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Bumalik sa Home
            </a>

            <!-- RESET FORM BUTTON (Subtle, malayo sa "Next" buttons, may Confirmation) -->
            <button
                type="button"
                onclick="
                    if (confirm('Sigurado ka bang gusto mong burahin lahat ng tina-type mo at umpisahan muli?')) {
                        sessionStorage.clear();
                        location.reload();
                    }
                "
                class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-slate-400 transition-all duration-200 hover:text-slate-700 focus:outline-none active:scale-95"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                I-reset ang Form
            </button>
        </div>

        <div class="mb-8 text-center">
            <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Gumawa ng Account</h1>
            <p class="mt-2 text-slate-600">Kumpletuhin ang apat na hakbang upang magkaroon ng account.</p>
        </div>

        <!-- PROGRESS BAR UI -->
        <div class="relative rounded-2xl border border-slate-100 bg-white p-6 shadow-xl md:p-10">
            <div class="relative mb-10">
                <!-- LINES CONTAINER -->
                <div class="absolute top-5 left-[12.5%] z-0 h-1 w-[75%] bg-slate-200">
                    <div id="progress-line" class="absolute top-0 left-0 z-0 h-1 bg-red-600 transition-all duration-300 ease-in-out" style="width: 0%"></div>
                </div>

                <!-- Steps Container -->
                <div class="relative z-10 flex justify-between">
                    <!-- Step 1 -->
                    <div class="flex w-1/4 flex-col items-center">
                        <div class="indicator flex h-10 w-10 items-center justify-center rounded-full bg-red-600 font-bold text-white shadow-md transition-colors duration-300" id="ind-1">1</div>
                        <span class="mt-2 text-center text-xs font-semibold text-slate-500 md:text-sm">Personal</span>
                    </div>

                    <!-- Step 2 -->
                    <div class="flex w-1/4 flex-col items-center">
                        <div class="indicator flex h-10 w-10 items-center justify-center rounded-full bg-slate-200 font-bold text-slate-500 transition-colors duration-300" id="ind-2">2</div>
                        <span class="mt-2 text-center text-xs font-semibold text-slate-500 md:text-sm">Tirahan</span>
                    </div>

                    <!-- Step 3 -->
                    <div class="flex w-1/4 flex-col items-center">
                        <div class="indicator flex h-10 w-10 items-center justify-center rounded-full bg-slate-200 font-bold text-slate-500 transition-colors duration-300" id="ind-3">3</div>
                        <span class="mt-2 text-center text-xs font-semibold text-slate-500 md:text-sm">Account</span>
                    </div>

                    <!-- Step 4 -->
                    <div class="flex w-1/4 flex-col items-center">
                        <div class="indicator flex h-10 w-10 items-center justify-center rounded-full bg-slate-200 font-bold text-slate-500 transition-colors duration-300" id="ind-4">4</div>
                        <span class="mt-2 text-center text-xs font-semibold text-slate-500 md:text-sm">Verification</span>
                    </div>
                </div>
            </div>
            <!-- Registration Form -->
            <form action="{{ route('signup.post') }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf
                <!-- STEP 1: Personal na Impormasyon -->
                <div id="step1">
                    <h2 class="mb-6 text-2xl font-bold text-gray-800">Personal na Impormasyon</h2>

                    <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-4">
                        <!-- First Name -->
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">First Name <span class="text-red-500">*</span></label>
                            <input type="text" id="first_name" name="first_name" maxlength="255" required class="w-full rounded-lg border border-slate-300 px-4 py-3 transition-colors duration-200 outline-none focus:ring-2 focus:ring-slate-900" />
                            <p id="error-first_name" class="mt-1 hidden text-sm text-red-500">Ang field na ito ay kinakailangan.</p>
                        </div>

                        <!-- Middle Name -->
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Middle Name</label>
                            <input type="text" id="middle_name" name="middle_name" maxlength="255" class="w-full rounded-lg border border-slate-300 px-4 py-3 transition-colors duration-200 outline-none focus:ring-2 focus:ring-slate-900" />
                        </div>

                        <!-- Last Name -->
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Last Name <span class="text-red-500">*</span></label>
                            <input type="text" id="last_name" name="last_name" maxlength="255" required class="w-full rounded-lg border border-slate-300 px-4 py-3 transition-colors duration-200 outline-none focus:ring-2 focus:ring-slate-900" />
                            <p id="error-last_name" class="mt-1 hidden text-sm text-red-500">Ang field na ito ay kinakailangan.</p>
                        </div>

                        <!-- Suffix -->
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Suffix (Opsyonal)</label>
                            <select id="suffix" name="suffix" class="w-full cursor-pointer rounded-lg border border-slate-300 px-4 py-3 transition-colors duration-200 outline-none focus:ring-2 focus:ring-slate-900">
                                <option value="">Wala</option>
                                <option value="Jr.">Jr.</option>
                                <option value="Sr.">Sr.</option>
                                <option value="II">II</option>
                                <option value="III">III</option>
                                <option value="IV">IV</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Kasarian -->
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Kasarian <span class="text-red-500">*</span></label>
                            <select id="sex" name="sex" required class="w-full cursor-pointer rounded-lg border border-slate-300 px-4 py-3 transition-colors duration-200 outline-none focus:ring-2 focus:ring-slate-900">
                                <option value="">-- Pumili ng Kasarian --</option>
                                <option value="Male">Lalaki</option>
                                <option value="Female">Babae</option>
                            </select>
                            <p id="error-sex" class="mt-1 hidden text-sm text-red-500">Ang field na ito ay kinakailangan.</p>
                        </div>

                        <!-- Petsa ng Kapanganakan -->
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Petsa ng Kapanganakan <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-3 gap-2">
                                <div>
                                    <select id="dob_month" name="dob_month" required class="w-full rounded-lg border border-slate-300 px-3 py-3 transition-colors duration-200 outline-none focus:ring-2 focus:ring-slate-900">
                                        <option value="">Buwan</option>
                                        <option value="01">Enero</option>
                                        <option value="02">Pebrero</option>
                                        <option value="03">Marso</option>
                                        <option value="04">Abril</option>
                                        <option value="05">Mayo</option>
                                        <option value="06">Hunyo</option>
                                        <option value="07">Hulyo</option>
                                        <option value="08">Agosto</option>
                                        <option value="09">Setyembre</option>
                                        <option value="10">Oktubre</option>
                                        <option value="11">Nobyembre</option>
                                        <option value="12">Disyembre</option>
                                    </select>
                                    <p id="error-dob_month" class="mt-1 hidden text-xs text-red-500">Kailangan</p>
                                </div>
                                <div>
                                    <select id="dob_day" name="dob_day" required class="w-full rounded-lg border border-slate-300 px-3 py-3 transition-colors duration-200 outline-none focus:ring-2 focus:ring-slate-900">
                                        <option value="">Araw</option>
                                        @for ($i = 1; $i <= 31; $i++)
                                            <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                    <p id="error-dob_day" class="mt-1 hidden text-xs text-red-500">Kailangan</p>
                                </div>
                                <div>
                                    <select id="dob_year" name="dob_year" required class="w-full rounded-lg border border-slate-300 px-3 py-3 transition-colors duration-200 outline-none focus:ring-2 focus:ring-slate-900">
                                        <option value="">Taon</option>
                                        @for ($i = date('Y'); $i >= 1900; $i--)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                    <p id="error-dob_year" class="mt-1 hidden text-xs text-red-500">Kailangan</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex w-full justify-end">
                        <button type="button" onclick="validateAndGo('step1', 'step2')" class="w-full rounded-xl bg-slate-900 px-8 py-3 font-bold text-white transition-all duration-200 hover:bg-slate-800 active:scale-95 active:bg-slate-700 md:w-auto">Susunod na Hakbang</button>
                    </div>
                </div>

                <!-- STEP 2: TIRAHAN -->
                <div id="step2" class="hidden">
                    <h2 class="mb-6 text-2xl font-bold text-gray-800">Tirahan</h2>

                    <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Numero ng Bahay <span class="text-red-500">*</span></label>
                            <input type="text" id="house_number" name="house_number" maxlength="50" required class="w-full rounded-lg border border-slate-300 px-4 py-3 transition-colors duration-200 outline-none focus:ring-2 focus:ring-slate-900" />
                            <p id="error-house_number" class="mt-1 hidden text-sm text-red-500">Ang field na ito ay kinakailangan.</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Purok / Kalye <span class="text-red-500">*</span></label>
                            <input type="text" id="purok_street" name="purok_street" maxlength="255" required class="w-full rounded-lg border border-slate-300 px-4 py-3 transition-colors duration-200 outline-none focus:ring-2 focus:ring-slate-900" />
                            <p id="error-purok_street" class="mt-1 hidden text-sm text-red-500">Ang field na ito ay kinakailangan.</p>
                        </div>
                    </div>

                    <div class="mt-8 flex w-full flex-col-reverse items-stretch justify-between gap-4 md:flex-row">
                        <button type="button" onclick="goBack('step2', 'step1')" class="w-full rounded-xl border-2 border-slate-300 bg-transparent px-8 py-3 font-bold text-slate-700 transition-all duration-200 hover:bg-slate-100 focus:ring-4 focus:ring-slate-200 focus:outline-none active:scale-95 active:bg-slate-200 md:w-auto">Bumalik</button>
                        <button type="button" onclick="validateAndGo('step2', 'step3')" class="w-full rounded-xl bg-slate-900 px-8 py-3 font-bold text-white transition-all duration-200 hover:bg-slate-800 active:scale-95 active:bg-slate-700 md:w-auto">Susunod na Hakbang</button>
                    </div>
                </div>

                <!-- STEP 3: ACCOUNT DETAILS -->
                <div id="step3" class="hidden">
                    <h2 class="mb-6 text-2xl font-bold text-gray-800">Mga Detalye ng Account</h2>

                    <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Mobile Number <span class="text-red-500">*</span></label>
                            <input type="tel" id="contact_number" name="contact_number" inputmode="numeric" required maxlength="11" pattern="09{9}" placeholder="09XXXXXXXXX" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="w-full rounded-lg border border-slate-300 px-4 py-3 transition-colors duration-200 outline-none focus:ring-2 focus:ring-slate-900" />
                            <p id="error-contact_number" class="mt-1 hidden text-sm text-red-500">Ang field na ito ay kinakailangan.</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Email Address (Opsyonal)</label>
                            <input type="email" id="email" name="email" maxlength="255" class="w-full rounded-lg border border-slate-300 px-4 py-3 transition-colors duration-200 outline-none focus:ring-2 focus:ring-slate-900" />
                        </div>
                    </div>

                    <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Password <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="password" id="password" name="password" minlength="8" maxlength="64" required class="w-full rounded-lg border border-slate-300 px-4 py-3 pr-20 transition-colors duration-200 outline-none focus:ring-2 focus:ring-slate-900" />
                                <button type="button" onclick="togglePassword('password')" class="absolute top-1/2 right-2 -translate-y-1/2 rounded-md p-3 text-xs font-bold text-slate-400 transition-all duration-100 select-none hover:text-slate-900 focus:outline-none active:scale-95 active:bg-slate-200 active:text-slate-900">IPAKITA</button>
                            </div>
                            <p id="error-password" class="mt-1 hidden text-sm text-red-500">Ang field na ito ay kinakailangan.</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">I-kumpirma ang Password <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="password" id="password_confirmation" name="password_confirmation" minlength="8" maxlength="64" required class="w-full rounded-lg border border-slate-300 px-4 py-3 pr-20 transition-colors duration-200 outline-none focus:ring-2 focus:ring-slate-900" />
                                <button type="button" onclick="togglePassword('password_confirmation')" class="absolute top-1/2 right-2 -translate-y-1/2 rounded-md p-3 text-xs font-bold text-slate-400 transition-all duration-100 select-none hover:text-slate-900 focus:outline-none active:scale-95 active:bg-slate-200 active:text-slate-900">IPAKITA</button>
                            </div>
                            <p id="error-password_confirmation" class="mt-1 hidden text-sm text-red-500">Ang field na ito ay kinakailangan.</p>
                        </div>
                    </div>

                    <div class="mt-8 flex w-full flex-col-reverse items-stretch justify-between gap-4 md:flex-row">
                        <button type="button" onclick="goBack('step3', 'step2')" class="w-full rounded-xl border-2 border-slate-300 bg-transparent px-8 py-3 font-bold text-slate-700 transition-all duration-200 hover:bg-slate-100 focus:ring-4 focus:ring-slate-200 focus:outline-none active:scale-95 active:bg-slate-200 md:w-auto">Bumalik</button>
                        <button type="button" onclick="validateAndGo('step3', 'step4')" class="w-full rounded-xl bg-slate-900 px-8 py-3 font-bold text-white transition-all duration-200 hover:bg-slate-800 active:scale-95 active:bg-slate-700 md:w-auto">Susunod na Hakbang</button>
                    </div>
                </div>

                <!-- STEP 4: ACCOUNT VERIFICATION -->
                <div id="step4" class="hidden">
                    <h2 class="mb-2 text-2xl font-bold text-gray-800">Account Verification</h2>
                    <p class="mb-6 text-sm text-slate-500">Para sa seguridad ng iyong account, kailangan itong i-verify ng Barangay Administrator. Paki-upload ang mga sumusunod.</p>

                    <div class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Mag-upload ng Valid ID <span class="text-red-500">*</span></label>
                            <input type="file" id="id_photo_path" name="id_photo_path" accept="image/jpeg, image/png, image/jpg" required class="sr-only" onchange="previewImage(event, 'preview-id')" />
                            <button type="button" onclick="document.getElementById('id_photo_path').click()" class="mb-4 w-full rounded-md bg-slate-900 px-6 py-2 font-medium text-white transition-all duration-200 hover:bg-slate-800 active:scale-95 active:bg-slate-700">Pumili ng Larawan ng ID</button>
                            <div class="flex h-48 w-full items-center justify-center overflow-hidden rounded-lg border-2 border-dashed border-slate-300 bg-white">
                                <span id="placeholder-id" class="text-sm text-slate-400">Walang napiling larawan</span>
                                <img id="preview-id" src="" alt="ID Preview" class="hidden h-full w-full object-cover" />
                            </div>
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Mag-upload ng Selfie <span class="text-red-500">*</span></label>
                            <input type="file" id="selfie_photo_path" name="selfie_photo_path" accept="image/jpeg, image/png, image/jpg" required class="sr-only" onchange="previewImage(event, 'preview-selfie')" />
                            <button type="button" onclick="document.getElementById('selfie_photo_path').click()" class="mb-4 w-full rounded-md bg-slate-900 px-6 py-2 font-medium text-white transition-all duration-200 hover:bg-slate-800 active:scale-95 active:bg-slate-700">Pumili ng Larawan ng Selfie</button>
                            <div class="flex h-48 w-full items-center justify-center overflow-hidden rounded-lg border-2 border-dashed border-slate-300 bg-white">
                                <span id="placeholder-selfie" class="text-sm text-slate-400">Walang napiling larawan</span>
                                <img id="preview-selfie" src="" alt="Selfie Preview" class="hidden h-full w-full object-cover" />
                            </div>
                        </div>
                    </div>

                    <div class="mb-8 flex items-start gap-3 rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <input type="checkbox" id="terms" name="terms" required onchange="document.getElementById('submitBtn').disabled = !this.checked" class="mt-1 h-5 w-5 cursor-pointer rounded border-slate-300 text-slate-900 focus:ring-slate-900" />
                        <label for="terms" class="cursor-pointer text-sm text-slate-700">
                            Nabasa ko at sumasang-ayon ako sa
                            <button type="button" onclick="openLegalModal('privacyModal')" class="font-bold text-red-600 transition-all hover:underline focus:outline-none">Privacy Policy</button>
                            at
                            <button type="button" onclick="openLegalModal('termsModal')" class="font-bold text-red-600 transition-all hover:underline focus:outline-none">Terms & Conditions</button>
                            ng BDLS.
                        </label>
                    </div>

                    <div class="mt-8 flex w-full flex-col-reverse items-stretch justify-between gap-4 md:flex-row">
                        <button type="button" onclick="goBack('step4', 'step3')" class="w-full rounded-xl border-2 border-slate-300 bg-transparent px-8 py-3 font-bold text-slate-700 transition-all duration-200 hover:bg-slate-100 focus:ring-4 focus:ring-slate-200 focus:outline-none active:scale-95 active:bg-slate-200 md:w-auto">Bumalik</button>
                        <button type="submit" id="submitBtn" disabled class="w-full rounded-xl bg-slate-900 px-8 py-3 font-bold whitespace-nowrap text-white transition-all duration-200 hover:bg-slate-800 active:scale-95 active:bg-slate-700 disabled:cursor-not-allowed disabled:opacity-50 disabled:active:scale-100 md:w-auto">I-submit ang Pagpaparehistro</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- LEGAL MODALS -->
    <div id="privacyModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm transition-opacity">
        <div class="flex max-h-[80vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 p-6">
                <h2 class="text-xl font-bold text-slate-900">Patakaran sa Privacy ng BDLS</h2>
                <button type="button" onclick="closeLegalModal('privacyModal')" class="text-2xl font-bold text-slate-400 hover:text-slate-800">&times;</button>
            </div>
            <div class="space-y-4 overflow-y-auto p-6 text-sm text-slate-700">
                <p>Ang Barangay Doña Lucia ay nagpapahalaga sa iyong personal na impormasyon. Ang patakarang ito ay nagpapaliwanag kung paano namin ginagamit at pino-protektahan ang iyong data.</p>
                <h3 class="font-bold text-slate-900">1. Anong impormasyon ang kinokolekta namin?</h3>
                <p>Para makagawa ng account, kukunin namin ang iyong Pangalan, Address, Contact Number, Password, litrato ng iyong ID, at isang Selfie. Ang pagbibigay ng Email ay opsyonal. Kukunin din namin ang iyong Edad at Kasarian para matukoy ng barangay kung anong mga benepisyo o programa ang nararapat sa iyong grupo.</p>
                <h3 class="font-bold text-slate-900">2. Paano namin ito itatago at pino-protektahan?</h3>
                <p>Ang iyong Selfie ay gagamitin bilang iyong Profile Picture sa system. Ang litrato ng iyong ID ay itatago ng system upang hindi mo na kailangang mag-pasa ulit para sa mga susunod mong transaksyon. Para sa iyong kaligtasan, <strong class="text-slate-900">ang iyong data ay naka-encrypt at naka-imbak sa mga secure servers</strong> upang maiwasan ang pagnanakaw ng impormasyon.</p>
                <h3 class="font-bold text-slate-900">3. Kanino namin ito ibinabahagi?</h3>
                <p>HINDI namin ibebenta, ipagpapalit, o ibibigay ang iyong data sa mga taong walang awtorisasyon. Ipapasa lamang ang iyong Contact Number (at Email kung meron) sa aming awtomatikong Notification System para makapagpadala sa iyo ng updates tungkol sa iyong request. Ibabahagi lamang namin ang iyong impormasyon sa mga awtoridad kung may utos ng batas o may naganap na krimen.</p>
                <h3 class="font-bold text-slate-900">4. Ang Iyong Karapatan sa Data (Data Rights)</h3>
                <p>Maaari mong hilingin na i-update o i-delete ang iyong impormasyon sa system anumang oras sa pamamagitan ng pagpapadala ng mensahe o paglapit nang personal sa aming barangay admin.</p>
            </div>
            <div class="border-t border-slate-200 bg-slate-50 p-4 text-right">
                <button type="button" onclick="closeLegalModal('privacyModal')" class="rounded-lg bg-slate-900 px-6 py-2 text-sm font-bold text-white transition-all hover:bg-slate-800 active:scale-95">Naintindihan Ko</button>
            </div>
        </div>
    </div>

    <div id="termsModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm transition-opacity">
        <div class="flex max-h-[80vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 p-6">
                <h2 class="text-xl font-bold text-slate-900">Mga Tuntunin at Kundisyon</h2>
                <button type="button" onclick="closeLegalModal('termsModal')" class="text-2xl font-bold text-slate-400 hover:text-slate-800">&times;</button>
            </div>
            <div class="space-y-4 overflow-y-auto p-6 text-sm text-slate-700">
                <p>Sa paggawa ng account sa BDLS, sumasang-ayon ka sa mga sumusunod na patakaran ng aming barangay:</p>
                <h3 class="font-bold text-slate-900">1. Responsibilidad sa Tamang Impormasyon</h3>
                <p>Responsibilidad ng user na siguraduhing tama at totoo ang lahat ng impormasyong ibibigay sa system. Anumang maling impormasyon ay maaaring maging dahilan ng pagka-antala o pagka-reject ng iyong request.</p>
                <h3 class="font-bold text-slate-900">2. Seguridad ng Account</h3>
                <p>Huwag ibigay ang iyong password sa iba. Ikaw ang responsable sa pag-iingat ng iyong account. Anumang transaksyon o request na ginawa gamit ang iyong account ay ituturing na gawa mo.</p>
                <h3 class="font-bold text-slate-900">3. Bawal ang Spam at Panliligalig</h3>
                <p>Mahigpit na ipinagbabawal ang paggamit ng system para mang-harass, mang-troll, o mag-spam ng mga walang kwentang service requests na nakakaabala sa operasyon ng barangay hall.</p>
                <h3 class="font-bold text-slate-900">4. Bawal ang Paggamit ng Pagkakakilanlan ng Iba (Identity Theft)</h3>
                <p>Ang paggamit ng pekeng pangalan, o pag-upload ng ID at mukha ng ibang tao nang walang pahintulot ay isang krimen. Ito ay labag sa RA 10175 (Cybercrime Prevention Act of 2012). Ang sinumang mahuhuli ay ire-report sa mga awtoridad para sa legal na aksyon at agad na iba-ban ang account.</p>
                <h3 class="font-bold text-slate-900">5. Bawal ang Pangha-hack at Kriminalidad</h3>
                <p>Anumang pagsubok na nakawin ang data ng ibang residente o sirain ang system ay may katumbas na kasong kriminal at agarang pagka-ban.</p>
                <h3 class="font-bold text-slate-900">6. Patakaran sa Hindi Pagkuha ng Dokumento (No-Show Policy)</h3>
                <p>Kapag na-aprubahan at na-text ka na ang iyong dokumento ay "Ready for Release", mangyaring kunin ito agad. <br />
                • Kung hindi mo ito makuha sa loob ng <strong>isang linggo</strong>, papadalhan ka namin ng isa pang paalala.<br />
                • Kung lumipas ang <strong>dalawang linggo</strong> at hindi mo pa rin kinukuha, papatawan ang iyong account ng <strong>isang linggong penalty</strong> kung saan hindi ka muna makakapag-request ng bagong dokumento sa system. Gayunpaman, maaari mo pa ring kunin ang iyong nakabinbing dokumento nang personal sa barangay hall.</p>
            </div>
            <div class="border-t border-slate-200 bg-slate-50 p-4 text-right">
                <button type="button" onclick="closeLegalModal('termsModal')" class="rounded-lg bg-slate-900 px-6 py-2 text-sm font-bold text-white transition-all hover:bg-slate-800 active:scale-95">Naintindihan Ko</button>
            </div>
        </div>
    </div>

    <!-- GLOBAL LOADER -->
    <div id="global-loader" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm transition-opacity">
        <div class="animate-bounce-slight flex flex-col items-center gap-4 rounded-2xl bg-white p-6 shadow-2xl">
            <svg class="h-10 w-10 animate-spin text-slate-900" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-sm font-bold text-slate-800">Pinoproseso...</p>
        </div>
    </div>

    <!-- ERROR MODAL -->
    <div id="errorModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-slate-900/80 p-4 backdrop-blur-sm transition-opacity">
        <div class="flex w-full max-w-sm flex-col items-center overflow-hidden rounded-2xl border-t-4 border-red-500 bg-white p-8 text-center shadow-2xl">
            <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-red-100 text-red-600">
                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <h3 class="mb-2 text-xl font-bold text-slate-800">May Problema</h3>
            <p id="errorModalMessage" class="mb-6 text-sm text-slate-600"></p>
            <button type="button" onclick="closeErrorModal()" class="w-full rounded-lg bg-red-600 px-4 py-3 font-bold text-white transition-all hover:bg-red-700 active:scale-95">Sige, Susubukan Ulit</button>
        </div>
    </div>

    <!-- TOAST NOTIFICATION SYSTEM -->
    <div id="toast-container" class="pointer-events-none fixed top-6 left-1/2 z-[64] flex w-full max-w-md -translate-x-1/2 transform flex-col gap-3 px-4">
        @if ($errors->any())
            <div id="backend-toast" class="pointer-events-auto flex -translate-y-20 transform items-center gap-4 rounded-xl border-l-4 border-red-500 bg-slate-900 px-6 py-4 text-white opacity-0 shadow-2xl transition-all duration-500">
                <svg class="h-6 w-6 shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <div>
                    <p class="text-sm font-bold">Oops! May nakitang mali.</p>
                    <p class="text-sm font-medium">{{ $errors->first() }}</p>
                </div>
            </div>
        @endif

        <div id="frontend-toast" class="pointer-events-auto hidden -translate-y-20 transform items-center gap-4 rounded-xl border-l-4 border-red-500 bg-slate-900 px-6 py-4 text-white opacity-0 shadow-2xl transition-all duration-500">
            <svg class="h-6 w-6 shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <div>
                <p class="text-sm font-bold">Oops! May nakitang mali.</p>
                <p id="frontend-toast-message" class="text-sm font-medium"></p>
            </div>
        </div>
    </div>

    <!-- THE LARAVEL CONFIG INJECTOR & SCRIPTS -->
    <script>
        window.SIGNUP_CONFIG = {
            hasErrors: {{ $errors->any() ? 'true' : 'false' }},
            errorStep: 'step1',
        };
        @if ($errors->hasAny(['house_number', 'purok_street']))
        window.SIGNUP_CONFIG.errorStep = 'step2';
        @elseif ($errors->hasAny(['contact_number', 'email', 'password']))
        window.SIGNUP_CONFIG.errorStep = 'step3';
        @elseif ($errors->hasAny(['id_photo_path', 'selfie_photo_path', 'terms']))
        window.SIGNUP_CONFIG.errorStep = 'step4';
        @endif
    </script>
    <script src="{{ asset('js/signup.js') }}"></script>
</body>
</html>
