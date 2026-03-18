<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mag-Signup - Barangay Doña Lucia</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 font-sans text-slate-900 antialiased min-h-screen flex flex-col justify-center py-10">

    <!-- Back Button -->
    <div class="absolute top-4 left-4 md:top-8 md:left-8">
        <a href="/" class="flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-red-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Bumalik sa Home
        </a>
    </div>

    <div class="max-w-3xl mx-auto w-full px-4">
        
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-red-600 rounded-full mx-auto flex items-center justify-center shadow-lg mb-4">
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
                    <div id="progress-line" class="absolute left-0 top-0 h-1 bg-red-600 z-0 transition-all duration-300 ease-in-out" style="width: 0%;"></div>
                </div>
                
                <!-- Steps Container -->
                <div class="flex justify-between relative z-10">
                    
                    <!-- Step 1 -->
                    <div class="flex flex-col items-center w-1/4">
                        <div class="w-10 h-10 rounded-full bg-red-600 text-white flex items-center justify-center font-bold shadow-md transition-colors duration-300 indicator" id="ind-1">1</div>
                        <span class="mt-2 text-xs md:text-sm font-semibold text-slate-500 text-center">Personal</span>
                    </div>

                    <!-- Step 2 -->
                    <div class="flex flex-col items-center w-1/4">
                        <div class="w-10 h-10 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center font-bold transition-colors duration-300 indicator" id="ind-2">2</div>
                        <span class="mt-2 text-xs md:text-sm font-semibold text-slate-500 text-center">Tirahan</span>
                    </div>

                    <!-- Step 3 -->
                    <div class="flex flex-col items-center w-1/4">
                        <div class="w-10 h-10 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center font-bold transition-colors duration-300 indicator" id="ind-3">3</div>
                        <span class="mt-2 text-xs md:text-sm font-semibold text-slate-500 text-center">Account</span>
                    </div>

                    <!-- Step 4 -->
                    <div class="flex flex-col items-center w-1/4">
                        <div class="w-10 h-10 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center font-bold transition-colors duration-300 indicator" id="ind-4">4</div>
                        <span class="mt-2 text-xs md:text-sm font-semibold text-slate-500 text-center">Verification</span>
                    </div>

                </div>
            </div>

            <form action="#" method="POST" enctype="multipart/form-data">

                <!-- STEP 1: Personal Info -->
                <div id="step-1" class="form-step">
                    <h3 class="text-lg font-bold text-slate-800 mb-4">1. Personal na Impormasyon</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">First Name <span class="text-red-500">*</span></label>
                            <input type="text" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-red-600 outline-none"> 
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Middle Name</label>
                            <input type="text" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-red-600 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Last Name <span class="text-red-500">*</span></label>
                            <input type="text" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-red-600 outline-none">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Suffix</label>
                                <input type="text" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-red-600 outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Birthdate <span class="text-red-500">*</span></label>
                                <input type="date" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-red-600 outline-none">
                            </div>
                        </div>
                    </div>
                    <!-- Step 1 Button -->
                    <div class="mt-8 flex justify-end">
                        <button type="button" onclick="validateAndGo(1, 2)" class="bg-slate-900 hover:bg-slate-800 text-white font-bold py-3 px-8 rounded-xl transition-colors">Next Step &rarr;</button>
                    </div>
                </div>

                <!-- STEP 2: Tirahan (Naka-hide by default) -->
                <div id="step-2" class="form-step hidden">
                    <h3 class="text-lg font-bold text-slate-800 mb-4">2. Tirahan sa Doña Lucia</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">House / Unit Number <span class="text-red-500">*</span></label>
                            <input type="text" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-red-600 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Purok / Street <span class="text-red-500">*</span></label>
                            <input type="text" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-red-600 outline-none">
                        </div>
                    </div>
                    <!-- Step 2 Buttons -->
                    <div class="mt-8 flex justify-between">
                        <button type="button" onclick="goToStep(1)" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-3 px-6 rounded-xl transition-colors">&larr; Back</button>
                        <button type="button" onclick="validateAndGo(2,3)" class="bg-slate-900 hover:bg-slate-800 text-white font-bold py-3 px-8 rounded-xl transition-colors">Next Step &rarr;</button>
                    </div>
                </div>

                <!-- STEP 3: Account -->
                <div id="step-3" class="form-step hidden">
                    <h3 class="text-lg font-bold text-slate-800 mb-4">3. Account Login Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Contact Number <span class="text-red-500">*</span></label>
                            <input type="text" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-red-600 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Email <span class="text-slate-400 font-normal">(Optional)</span></label>
                            <input type="email" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-red-600 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Password <span class="text-red-500">*</span></label>
                            <input type="password" id="password" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-red-600 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Confirm Password <span class="text-red-500">*</span></label>
                            <input type="password" id="password_confirmation" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-red-600 outline-none">
                        </div>
                    </div>
                    <!-- Step 3 Buttons -->
                    <div class="mt-8 flex justify-between">
                        <button type="button" onclick="goToStep(2)" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-3 px-6 rounded-xl transition-colors">&larr; Back</button>
                        <button type="button" onclick="validateAndGo(3,4)" class="bg-slate-900 hover:bg-slate-800 text-white font-bold py-3 px-8 rounded-xl transition-colors">Next Step &rarr;</button>
                    </div>
                </div>

                <!-- STEP 4: Verification -->
                <div id="step-4" class="form-step hidden">
                    <h3 class="text-lg font-bold text-slate-800 mb-4">4. Pagpapatunay (ID & Selfie)</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="border-2 border-dashed border-slate-300 rounded-xl p-6 text-center bg-slate-50">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Upload Valid ID <span class="text-red-500">*</span></label>
                            <input type="file" accept="image/*" class="w-full text-sm text-slate-500">
                        </div>
                        <div class="border-2 border-dashed border-slate-300 rounded-xl p-6 text-center bg-slate-50">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Upload Selfie kasama ang ID <span class="text-red-500">*</span></label>
                            <input type="file" accept="image/*" class="w-full text-sm text-slate-500">
                        </div>
                    </div>

                    <!-- Poka-yoke Defensive Design mo kanina -->
                    <div class="bg-slate-50 p-4 rounded-lg border border-slate-200 flex items-start gap-3 mb-8">
                        <input type="checkbox" id="privacy" required onchange="document.getElementById('submitBtn').disabled = !this.checked" class="mt-1 w-5 h-5 text-red-600 border-slate-300 rounded cursor-pointer">
                        <label for="privacy" class="text-sm text-slate-700 cursor-pointer">
                            Sumasang-ayon ako sa Data Privacy Terms.
                        </label>
                    </div>

                    <!-- Step 4 Buttons -->
                    <div class="flex justify-between items-center mt-4">
                        <button type="button" onclick="goToStep(3)" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-3 px-6 rounded-xl transition-colors">&larr; Back</button>
                        
                        <button type="submit" id="submitBtn" disabled class="bg-red-600 hover:bg-red-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white font-bold py-3 px-8 rounded-xl shadow-md transition-colors text-lg">
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
        function validateAndGo(currentStep, nextStep) {
            let stepDiv = document.getElementById('step-' + currentStep);
            let inputs = stepDiv.querySelectorAll('input[required]');
            let isValid = true;

            // 1. I-check kung may blangkong required fields
            for (let i = 0; i < inputs.length; i++) {
                if (!inputs[i].checkValidity()) {
                    inputs[i].reportValidity(); // Magpapalabas ng error tooltip
                    isValid = false;
                    break; // Para huminto sa unang error
                }
            }

            // 2. BAGONG PASSWORD CHECK PARA SA STEP 3 (Tatakbo lang kung walang blangko)
            if (currentStep === 3 && isValid) {
                let pass = document.getElementById('password');
                let confirmPass = document.getElementById('password_confirmation');

                if (pass.value !== confirmPass.value) {
                    // Magpapalabas ng custom error tooltip kung hindi pareho
                    confirmPass.setCustomValidity("Hindi magkapareho ang iyong password!");
                    confirmPass.reportValidity();
                    isValid = false;
                } else {
                    // Tatanggalin ang error kung pareho na
                    confirmPass.setCustomValidity(""); 
                }
            }

            // 3. Kung pumasa sa lahat ng checking, tsaka lang tatawagin ang lumang goToStep
            if (isValid) {
                goToStep(nextStep);
            }
        }
        
        function goToStep(stepNumber) {
            // 1. Itago lahat ng steps
            document.querySelectorAll('.form-step').forEach(function(step) {
                step.classList.add('hidden');
            });
            
            // 2. Ipakita yung target step
            document.getElementById('step-' + stepNumber).classList.remove('hidden');

            // 3. I-update ang kulay ng 1-2-3-4 indicators
            for(let i = 1; i <= 4; i++) {
                let indicator = document.getElementById('ind-' + i);
                if(i <= stepNumber) {
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