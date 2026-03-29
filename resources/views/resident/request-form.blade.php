@extends('resident.layouts.app')

@section('title', 'Gumawa ng Request')

@section('content')
<div class="max-w-3xl mx-auto pb-10">
    <!-- BACK BUTTON (SPA Illusion / Tactile UX) -->
    <a href="{{ route('resident.dashboard') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-slate-900 mb-6 transition-all active:scale-95">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Bumalik sa Dashboard
    </a>

    <!-- 60/30/10 FORM CARD -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 md:p-8">
        <h1 class="text-2xl font-bold text-slate-900 mb-2">Service Request Form</h1>
        <p class="text-slate-500 text-sm mb-8">Kumpletuhin ang mga detalye sa ibaba para makakuha ng queue number.</p>

        <!-- Tandaan: Papunta ito sa 'store' function na gagawin natin mamaya -->
        <form action="{{ route('resident.request.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <!-- 1. URI NG DOKUMENTO -->
            <div>
                <label class="block text-sm font-bold text-slate-800 mb-2">Uri ng Dokumento <span class="text-red-500">*</span></label>
                <!-- Pansamantala: Hardcoded options muna para maka-focus sa UI -->
                <select name="document_type_id" required class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-slate-900 outline-none bg-slate-50 transition-all cursor-pointer">
                    <option value="">-- Pumili ng Dokumento --</option>
                    <option value="1">Barangay Clearance</option>
                    <option value="2">Certificate of Indigency</option>
                    <option value="3">Certificate of Residency</option>
                </select>
            </div>

            <!-- 2. PURPOSE -->
            <div>
                <label class="block text-sm font-bold text-slate-800 mb-2">Layunin (Purpose) <span class="text-red-500">*</span></label>
                <input type="text" name="purpose" placeholder="Hal. Requirement sa Trabaho, Scholarship..." required class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-slate-900 outline-none bg-slate-50 transition-all">
            </div>

            <!-- 3. PREFERRED PICKUP TIME -->
            <div>
                <label class="block text-sm font-bold text-slate-800 mb-2">Kailan mo gustong kunin? <span class="text-red-500">*</span></label>
                <input type="datetime-local" name="preferred_pickup_time" required class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-slate-900 outline-none bg-slate-50 transition-all cursor-pointer">
                <p class="text-xs text-amber-600 font-bold mt-2">⚠️ Paalala: Nakadepende pa rin ito sa approval at availability ng Admin.</p>
            </div>

            <!-- 4. ADDITIONAL DETAILS (Optional) -->
            <div>
                <label class="block text-sm font-bold text-slate-800 mb-2">Karagdagang Detalye (Optional)</label>
                <textarea name="additional_details" rows="3" placeholder="I-type dito kung may espesyal kang habilin..." class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-slate-900 outline-none bg-slate-50 transition-all"></textarea>
            </div>

            <!-- SUBMIT BUTTON (Right-aligned para sa natural na ending ng F-Pattern) -->
            <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button type="submit" class="w-full sm:w-auto bg-slate-900 hover:bg-slate-800 active:scale-95 text-white font-bold py-3 px-8 rounded-xl transition-all shadow-md">
                    I-submit ang Request
                </button>
            </div>
        </form>
    </div>
</div>
@endsection