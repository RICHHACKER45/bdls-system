<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Forgot Password - BDLS</title>
    @vite (['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-50 p-4 font-sans text-slate-900 antialiased">
    <!-- BACK BUTTON -->
    <div class="absolute top-4 left-4 md:top-8 md:left-8">
        <a href="{{ route('login') }}" class="flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-slate-500 transition-all duration-200 hover:text-red-600 focus:ring-4 focus:ring-slate-200 active:scale-95 active:bg-slate-200">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Bumalik sa Login
        </a>
    </div>

    <div class="w-full max-w-md rounded-2xl border border-slate-100 bg-white p-6 shadow-xl md:p-8">
        <div class="mb-6 text-center">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-900">
                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4v-4l5.659-5.659C9.092 9.896 9.598 8.558 10.657 7.5A6 6 0 0121 9z"></path></svg>
            </div>
            <h2 class="text-2xl font-bold text-slate-900">Forgot Password</h2>
            <p class="mt-2 text-sm text-slate-500">I-type ang iyong nakarehistrong contact number upang makatanggap ng 6-digit OTP code para sa pag-reset.</p>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-r-lg border-l-4 border-red-500 bg-red-50 p-4 text-sm font-medium text-red-700 shadow-sm">{{ $errors->first() }}</div>
        @endif

        <form action="{{ route('password.send_otp') }}" method="POST">
            @csrf
            <div class="mb-6">
                <label class="mb-1 block text-center text-sm font-semibold text-slate-700">Contact Number</label>
                <input type="tel" name="contact_number" placeholder="09XXXXXXXXX" required maxlength="11" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="w-full rounded-lg border border-slate-300 px-4 py-3.5 text-center font-mono text-xl font-bold tracking-widest transition-all outline-none focus:border-red-600 focus:ring-2 focus:ring-red-600" />
            </div>
            <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-8 py-3.5 font-bold text-white shadow-md transition-all hover:bg-slate-800 active:scale-95">Ipadala ang OTP Code</button>
        </form>
    </div>
</body>
</html>
