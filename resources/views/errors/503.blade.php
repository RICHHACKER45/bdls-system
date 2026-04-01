<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Under Maintenance - BDLS</title>
    <!-- Gumamit ng CDN para sigurado na load ang Tailwind kahit naka-down ang Vite -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-slate-50 font-sans text-slate-900 min-h-screen flex items-center justify-center p-4">
    
    <div class="bg-white p-8 rounded-2xl shadow-xl max-w-md w-full text-center border border-slate-200">
        <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        
        <h1 class="text-2xl font-extrabold text-slate-900 mb-2">Ang aming website ay down.</h1>
        <p class="text-sm text-slate-600 mb-6 font-medium">
            May inaayos lang kami sa Website. Maaring bumalik na lang po kayo mamaya!
        </p>

        <!-- Huwag gamitin ang route() dito dahil naka-down ang routing engine -->
        <a href="/" class="inline-block w-full bg-slate-900 hover:bg-slate-800 active:scale-95 text-white font-bold py-3.5 px-4 rounded-xl transition-all shadow-md">
            I-refresh ang Page
        </a>
    </div>

</body>
</html>