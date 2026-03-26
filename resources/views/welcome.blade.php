<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Welcome - Barangay Doña Lucia</title>
    @vite (['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 font-sans text-slate-900 antialiased">
    <!-- Header / Navbar -->
    <nav
        class="bg-white shadow-sm border-b border-slate-100 py-4 px-6 md:px-12 flex justify-between items-center sticky top-0 z-50"
    >
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 bg-red-600 rounded-full flex items-center justify-center shadow text-white font-bold text-xs"
            >
                BDLS
            </div>
            <span class="font-bold text-lg md:text-xl tracking-tight text-slate-800 hidden sm:block"
                >Barangay Doña Lucia Services</span
            >
        </div>
        <div class="flex items-center gap-4">
            <!-- Secondary Action (Sinking Box Effect) -->
            <a
                href="/login"
                class="text-slate-700 font-bold px-5 py-2 rounded-lg transition-all duration-200 hover:bg-slate-200 hover:shadow-inner active:scale-95 active:bg-slate-300 active:shadow-inner"
            >
                Login
            </a>
            <!-- Primary Action (Solid Red Press Effect) -->
            <a
                href="/signup"
                class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-5 rounded-lg transition-all shadow-sm active:scale-95 active:bg-red-800 active:shadow-inner"
            >
                Mag-Signup
            </a>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="relative bg-slate-800 text-white pt-24 pb-32 px-6 md:px-12 overflow-hidden">
        <div class="relative z-10 max-w-4xl">
            <h1 class="text-4xl md:text-6xl font-extrabold mb-4 tracking-tight">
                Welcome to Barangay Doña Lucia Services
            </h1>
            <p class="text-lg md:text-xl text-slate-300 max-w-2xl">Ang iyong mabilis at direktang koneksyon para sa mga government services, dokumento, at impormasyon ng barangay.</p>
        </div>
    </header>

    <!-- Main Content (Overlapping Section) -->
    <main class="max-w-7xl mx-auto px-6 md:px-12 -mt-16 relative z-20 pb-24">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Left Side: Government Services Cards -->
            <div class="flex-1 mt-16 lg:mt-24">
                <h2 class="text-2xl font-bold mb-6 text-slate-800">Government Services</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Card 1: Online Services (Clickable na) -->
                    <a
                        href="/signup"
                        class="block bg-white p-6 rounded-xl shadow-sm border border-slate-200 hover:shadow-md hover:border-red-300 active:scale-95 active:bg-slate-50 transition-all duration-200"
                    >
                        <div
                            class="w-12 h-12 bg-red-100 text-red-600 rounded-lg flex items-center justify-center mb-4"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800 mb-2">Online Services</h3>
                        <p class="text-slate-600 text-sm">Mag-request ng Barangay Clearance, Indigency, at iba pang dokumento online.</p>
                    </a>

                    <!-- Card 2: Queue Tracking (Clickable na) -->
                    <a
                        href="/login"
                        class="block bg-white p-6 rounded-xl shadow-sm border border-slate-200 hover:shadow-md hover:border-slate-400 active:scale-95 active:bg-slate-50 transition-all duration-200"
                    >
                        <div
                            class="w-12 h-12 bg-slate-100 text-slate-600 rounded-lg flex items-center justify-center mb-4"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800 mb-2">Queue & SMS Tracking</h3>
                        <p class="text-slate-600 text-sm">Alamin ang status ng iyong papel in real-time gamit ang SMS technology.</p>
                    </a>
                </div>
            </div>

            <!-- Right Side: The Overlapping Announcement Card -->
            <div class="w-full lg:w-1/3 lg:mt-0">
                <div
                    class="bg-white rounded-2xl shadow-xl border border-slate-100 p-8 relative overflow-hidden"
                >
                    <!-- Red Accent line sa taas ng card -->
                    <div class="absolute top-0 left-0 w-full h-1 bg-red-600"></div>
                    <h3 class="text-xl font-bold text-slate-900 mb-4 leading-snug">
                        ANNOUNCEMENT: Launch of the Official BDLS System
                    </h3>
                    <p class="text-slate-600 text-sm leading-relaxed mb-6">Ikinagagalak naming i-anunsyo ang opisyal na pagbubukas ng website ng Barangay Doña Lucia. Layunin nitong pabilisin ang inyong pag-request ng dokumento gamit ang aming bagong SMS Queuing System.</p>
                </div>
            </div>
        </div>
    </main>
    <!-- CLEANUP SCRIPT: Burahin ang mga "Sticky Form" drafts mula sa Signup kapag bumalik sa Home -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            sessionStorage.clear();
        });
    </script>
</body>
</html>
