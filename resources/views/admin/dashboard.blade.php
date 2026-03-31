<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - BDLS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 font-sans text-slate-900 min-h-screen flex flex-col items-center justify-center">

    <div class="bg-white p-10 rounded-2xl shadow-xl text-center border border-slate-200">
        <div class="w-16 h-16 bg-slate-900 text-white rounded-full flex items-center justify-center mx-auto mb-4 font-bold text-xl">
            ADMIN
        </div>
        <h1 class="text-3xl font-extrabold text-slate-900 mb-2">Eto ang Admin Dashboard</h1>
        <p class="text-slate-600 font-medium mb-8">
            Welcome, {{ Auth::user()->first_name }}! <br>
            <span class="text-sm text-amber-600 font-bold">(Role mo sa Database: {{ Auth::user()->role }})</span>
        </p>

        <!-- PANSAMANTALANG LOGOUT BUTTON PARA HINDI KA MA-STUCK -->
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-8 rounded-xl transition-all active:scale-95 shadow-md">
                Mag-Logout
            </button>
        </form>
    </div>
</body>
</html>