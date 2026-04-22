<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Session Expired - BDLS</title>
    @vite (['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-50 p-4 font-sans text-slate-900">
    <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-xl">
        <!-- Warning Icon (Red Accent - 10% Rule) -->
        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-red-100 text-red-600">
            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>

        <!-- Typography (Slate Primary - 30% Rule) -->
        <h1 class="mb-2 text-2xl font-extrabold text-slate-900">Pahina Nag-Expire</h1>
        <p class="mb-6 text-sm font-medium text-slate-600">Masyadong matagal na nabakante ang iyong session, o nag-logout ka na sa kabilang tab. Para sa iyong kaligtasan at seguridad, paki-refresh ang pahina o bumalik sa Home.</p>

        <!-- Primary Action Button (Slate-900 Accent) -->
        <a href="{{ route('home') }}" class="inline-block w-full rounded-xl bg-slate-900 px-4 py-3.5 font-bold text-white shadow-md transition-all hover:bg-slate-800 active:scale-95"> Bumalik sa Home </a>
    </div>
</body>
</html>