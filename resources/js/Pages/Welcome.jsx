import React, { useState, useEffect } from 'react';
import { Link, Head } from '@inertiajs/react';

export default function Welcome() {
    const [currentSlide, setCurrentSlide] = useState(0);
    const slides = [
        '/images/carousel-1.jpg',
        '/images/carousel-2.jpg',
        '/images/carousel-3.jpg',
        '/images/carousel-4.jpg',
        '/images/carousel-5.jpg',
        '/images/carousel-6.jpg',
        '/images/carousel-7.jpg',
    ];

    useEffect(() => {
        const interval = setInterval(() => {
            setCurrentSlide((prev) => (prev + 1) % slides.length);
        }, 5000);
        return () => clearInterval(interval);
    }, [slides.length]);

    const triggerSmartEmail = (e) => {
        e.preventDefault();
        const subject = encodeURIComponent('BDLS System Inquiry');
        const body = encodeURIComponent(''); // Empty body for general inquiry

        // Detect if the user is on a mobile device
        const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);

        if (isMobile) {
            // Mobile: Force default mail app using invisible anchor
            const mailtoLink = document.createElement('a');
            mailtoLink.href = `mailto:barangaysec@bdlsgov.ph?subject=${subject}&body=${body}`;
            document.body.appendChild(mailtoLink);
            mailtoLink.click();
            document.body.removeChild(mailtoLink);
        } else {
            // Desktop: Force Web Gmail in a new tab
            const gmailUrl = `https://mail.google.com/mail/?view=cm&fs=1&to=barangaysec@bdlsgov.ph&su=${subject}&body=${body}`;
            window.open(gmailUrl, '_blank');
        }
    };

    const scrollToServices = (e) => {
        e.preventDefault();
        document.getElementById('services').scrollIntoView({ behavior: 'smooth' });
    };

    return (
        <div className="bg-slate-50 font-sans text-slate-900 antialiased">
            <Head title="Welcome - Barangay Doña Lucia" />

            <div className="fixed top-0 left-0 z-50 w-full">
                {/* LAUNCH NOTICE BANNER
                <div className="bg-red-700 px-4 py-2.5 text-center text-[10px] font-black tracking-widest text-white uppercase shadow-md sm:text-xs">
                    🎉 Opisyal nang inilunsad ang BDLS System! Mag-register na upang subukan ang
                    aming digital na serbisyo.
                </div>
                */}
                <nav className="flex w-full items-center justify-between bg-gradient-to-b from-slate-900/90 to-transparent px-6 py-4 md:px-12">
                    <div className="flex items-center gap-3">
                        <span className="text-xl font-black tracking-widest text-white drop-shadow-md">
                            BDLS
                        </span>
                    </div>
                    <div className="flex items-center gap-4">
                        <Link
                            href="/login"
                            className="rounded-xl px-6 py-2.5 text-sm font-bold tracking-widest text-white uppercase transition-all duration-200 hover:bg-white/20 active:scale-95"
                        >
                            Login
                        </Link>
                        <Link
                            href="/signup"
                            className="rounded-xl bg-red-600 px-6 py-2.5 text-sm font-black tracking-widest text-white uppercase shadow-lg transition-all hover:bg-red-700 hover:shadow-red-600/30 active:scale-95"
                        >
                            Mag-Signup
                        </Link>
                    </div>
                </nav>
            </div>

            <header className="relative flex min-h-[90vh] items-center justify-center overflow-hidden">
                <div id="hero-carousel" className="absolute inset-0 z-0 h-full w-full bg-slate-900">
                    {slides.map((slide, index) => (
                        <img
                            key={index}
                            src={slide}
                            className={`absolute inset-0 h-full w-full object-cover transition-opacity duration-1000 ease-in-out ${
                                index === currentSlide ? 'opacity-100' : 'opacity-0'
                            }`}
                            alt={`Slide ${index + 1}`}
                        />
                    ))}
                </div>

                <div className="absolute inset-0 z-10 bg-gradient-to-b from-slate-900/80 via-slate-900/60 to-slate-900/90"></div>

                <div className="relative z-40 mt-28 mb-32 flex max-w-4xl flex-col items-center px-6 text-center text-white md:px-12">
                    <div className="mb-6 flex h-32 w-32 items-center justify-center overflow-hidden rounded-full border-4 border-white/20 bg-slate-100/10 shadow-2xl backdrop-blur-sm">
                        <img
                            src="/images/bdls-logo-large.png"
                            alt="BDLS Logo"
                            className="h-full w-full object-cover"
                            onError={(e) => {
                                e.currentTarget.style.display = 'none';
                                e.currentTarget.parentElement.innerHTML =
                                    '<span class="text-3xl font-black text-white">BDLS</span>';
                            }}
                        />
                    </div>

                    <h1 className="mb-6 text-4xl font-extrabold tracking-tight drop-shadow-lg md:text-6xl">
                        Welcome sa <br />
                        <span className="text-red-500">Barangay Doña Lucia</span> Services
                    </h1>
                    <p className="mx-auto max-w-2xl text-lg font-medium text-slate-300 drop-shadow-md md:text-xl">
                        Ang iyong mabilis at direktang koneksyon para sa mga government services,
                        dokumento, at impormasyon ng barangay.
                    </p>

                    <div className="mt-10 flex flex-col items-center gap-4">
                        <div className="flex w-full flex-col gap-4 sm:w-auto sm:flex-row">
                            <Link
                                href="/signup"
                                className="rounded-xl bg-red-600 px-8 py-4 font-black tracking-widest text-white uppercase shadow-xl transition-all hover:-translate-y-1 hover:bg-red-700 hover:shadow-red-600/40 active:scale-95"
                            >
                                Gumawa ng Account
                            </Link>
                            <a
                                href="#services"
                                onClick={scrollToServices}
                                className="rounded-xl border-2 border-white/30 bg-white/10 px-8 py-4 font-black tracking-widest text-white uppercase backdrop-blur-sm transition-all hover:bg-white/20 active:scale-95"
                            >
                                Tingnan ang Serbisyo
                            </a>
                        </div>
                        <p className="mt-2 text-sm font-medium text-slate-300">
                            May account na?{' '}
                            <Link href="/login" className="font-bold text-white transition-colors hover:text-red-400 underline decoration-white/30 hover:decoration-red-400 underline-offset-4">
                                Mag-login dito
                            </Link>
                        </p>
                    </div>
                </div>

                <div className="absolute bottom-0 left-0 z-30 w-full overflow-hidden leading-none translate-y-px">
                    <svg
                        className="block h-[10vh] min-h-[60px] w-full"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 1440 320"
                        preserveAspectRatio="none"
                    >
                        <path
                            fill="#dc2626"
                            fillOpacity="0.9"
                            d="M0,224L48,213.3C96,203,192,181,288,181.3C384,181,480,203,576,213.3C672,224,768,224,864,197.3C960,171,1056,117,1152,106.7C1248,96,1344,128,1392,144L1440,160L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"
                        ></path>
                        <path
                            fill="#f8fafc"
                            fillOpacity="1"
                            d="M0,288L48,272C96,256,192,224,288,213.3C384,203,480,213,576,224C672,235,768,245,864,229.3C960,213,1056,171,1152,160C1248,149,1344,171,1392,181.3L1440,192L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"
                        ></path>
                    </svg>
                </div>
            </header>

            <main id="services" className="relative bg-slate-50 pt-20 pb-32">
                <style>{`
                    .hide-scrollbar::-webkit-scrollbar { display: none; }
                    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
                `}</style>

                <div className="mx-auto max-w-7xl px-6 text-center md:px-12">
                    <h2 className="text-3xl font-black tracking-tight text-slate-900 uppercase">
                        Government Services
                    </h2>
                    <p className="mt-2 font-medium text-slate-500">
                        I-access ang mga serbisyo ng barangay sa iyong mga kamay. Mag-scroll pakanan
                        para makita lahat.
                    </p>
                </div>

                {/* HORIZONTAL SCROLL CAROUSEL */}
                <div className="hide-scrollbar mt-12 flex w-full snap-x snap-mandatory gap-6 overflow-x-auto px-6 pt-4 pb-12 md:justify-center md:px-12">
                    {/* CARD 1: Online Queuing */}
                    <div className="group relative flex w-[85vw] shrink-0 snap-center flex-col justify-between rounded-3xl border border-slate-200 bg-white p-8 shadow-sm transition-all duration-500 hover:-translate-y-2 hover:border-red-300 hover:shadow-2xl sm:w-96">
                        <div className="mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-red-50 text-red-600 shadow-inner transition-colors group-hover:bg-red-600 group-hover:text-white">
                            <svg
                                className="h-8 w-8"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                ></path>
                            </svg>
                        </div>
                        <div>
                            <h3 className="mb-3 text-xl font-black tracking-tight text-slate-800">
                                Online Queuing System
                            </h3>
                            <p className="text-sm leading-relaxed font-medium text-slate-600">
                                Pumila para sa Barangay Clearance, Indigency, at iba pang dokumento
                                online bago pa man pumunta sa hall.
                            </p>
                        </div>
                    </div>

                    {/* CARD 2: Live SMS Tracking */}
                    <div className="group relative flex w-[85vw] shrink-0 snap-center flex-col justify-between rounded-3xl border border-slate-200 bg-white p-8 shadow-sm transition-all duration-500 hover:-translate-y-2 hover:border-blue-300 hover:shadow-2xl sm:w-96">
                        <div className="mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 shadow-inner transition-colors group-hover:bg-blue-600 group-hover:text-white">
                            <svg
                                className="h-8 w-8"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth="2"
                                    d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"
                                ></path>
                            </svg>
                        </div>
                        <div>
                            <h3 className="mb-3 text-xl font-black tracking-tight text-slate-800">
                                Live SMS Tracking
                            </h3>
                            <p className="text-sm leading-relaxed font-medium text-slate-600">
                                Alamin ang status ng iyong papel in real-time. Makakatanggap ka ng
                                text kapag ready na for release ang iyong dokumento.
                            </p>
                        </div>
                    </div>

                    {/* CARD 3: Automated ID Scan */}
                    <div className="group relative flex w-[85vw] shrink-0 snap-center flex-col justify-between rounded-3xl border border-slate-200 bg-white p-8 shadow-sm transition-all duration-500 hover:-translate-y-2 hover:border-emerald-300 hover:shadow-2xl sm:w-96">
                        <div className="mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 shadow-inner transition-colors group-hover:bg-emerald-600 group-hover:text-white">
                            <svg
                                className="h-8 w-8"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth="2"
                                    d="M10 21h7a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v11m0 5l3-3m-3 3l-3-3"
                                ></path>
                            </svg>
                        </div>
                        <div>
                            <h3 className="mb-3 text-xl font-black tracking-tight text-slate-800">
                                Automated ID Scan
                            </h3>
                            <p className="text-sm leading-relaxed font-medium text-slate-600">
                                Mabilis na pag-verify ng iyong account gamit ang aming AI-powered ID
                                scanner na direktang nakakonekta sa census.
                            </p>
                        </div>
                    </div>



                    {/* Invisible Placeholder to allow final card to center properly on mobile */}
                    <div className="w-4 shrink-0 sm:w-12 md:hidden"></div>
                </div>

                {/* BOTTOM WAVE SEPARATOR */}
                <div className="absolute bottom-0 left-0 w-full overflow-hidden leading-none translate-y-px">
                    <svg
                        className="block h-[8vh] min-h-[50px] w-full"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 1440 320"
                        preserveAspectRatio="none"
                    >
                        <path
                            fill="#ffffff"
                            fillOpacity="1"
                            d="M0,192L48,197.3C96,203,192,213,288,192C384,171,480,117,576,117.3C672,117,768,171,864,197.3C960,224,1056,224,1152,197.3C1248,171,1344,117,1392,90.7L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"
                        ></path>
                    </svg>
                </div>
            </main>

            <section className="bg-white pt-8 pb-20">
                <div className="mx-auto max-w-7xl px-6 md:px-12">
                    <div className="mb-16 text-center">
                        <h2 className="text-3xl font-black tracking-tight text-slate-900 uppercase">
                            Pagkakakilanlan ng Barangay
                        </h2>
                        <p className="mt-2 font-medium text-slate-500">
                            Ang aming panumpa at pangarap para sa komunidad ng Doña Lucia.
                        </p>
                        <div className="mx-auto mt-6 h-1 w-24 rounded-full bg-red-600"></div>
                    </div>

                    <div className="grid grid-cols-1 gap-8 md:grid-cols-3">
                        <div className="group flex flex-col justify-between rounded-2xl border border-slate-100 bg-slate-50 p-8 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-slate-300 hover:shadow-xl">
                            <div>
                                <div className="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-200 text-slate-700 transition-colors group-hover:bg-slate-300">
                                    <svg
                                        className="h-7 w-7"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                        ></path>
                                    </svg>
                                </div>
                                <h3 className="mb-4 text-xl font-black tracking-widest text-slate-900 uppercase">
                                    Mandato
                                </h3>
                                <p className="text-justify text-sm leading-relaxed font-medium text-slate-600">
                                    Bilang pangunahing yunit ng pamahalaan, ang Barangay ay
                                    nagsisilbing pangunahing tagapagplano at tagapagpatupad ng yunit
                                    ng mga patakaran, plano, programa, proyekto, at aktibidad ng
                                    pamahalaan sa komunidad, at bilang isang porum kung saan ang mga
                                    sama-samang pananaw ng mga tao ay maaaring ipahayag,
                                    isaalang-alang, at kung saan ang mga hindi pagkakaunawaan ay
                                    maaaring maayos.
                                </p>
                            </div>
                        </div>

                        <div className="group flex flex-col justify-between rounded-2xl border border-slate-100 bg-slate-50 p-8 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-red-300 hover:shadow-xl">
                            <div>
                                <div className="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-red-100 text-red-600 transition-colors group-hover:bg-red-600 group-hover:text-white">
                                    <svg
                                        className="h-7 w-7"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                        ></path>
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                        ></path>
                                    </svg>
                                </div>
                                <h3 className="mb-4 text-xl font-black tracking-widest text-slate-900 uppercase">
                                    Vision
                                </h3>
                                <p className="text-justify text-sm leading-relaxed font-medium text-slate-600">
                                    Ang Barangay Doña Lucia ay sentro ng Agrikulturang kalakaran sa
                                    bayan ng Quezon, na may mga mamamayan may takot sa Diyos may
                                    pag-kakaisa, may sapat na edukasyon, malusog na pangangatawan,
                                    malinis at mapayapang kapaligiran na may kumpletong pasilidad at
                                    may pamunuang nagtutulungan at nagkakaisa.
                                </p>
                            </div>
                        </div>

                        <div className="group flex flex-col justify-between rounded-2xl border border-slate-100 bg-slate-50 p-8 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-slate-400 hover:shadow-xl">
                            <div>
                                <div className="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-900 text-white transition-colors group-hover:bg-slate-800">
                                    <svg
                                        className="h-7 w-7"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth="2"
                                            d="M13 10V3L4 14h7v7l9-11h-7z"
                                        ></path>
                                    </svg>
                                </div>
                                <h3 className="mb-4 text-xl font-black tracking-widest text-slate-900 uppercase">
                                    Mission
                                </h3>
                                <p className="text-justify text-sm leading-relaxed font-medium text-slate-600">
                                    Maunlad, maka-kalikasan, maayos at mapayapang kapaligiran,
                                    malusog na mga mamamayan, maka-Diyos at may makataong namamahala
                                    sa komunidad.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* --- GRAND FOOTER: CONTACT & SUPPORT --- */}
            <footer className="bg-slate-950 py-12 text-slate-300 sm:py-16">
                <div className="mx-auto max-w-7xl px-6 md:px-12">
                    <div className="grid grid-cols-1 gap-12 md:grid-cols-3 lg:gap-16">
                        {/* Column 1: Brand & Address */}
                        <div className="flex flex-col gap-6">
                            <div className="flex items-center gap-3">
                                <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-white p-1">
                                    <img
                                        src="/images/bdls-logo-large.png"
                                        alt="BDLS Logo"
                                        className="h-full w-full object-contain"
                                    />
                                </div>
                                <span className="text-xl font-black tracking-widest text-white">
                                    BDLS
                                </span>
                            </div>
                            <p className="text-sm leading-relaxed font-medium">
                                Isang digital na inisyatibo para sa mas mabilis at mas organisadong
                                serbisyo publiko sa pamayanan ng Barangay Doña Lucia.
                            </p>
                            <div>
                                <h4 className="mb-2 text-[10px] font-black tracking-widest text-slate-500 uppercase">
                                    Lokasyon
                                </h4>
                                <p className="text-sm font-bold text-white">
                                    Barangay Hall, Doña Lucia
                                </p>
                                <p className="text-sm">Quezon, Nueva Ecija, Philippines</p>
                            </div>
                        </div>

                        {/* Column 2: Contact Info */}
                        <div className="flex flex-col gap-6">
                            <h4 className="text-sm font-black tracking-widest text-white uppercase">
                                Makipag-ugnayan
                            </h4>
                            <div className="space-y-4 text-sm font-medium">
                                <div className="flex items-start gap-3">
                                    <svg
                                        className="h-5 w-5 shrink-0 text-red-500"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth="2"
                                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                        ></path>
                                    </svg>
                                    <div>
                                        <p className="font-bold text-white">Opisyal na Email</p>
                                        <button
                                            onClick={triggerSmartEmail}
                                            className="transition-all hover:text-red-400"
                                        >
                                            barangaysec@bdlsgov.ph
                                        </button>
                                    </div>
                                </div>
                                <div className="flex items-start gap-3">
                                    <svg
                                        className="h-5 w-5 shrink-0 text-red-500"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth="2"
                                            d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"
                                        ></path>
                                    </svg>
                                    <div>
                                        <p className="font-bold text-white">Contact Numbers</p>
                                        <p>
                                            Globe / TM:{' '}
                                            <a
                                                href="sms:09171234567"
                                                className="font-mono text-white transition-all hover:text-red-400"
                                            >
                                                0917 123 4567
                                            </a>
                                        </p>
                                        <p>
                                            Smart / TNT:{' '}
                                            <a
                                                href="sms:09181234567"
                                                className="font-mono text-white transition-all hover:text-red-400"
                                            >
                                                0918 123 4567
                                            </a>
                                        </p>
                                        <p>
                                            DITO:{' '}
                                            <a
                                                href="sms:09911234567"
                                                className="font-mono text-white transition-all hover:text-red-400"
                                            >
                                                0991 123 4567
                                            </a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Column 3: Quick Action */}
                        <div className="flex flex-col gap-6">
                            <h4 className="text-sm font-black tracking-widest text-white uppercase">
                                May Isyu o Katanungan?
                            </h4>
                            <p className="text-sm font-medium">
                                I-click ang button sa ibaba upang direktang magpadala ng email sa
                                aming opisina.
                            </p>
                            <button
                                onClick={triggerSmartEmail}
                                className="inline-flex w-fit items-center gap-2 rounded-xl bg-red-600 px-6 py-3.5 text-xs font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-red-700 active:scale-95"
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
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                    ></path>
                                </svg>
                                Mag-Email Dito
                            </button>
                        </div>
                    </div>

                    <div className="mt-12 border-t border-slate-800 pt-8 text-center sm:mt-16">
                        <p className="text-xs font-medium text-slate-500">
                            &copy; {new Date().getFullYear()} Barangay Doña Lucia Services (BDLS).
                            All rights reserved. <br className="sm:hidden" />A Capstone Project by
                            NEUST CICT Students.
                        </p>
                    </div>
                </div>
            </footer>
        </div>
    );
}
