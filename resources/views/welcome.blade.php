<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Maligayang Pagdating - Barangay Doña Lucia</title>
    @vite (['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 font-sans text-slate-900 antialiased">
    <!-- THE FIX 1: Kinumpleto ang 'z-50' para hindi lumubog ang Login at Signup -->
    <nav class="fixed top-0 left-0 z-50 flex w-full items-center justify-between bg-gradient-to-b from-slate-900/90 to-transparent px-6 py-5 md:px-12">
        <div class="flex items-center gap-3">
            <span class="text-xl font-black tracking-widest text-white drop-shadow-md">BDLS</span>
        </div>
        <div class="flex items-center gap-4">
            <a href="/login" class="rounded-xl px-6 py-2.5 text-sm font-bold tracking-widest text-white uppercase transition-all duration-200 hover:bg-white/20 active:scale-95"> Mag-login </a>
            <a href="/signup" class="rounded-xl bg-red-600 px-6 py-2.5 text-sm font-black tracking-widest text-white uppercase shadow-lg transition-all hover:bg-red-700 hover:shadow-red-600/30 active:scale-95"> Mag-Signup </a>
        </div>
    </nav>

    <!-- THE DYNAMIC CAROUSEL HERO SECTION -->
    <header class="relative flex min-h-[90vh] items-center justify-center overflow-hidden">
        <!-- THE FIX 2: Ang 7 Carousel Images (Pansinin ang opacity-100 sa una lang) -->
        <div id="hero-carousel" class="absolute inset-0 z-0 h-full w-full bg-slate-900">
            <img src="{{ asset('images/carousel-1.jpg') }}" class="carousel-slide absolute inset-0 h-full w-full object-cover opacity-100 transition-opacity duration-1000 ease-in-out" alt="Slide 1" />
            <img src="{{ asset('images/carousel-2.jpg') }}" class="carousel-slide absolute inset-0 h-full w-full object-cover opacity-0 transition-opacity duration-1000 ease-in-out" alt="Slide 2" />
            <img src="{{ asset('images/carousel-3.jpg') }}" class="carousel-slide absolute inset-0 h-full w-full object-cover opacity-0 transition-opacity duration-1000 ease-in-out" alt="Slide 3" />
            <img src="{{ asset('images/carousel-4.jpg') }}" class="carousel-slide absolute inset-0 h-full w-full object-cover opacity-0 transition-opacity duration-1000 ease-in-out" alt="Slide 4" />
            <img src="{{ asset('images/carousel-5.jpg') }}" class="carousel-slide absolute inset-0 h-full w-full object-cover opacity-0 transition-opacity duration-1000 ease-in-out" alt="Slide 5" />
            <img src="{{ asset('images/carousel-6.jpg') }}" class="carousel-slide absolute inset-0 h-full w-full object-cover opacity-0 transition-opacity duration-1000 ease-in-out" alt="Slide 6" />
            <img src="{{ asset('images/carousel-7.jpg') }}" class="carousel-slide absolute inset-0 h-full w-full object-cover opacity-0 transition-opacity duration-1000 ease-in-out" alt="Slide 7" />
        </div>

        <!-- The Dark Accessibility Overlay (z-10) -->
        <div class="absolute inset-0 z-10 bg-gradient-to-b from-slate-900/80 via-slate-900/60 to-slate-900/90"></div>

        <!-- The Main Content (z-40) -->
        <div class="relative z-40 mt-20 mb-32 flex max-w-4xl flex-col items-center px-6 text-center text-white md:px-12">
            <!-- ANG MALAKING LOGO MO DITO -->
            <div class="mb-6 flex h-32 w-32 items-center justify-center overflow-hidden rounded-full border-4 border-white/20 bg-slate-100/10 shadow-2xl backdrop-blur-sm">
                <img src="{{ asset('images/bdls-logo-large.png') }}" alt="BDLS Logo" class="h-full w-full object-cover" onerror="this.outerHTML = '<span class=\'text-3xl font-black text-white\'>BDLS</span>'" />
            </div>

            <h1 class="mb-6 text-4xl font-extrabold tracking-tight drop-shadow-lg md:text-6xl">
                Maligayang Pagdating sa <br />
                <span class="text-red-500">Barangay Doña Lucia</span> Services
            </h1>
            <p class="mx-auto max-w-2xl text-lg font-medium text-slate-300 drop-shadow-md md:text-xl">Ang iyong mabilis at direktang koneksyon para sa mga serbisyo ng gobyerno, dokumento, at impormasyon ng barangay.</p>

            <div class="mt-10 flex flex-col gap-4 sm:flex-row">
                <a href="/signup" class="rounded-xl bg-red-600 px-8 py-4 font-black tracking-widest text-white uppercase shadow-xl transition-all hover:-translate-y-1 hover:bg-red-700 hover:shadow-red-600/40 active:scale-95">Gumawa ng Account</a>
                <a href="#services" class="rounded-xl border-2 border-white/30 bg-white/10 px-8 py-4 font-black tracking-widest text-white uppercase backdrop-blur-sm transition-all hover:bg-white/20 active:scale-95">Tingnan ang mga Serbisyo</a>
            </div>
        </div>

        <!-- THE FIX 3: SMOOTH & FLUID RED WAVY SVG DIVIDER (z-30) -->
        <div class="leading- absolute bottom-0 left-0 z-30 w-full overflow-hidden">
            <!-- Ginawang h-[10vh] at min-h-[60px] para dynamic sa lahat ng screen size -->
            <svg class="block h-[10vh] min-h-[60px] w-full" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320" preserveAspectRatio="none">
                <!-- Red Accent Wave -->
                <path fill="#dc2626" fill-opacity="0.9" d="M0,224L48,213.3C96,203,192,181,288,181.3C384,181,480,203,576,213.3C672,224,768,224,864,197.3C960,171,1056,117,1152,106.7C1248,96,1344,128,1392,144L1440,160L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
                <!-- Slate-50 Background Wave (Perfect match sa main body) -->
                <path fill="#f8fafc" fill-opacity="1" d="M0,288L48,272C96,256,192,224,288,213.3C384,203,480,213,576,224C672,235,768,245,864,229.3C960,213,1056,171,1152,160C1248,149,1344,171,1392,181.3L1440,192L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
            </svg>
        </div>
    </header>

    <!-- Main Content (Services Section) -->
    <main id="services" class="mx-auto max-w-7xl px-6 py-20 md:px-12">
        <div class="mb-12 text-center">
            <h2 class="text-3xl font-black tracking-tight text-slate-900 uppercase">Mga Serbisyo ng Pamahalaan</h2>
            <p class="mt-2 font-medium text-slate-500">I-access ang mga serbisyo ng barangay sa iyong mga kamay.</p>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <!-- Card 1: Online Services -->
            <a href="#" class="group block rounded-2xl border border-slate-200 bg-white p-8 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-red-300 hover:shadow-xl active:scale-95">
                <div class="mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-red-50 text-red-600 transition-colors group-hover:bg-red-600 group-hover:text-white">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <h3 class="mb-3 text-xl font-bold text-slate-800">Online Queuing System</h3>
                <p class="leading-relaxed text-slate-600">Pumila para sa Barangay Clearance, Indigency, at iba pang dokumento online bago pa man pumunta sa hall.</p>
            </a>

            <!-- Card 2: Queue Tracking -->
            <a href="#" class="group block rounded-2xl border border-slate-200 bg-white p-8 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-slate-400 hover:shadow-xl active:scale-95">
                <div class="mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-600 transition-colors group-hover:bg-slate-900 group-hover:text-white">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                </div>
                <h3 class="mb-3 text-xl font-bold text-slate-800">Live SMS Tracking</h3>
                <p class="leading-relaxed text-slate-600">Alamin ang status ng iyong papel in real-time. Makakatanggap ka ng text kapag handa na para sa release ang iyong dokumento.</p>
            </a>
        </div>
    </main>
    <!-- ABOUT THE BARANGAY (Mandato, Vision, Mission) -->
    <section class="border-t border-slate-200 bg-white py-20">
        <div class="mx-auto max-w-7xl px-6 md:px-12">
            <div class="mb-16 text-center">
                <h2 class="text-3xl font-black tracking-tight text-slate-900 uppercase">Pagkakakilanlan ng Barangay</h2>
                <p class="mt-2 font-medium text-slate-500">Ang aming panumpa at pangarap para sa komunidad ng Doña Lucia.</p>
                <div class="mx-auto mt-6 h-1 w-24 rounded-full bg-red-600"></div>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                <!-- 1. MANDATO CARD -->
                <div class="group flex flex-col justify-between rounded-2xl border border-slate-100 bg-slate-50 p-8 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-slate-300 hover:shadow-xl">
                    <div>
                        <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-200 text-slate-700 transition-colors group-hover:bg-slate-300">
                            <!-- Building/Gov Icon -->
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </div>
                        <h3 class="mb-4 text-xl font-black tracking-widest text-slate-900 uppercase">Mandato</h3>
                        <p class="text-justify text-sm leading-relaxed font-medium text-slate-600">Bilang pangunahing yunit ng pamahalaan, ang Barangay ay nagsisilbing pangunahing tagapagplano at tagapagpatupad ng mga patakaran, plano, programa, proyekto, at aktibidad ng pamahalaan sa komunidad, at bilang isang porum kung saan ang mga sama-samang pananaw ng mga tao ay maaaring ipahayag, isaalang-alang, at kung saan ang mga hindi pagkakaunawaan ay maaaring maayos.</p>
                    </div>
                </div>

                <!-- 2. VISION CARD -->
                <div class="group flex flex-col justify-between rounded-2xl border border-slate-100 bg-slate-50 p-8 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-red-300 hover:shadow-xl">
                    <div>
                        <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-red-100 text-red-600 transition-colors group-hover:bg-red-600 group-hover:text-white">
                            <!-- Eye/Vision Icon -->
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </div>
                        <h3 class="mb-4 text-xl font-black tracking-widest text-slate-900 uppercase">Vision</h3>
                        <p class="text-justify text-sm leading-relaxed font-medium text-slate-600">Ang Barangay Doña Lucia ay sentro ng Agrikulturang kalakaran sa bayan ng Quezon, na may mga mamamayang may takot sa Diyos, may pagkakaisa, may sapat na edukasyon, malusog na pangangatawan, malinis at mapayapang kapaligiran na may kumpletong pasilidad at may pamunuang nagtutulungan at nagkakaisa.</p>
                    </div>
                </div>

                <!-- 3. MISSION CARD -->
                <div class="group flex flex-col justify-between rounded-2xl border border-slate-100 bg-slate-50 p-8 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-slate-400 hover:shadow-xl">
                    <div>
                        <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-900 text-white transition-colors group-hover:bg-slate-800">
                            <!-- Lightning/Mission Icon -->
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        </div>
                        <h3 class="mb-4 text-xl font-black tracking-widest text-slate-900 uppercase">Mission</h3>
                        <p class="text-justify text-sm leading-relaxed font-medium text-slate-600">Maunlad, maka-kalikasan, maayos at mapayapang kapaligiran, malusog na mga mamamayan, maka-Diyos at may makataong namamahala sa komunidad.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- CAROUSEL ANIMATION SCRIPT (Vanilla JS - The Laravel Way) -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const slides = document.querySelectorAll('.carousel-slide');
            let currentSlide = 0;

            // Titiyaking aandar lang ito kung may nadetect na slides
            if (slides.length > 0) {
                setInterval(() => {
                    // Itago ang current
                    slides[currentSlide].classList.remove('opacity-100');
                    slides[currentSlide].classList.add('opacity-0');

                    // Lumipat sa next
                    currentSlide = (currentSlide + 1) % slides.length;

                    // Ipakita ang next
                    slides[currentSlide].classList.remove('opacity-0');
                    slides[currentSlide].classList.add('opacity-100');
                }, 5000); // 5 seconds bawat palit ng litrato
            }
        });
    </script>
    <script src="{{ asset('js/guest-cleanup.js') }}"></script>
</body>
</html>
