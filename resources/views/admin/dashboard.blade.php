@extends('admin.layouts.admin')

@section('content')

    <!-- ===================================== -->
    <!-- TAB 1: PENDING REGISTRATIONS (Default)-->
    <!-- ===================================== -->
    <div id="tab-pending" class="tab-content block">
        <div class="mb-4 flex justify-between items-center">
            <h2 class="text-xl font-bold text-slate-800">Mga Kailangang I-verify</h2>
            <span class="bg-slate-200 text-slate-700 px-3 py-1 rounded-full text-sm font-bold">Total: {{ $pendingAccounts->count() }}</span>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            @forelse($pendingAccounts as $user)
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden flex flex-col hover:shadow-md transition-shadow">
                    
                    <!-- Card Header (Counter & Status) -->
                    <div class="bg-slate-50 border-b border-slate-200 px-4 py-3 flex justify-between items-center">
                        <span class="font-bold text-slate-900 text-sm tracking-widest">RESIDENT #{{ $loop->iteration }}</span>
                        <span class="bg-amber-100 text-amber-700 text-xs font-bold px-3 py-1 rounded-full border border-amber-200">Under Review</span>
                    </div>

                    <div class="p-5 flex-1 flex flex-col">
                        <!-- IDENTIFICATION (Lastname, Firstname format) -->
                        <div class="mb-4 pb-4 border-b border-slate-100">
                            <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tight">
                                {{ $user->last_name }}, {{ $user->first_name }} {{ $user->middle_name }} {{ $user->suffix }}
                            </h3>
                            
                            <div class="grid grid-cols-2 gap-2 mt-3">
                                <div>
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Contact Number</p>
                                    <p class="font-mono font-bold text-slate-900">{{ $user->contact_number }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kasarian</p>
                                    <p class="font-bold text-slate-900">{{ $user->sex }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Date of Birth</p>
                                    <!-- The Laravel Way: Carbon Formatting -->
                                    <p class="font-bold text-slate-900">{{ \Carbon\Carbon::parse($user->date_of_birth)->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Edad</p>
                                    <p class="font-bold text-slate-900">{{ \Carbon\Carbon::parse($user->date_of_birth)->age }} taong gulang</p>
                                </div>
                            </div>
                        </div>

                        <!-- IMAGE PREVIEW (Modal Triggers) -->
                        <div class="flex flex-col sm:flex-row gap-4 mb-6">
                            <div class="flex-1 cursor-pointer group" onclick="openModal('{{ asset('storage/' . $user->id_photo_path) }}', 'Valid ID')">
                                <p class="text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Valid ID</p>
                                <div class="relative h-36 rounded-lg border-2 border-slate-200 overflow-hidden group-hover:border-slate-900 transition-colors">
                                    <img src="{{ asset('storage/' . $user->id_photo_path) }}" class="w-full h-full object-cover">
                                </div>
                            </div>
                            <div class="flex-1 cursor-pointer group" onclick="openModal('{{ asset('storage/' . $user->selfie_photo_path) }}', 'Selfie')">
                                <p class="text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Selfie</p>
                                <div class="relative h-36 rounded-lg border-2 border-slate-200 overflow-hidden group-hover:border-slate-900 transition-colors">
                                    <img src="{{ asset('storage/' . $user->selfie_photo_path) }}" class="w-full h-full object-cover">
                                </div>
                            </div>
                        </div>

                        <!-- ACTION BUTTONS -->
                        <div class="flex gap-3 mt-auto">
                            <!-- APPROVE FORM -->
                            <form action="{{ route('admin.approve_account', $user->id) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit" class="w-full bg-slate-900 text-white font-bold py-3 rounded-lg shadow hover:bg-slate-800 active:scale-95 transition-all">
                                    Approve
                                </button>
                            </form>
                            
                            <!-- REJECT FORM -->
                            <form action="{{ route('admin.reject_account', $user->id) }}" method="POST" class="flex-1" onsubmit="return confirm('Sigurado ka bang gusto mong i-reject at burahin ang account ni {{ $user->first_name }}?');">
                                @csrf
                                <button type="submit" class="w-full bg-red-50 text-red-600 border border-red-200 font-bold py-3 rounded-lg hover:bg-red-100 active:scale-95 transition-all">
                                    Reject
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-20 bg-white rounded-xl border border-slate-200 shadow-sm">
                    <svg class="mx-auto h-16 w-16 text-slate-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-slate-500 font-bold text-xl">Walang nakapila for review.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- ===================================== -->
    <!-- MGA BLANGKONG TABS PARA SA IBANG MODULES -->
    <!-- ===================================== -->
    <div id="tab-queue" class="tab-content hidden"><h2 class="text-xl font-bold text-slate-800">Queue & Processing Module (WIP)</h2></div>
    <div id="tab-walkin" class="tab-content hidden"><h2 class="text-xl font-bold text-slate-800">Walk-in Requests Module (WIP)</h2></div>
    <div id="tab-announcements" class="tab-content hidden"><h2 class="text-xl font-bold text-slate-800">Announcements Module (WIP)</h2></div>
    <div id="tab-audit" class="tab-content hidden"><h2 class="text-xl font-bold text-slate-800">System Audit Logs (WIP)</h2></div>

    <!-- IMAGE VIEWER MODAL -->
    <div id="imageModal" class="hidden fixed inset-0 z-[1] bg-slate-900/90 backdrop-blur-sm flex justify-center items-center p-4">
        <div class="relative max-w-4xl w-full bg-white rounded-xl shadow-2xl overflow-hidden">
            <div class="flex justify-between items-center p-4 border-b border-slate-200 bg-slate-50">
                <h3 id="modalTitle" class="text-lg font-bold text-slate-900 uppercase">Preview</h3>
                <button onclick="closeModal()" class="text-slate-500 hover:text-red-600 font-bold text-2xl leading-none">&times;</button>
            </div>
            <div class="p-4 bg-slate-200 flex justify-center">
                <img id="modalImg" src="" class="max-h-[65vh] object-contain rounded shadow-sm">
            </div>
            <div class="p-4 border-t border-slate-200 bg-slate-50 text-right">
                <button onclick="closeModal()" class="bg-slate-900 text-white font-bold py-2 px-8 rounded-lg hover:bg-slate-800 active:scale-95 transition-all">Isara</button>
            </div>
        </div>
    </div>

    <!-- PAG-PAPASA NG DATA SA EXTERNAL JAVASCRIPT -->
    <script>
        window.initialPendingCount = {{ $pendingAccounts->count() }};
        window.pollingUrl = "{{ route('admin.api.pending_count') }}";
        
        // Modal Scripts
        function openModal(imageSrc, title) {
            document.getElementById('modalImg').src = imageSrc;
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('imageModal').classList.remove('hidden');
        }
        function closeModal() {
            document.getElementById('imageModal').classList.add('hidden');
            document.getElementById('modalImg').src = '';
        }
    </script>
    
    <!-- EXTERNAL JS PARA SA AJAX POLLING -->
    <script src="{{ asset('js/admin-dashboard.js') }}"></script>
@endsection