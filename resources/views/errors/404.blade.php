<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - BDLS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 font-sans text-slate-900 min-h-screen flex items-center justify-center p-4">
    
    <div class="bg-white p-8 rounded-2xl shadow-xl max-w-md w-full text-center border border-slate-200">
        <!-- Warning Icon (Red Accent - 10% Rule) -->
        <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        
        <!-- Typography (Slate Primary - 30% Rule) -->
        <h1 class="text-2xl font-extrabold text-slate-900 mb-2">Walang page dito.</h1>
        <p class="text-sm text-slate-600 mb-6 font-medium">
            Wala dito ang iyong hinahanap. Maaring bumalik na sa Homepage
        </p>

        <!-- Primary Action Button (Slate-900 Accent) -->
        <a href="{{ route('home') }}" class="inline-block w-full bg-slate-900 hover:bg-slate-800 active:scale-95 text-white font-bold py-3.5 px-4 rounded-xl transition-all shadow-md">
            Bumalik sa Home
        </a>
    </div>

</body>
</html>