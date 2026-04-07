@extends('admin.layouts.admin')

@section('content')

<!-- ===================================== -->
<!-- TAB 1: PENDING REGISTRATIONS & ACCOUNTS -->
<!-- ===================================== -->
<div id="tab-pending" class="tab-content {{ session('active_tab', 'pending') == 'pending' ? 'block' : 'hidden' }}">

    <!-- TASK 1: Fix mobile search bar horizontal scroll issue -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 mb-6">
        <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-wrap gap-2 w-full min-w-0">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Maghanap ng pangalan o number..." class="flex-1 min-w-[200px] border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-slate-900 focus:outline-none outline-none">
            <select name="sort" onchange="this.form.submit()" class="border border-slate-300 rounded-lg px-4 py-2 bg-slate-50 focus:outline-none">
                <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Pinakabago</option>
                <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Pinakaluma</option>
            </select>
            <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-bold px-6 py-2 rounded-lg transition-all active:scale-95 shrink-0">Search</button>
        </form>
    </div>

    <div class="flex gap-2 mb-6 overflow-x-auto pb-2">
        <button onclick="showSubTab('sub-pending', this)" class="sub-tab-btn px-5 py-2 rounded-full bg-slate-900 text-white font-bold text-sm whitespace-nowrap transition-all">
            Under Review ({{ $pendingAccounts->count() }})
        </button>
        <button onclick="showSubTab('sub-approved', this)" class="sub-tab-btn px-5 py-2 rounded-full bg-slate-200 text-slate-700 hover:bg-slate-300 font-bold text-sm whitespace-nowrap transition-all">
            Approved ({{ $approvedAccounts->count() }})
        </button>
        <button onclick="showSubTab('sub-rejected', this)" class="sub-tab-btn px-5 py-2 rounded-full bg-slate-200 text-slate-700 hover:bg-red-200 hover:text-red-700 font-bold text-sm whitespace-nowrap transition-all">
            Rejected / Locked ({{ $rejectedAccounts->count() }})
        </button>
    </div>

    <!-- SUB-TAB CONTENT: PENDING -->
    <div id="sub-pending" class="sub-tab-content grid grid-cols-1 xl:grid-cols-2 gap-6">
        @forelse($pendingAccounts as $user)
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex flex-col sm:flex-row gap-5 transition-all hover:shadow-md">
            <!-- TASK 1: Clickable Images -->
            <div class="flex sm:flex-col gap-2 shrink-0">
                <div class="relative group">
                    <img src="{{ asset('storage/' . $user->id_photo_path) }}" class="w-20 h-20 sm:w-24 sm:h-24 rounded-lg object-cover border border-slate-200 cursor-pointer group-hover:opacity-75 transition-all" onclick="openModal(this.src, 'Valid ID')">
                    <span class="absolute bottom-1 right-1 bg-slate-900/60 text-[8px] text-white px-1 rounded font-bold">ID</span>
                </div>
                <div class="relative group">
                    <img src="{{ asset('storage/' . $user->selfie_photo_path) }}" class="w-20 h-20 sm:w-24 sm:h-24 rounded-lg object-cover border border-slate-200 cursor-pointer group-hover:opacity-75 transition-all" onclick="openModal(this.src, 'Selfie')">
                    <span class="absolute bottom-1 right-1 bg-slate-900/60 text-[8px] text-white px-1 rounded font-bold">SELFIE</span>
                </div>
            </div>
            
            <div class="flex-1 flex flex-col justify-between">
                <div>
                    <!-- TASK 1: Format exactly as requested -->
                    <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight leading-tight mb-1">
                        {{ $user->last_name }}, {{ $user->first_name }}, {{ $user->middle_name }}, {{ $user->suffix }}
                    </h3>
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-3 flex flex-wrap gap-x-2 gap-y-1">
                        <span>{{ $user->sex }}</span>
                        <span class="text-slate-300">|</span>
                        <span>{{ $user->age }} YRS OLD</span>
                        <span class="text-slate-300">|</span>
                        <span>DOB: {{ $user->date_of_birth->format('M d, Y') }}</span>
                        <span class="text-slate-300">|</span>
                        <span class="text-slate-900 font-mono">{{ $user->contact_number }}</span>
                    </p>
                    <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-100 mb-4">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Address</p>
                        <p class="text-xs font-bold text-slate-700 uppercase">{{ $user->house_number }} {{ $user->purok_street }}</p>
                    </div>
                </div>

                <div class="flex gap-2">
                    <form action="{{ route('admin.approve_account', $user->id) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full bg-slate-900 text-white font-black text-[10px] uppercase tracking-widest py-2.5 rounded-lg hover:bg-slate-800 transition-all active:scale-95 shadow-sm">Approve</button>
                    </form>
                    <button type="button" onclick="openRejectModal('{{ $user->id }}', '{{ $user->first_name }} {{ $user->last_name }}')" class="flex-1 bg-red-50 text-red-600 border border-red-200 font-black text-[10px] uppercase tracking-widest py-2.5 rounded-lg hover:bg-red-100 transition-all active:scale-95">Reject</button>
                </div>
            </div>
        </div>
        @empty
        <p class="col-span-full text-slate-500 text-center py-10 font-bold italic">Walang pending registrations.</p>
        @endforelse
    </div>

    <!-- SUB-TAB CONTENT: APPROVED -->
    <div id="sub-approved" class="sub-tab-content hidden grid grid-cols-1 gap-4">
        @forelse($approvedAccounts as $user)
        <div class="bg-white p-4 border border-green-200 rounded-lg flex justify-between items-center border-l-4 border-l-green-500 shadow-sm">
            <div>
                <p class="font-bold text-slate-900 uppercase leading-none mb-1">{{ $user->last_name }}, {{ $user->first_name }}</p>
                <p class="text-[10px] text-slate-500 font-mono tracking-tight">{{ $user->contact_number }}</p>
            </div>
            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider">Verified</span>
        </div>
        @empty
        <p class="text-slate-500 text-center py-10 font-bold italic">Walang approved accounts.</p>
        @endforelse
    </div>

    <!-- SUB-TAB CONTENT: REJECTED / LOCKED -->
    <div id="sub-rejected" class="sub-tab-content hidden grid grid-cols-1 xl:grid-cols-2 gap-6">
        @forelse($rejectedAccounts as $user)
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 border-l-4 border-l-red-500">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight">{{ $user->last_name }}, {{ $user->first_name }}</h3>
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">{{ $user->contact_number }} | Attempts: {{ $user->rejection_count }}/5</p>
                </div>
                <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-[9px] font-black uppercase tracking-widest">Rejected</span>
            </div>
            
            <div class="bg-red-50 p-3 rounded-lg border border-red-100 mb-4">
                <p class="text-[9px] font-black text-red-400 uppercase tracking-widest mb-1">Reason for Rejection</p>
                <p class="text-xs font-bold text-red-700">{{ $user->rejection_reason }}</p>
            </div>

            <!-- TASK 1: Replace buttons with Delete Account -->
            <form action="#" method="POST" onsubmit="return confirm('Sigurado ka bang gusto mong burahin ang account na ito?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full bg-red-600 text-white font-black text-[10px] uppercase tracking-widest py-2.5 rounded-lg hover:bg-red-700 transition-all active:scale-95 shadow-md">
                    Delete Account
                </button>
            </form>
        </div>
        @empty
        <p class="col-span-full text-slate-500 text-center py-10 font-bold italic">Walang rejected accounts.</p>
        @endforelse
    </div>
</div>

<!-- ===================================== -->
<!-- TAB 2: QUEUE & PROCESSING MODULE -->
<!-- ===================================== -->
<div id="tab-queue" class="tab-content {{ session('active_tab') == 'queue' ? 'block' : 'hidden' }}">
    
    <!-- TASK 2: Horizontal sub-tab for Active vs Received -->
    <div class="flex gap-4 mb-6 border-b border-slate-200">
        <button onclick="showSubTab('queue-active', this)" class="sub-tab-btn px-6 py-3 font-black text-[11px] uppercase tracking-[0.2em] border-b-2 border-slate-900 text-slate-900 transition-all">
            Active Queue ({{ $activeQueue->count() }})
        </button>
        <button onclick="showSubTab('queue-received', this)" class="sub-tab-btn px-6 py-3 font-black text-[11px] uppercase tracking-[0.2em] border-b-2 border-transparent text-slate-400 hover:text-slate-600 transition-all">
            Received History
        </button>
    </div>

    <!-- ACTIVE QUEUE TABLE -->
    <div id="queue-active" class="sub-tab-content bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-[10px] uppercase tracking-[0.15em]">
                    <th class="p-4 font-black">Queue #</th>
                    <th class="p-4 font-black">Residente</th>
                    <th class="p-4 font-black">Dokumento</th>
                    <th class="p-4 font-black">Status</th>
                    <th class="p-4 font-black text-right">Aksyon</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($activeQueue as $queue)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="p-4 font-black text-slate-900 text-xl">{{ $queue->queue_number }}</td>
                    <td class="p-4">
                        <p class="font-bold text-slate-900 uppercase leading-none mb-1 text-sm">{{ $queue->user->last_name }}, {{ $queue->user->first_name }}</p>
                        <p class="text-[10px] text-slate-500 font-mono tracking-tight">{{ $queue->user->contact_number }}</p>
                    </td>
                    <td class="p-4 font-bold text-slate-700 text-xs uppercase">{{ $queue->documentType->name ?? 'N/A' }}</td>
                    <td class="p-4">
                        <!-- TASK 2: Status Colors -->
                        <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest shadow-sm
                            {{ $queue->status == 'pending' ? 'bg-yellow-100 text-yellow-700 border border-yellow-200' : '' }}
                            {{ $queue->status == 'processing' ? 'bg-blue-100 text-blue-700 border border-blue-200' : '' }}
                            {{ $queue->status == 'for_interview' ? 'bg-purple-100 text-purple-700 border border-purple-200' : '' }}
                            {{ $queue->status == 'released' ? 'bg-orange-100 text-orange-700 border border-orange-200' : '' }}
                        ">
                            {{ str_replace('_', ' ', $queue->status) }}
                        </span>
                    </td>
                    <td class="p-4 text-right">
                        <!-- TASK 2: Replace dropdown with Update Status button -->
                        <button type="button" onclick="openStatusModal('{{ $queue->id }}', '{{ $queue->status }}')" class="bg-slate-900 hover:bg-slate-800 text-white font-black text-[10px] uppercase tracking-widest px-4 py-2 rounded-lg transition-all active:scale-95 shadow-sm">
                            Update Status
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-12 text-center text-slate-400 font-bold italic">Walang aktibong nakapila.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- RECEIVED HISTORY TABLE -->
    <div id="queue-received" class="sub-tab-content hidden bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-[10px] uppercase tracking-[0.15em]">
                    <th class="p-4 font-black">Queue #</th>
                    <th class="p-4 font-black">Residente</th>
                    <th class="p-4 font-black">Dokumento</th>
                    <th class="p-4 font-black">Date Released</th>
                    <th class="p-4 font-black text-right">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($receivedQueue as $queue)
                <tr class="bg-slate-50/50">
                    <td class="p-4 font-black text-slate-400 text-xl">{{ $queue->queue_number }}</td>
                    <td class="p-4 opacity-75">
                        <p class="font-bold text-slate-900 uppercase leading-none mb-1 text-sm">{{ $queue->user->last_name }}, {{ $queue->user->first_name }}</p>
                        <p class="text-[10px] text-slate-500 font-mono">{{ $queue->user->contact_number }}</p>
                    </td>
                    <td class="p-4 font-bold text-slate-600 text-xs uppercase">{{ $queue->documentType->name ?? 'N/A' }}</td>
                    <td class="p-4 text-xs font-bold text-slate-500">{{ $queue->released_at ? $queue->released_at->format('M d, Y h:i A') : 'N/A' }}</td>
                    <td class="p-4 text-right">
                        <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border border-green-200">
                            Received
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-12 text-center text-slate-400 font-bold italic">Walang record ng release history.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ... other WIP tabs remain hidden ... -->
<div id="tab-walkin" class="tab-content hidden"><h2 class="text-xl font-bold text-slate-800 italic">Walk-in Requests Module (WIP)</h2></div>
<div id="tab-announcements" class="tab-content hidden"><h2 class="text-xl font-bold text-slate-800 italic">Announcements Module (WIP)</h2></div>
<div id="tab-audit" class="tab-content hidden"><h2 class="text-xl font-bold text-slate-800 italic">System Audit Logs (WIP)</h2></div>

<!-- IMAGE VIEWER MODAL (Existing) -->
<div id="imageModal" class="hidden fixed inset-0 z-[120] bg-slate-900/90 backdrop-blur-sm flex justify-center items-center p-4">
    <div class="relative max-w-4xl w-full bg-white rounded-xl shadow-2xl overflow-hidden">
        <div class="flex justify-between items-center p-4 border-b border-slate-200 bg-slate-50">
            <h3 id="modalTitle" class="text-lg font-black text-slate-900 uppercase tracking-tighter">Preview</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-red-600 font-bold text-2xl leading-none transition-all">&times;</button>
        </div>
        <div class="p-4 bg-slate-200 flex justify-center">
            <img id="modalImg" src="" class="max-h-[65vh] object-contain rounded shadow-lg">
        </div>
        <div class="p-4 border-t border-slate-200 bg-slate-50 text-right">
            <button onclick="closeModal()" class="bg-slate-900 text-white font-black text-[10px] uppercase tracking-widest py-3 px-10 rounded-lg hover:bg-slate-800 active:scale-95 transition-all">Isara</button>
        </div>
    </div>
</div>

<!-- TASK 2: STATUS CONFIRMATION MODAL -->
<div id="statusModal" class="hidden fixed inset-0 z-[110] bg-slate-900/80 backdrop-blur-sm flex justify-center items-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden p-6 transform transition-all border border-slate-100">
        <div class="flex flex-col items-center text-center mb-6">
            <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
            </div>
            <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight">Update Status?</h3>
            <p class="text-sm text-slate-500 font-medium px-4">Pumili ng bagong status para sa request na ito. Maaari itong mag-trigger ng SMS notification.</p>
        </div>

        <form id="statusForm" method="POST">
            @csrf
            <input type="hidden" name="status" id="targetStatusInput">
            <div class="grid grid-cols-2 gap-2 mb-6" id="statusOptions">
                <button type="button" onclick="confirmStatus('processing')" class="p-3 border-2 border-blue-100 bg-blue-50/30 rounded-xl text-[10px] font-black uppercase tracking-widest text-blue-700 hover:bg-blue-100 transition-all">Processing</button>
                <button type="button" onclick="confirmStatus('for_interview')" class="p-3 border-2 border-purple-100 bg-purple-50/30 rounded-xl text-[10px] font-black uppercase tracking-widest text-purple-700 hover:bg-purple-100 transition-all">Interview</button>
                <button type="button" onclick="confirmStatus('released')" class="p-3 border-2 border-orange-100 bg-orange-50/30 rounded-xl text-[10px] font-black uppercase tracking-widest text-orange-700 hover:bg-orange-100 transition-all">Released</button>
                <button type="button" onclick="confirmStatus('received')" class="p-3 border-2 border-green-100 bg-green-50/30 rounded-xl text-[10px] font-black uppercase tracking-widest text-green-700 hover:bg-green-100 transition-all">Received</button>
            </div>
            
            <div class="flex flex-col gap-2">
                <button type="button" onclick="closeStatusModal()" class="w-full text-slate-400 font-black text-[10px] uppercase tracking-widest py-2 hover:text-slate-600 transition-all">Kanselahin</button>
            </div>
        </form>
    </div>
</div>

<!-- REJECT REASON MODAL (Existing) -->
<div id="rejectModal" class="hidden fixed inset-0 z-[110] bg-slate-900/80 backdrop-blur-sm justify-center items-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all border border-red-100">
        <div class="bg-red-50 border-b border-red-100 p-4 flex justify-between items-center text-red-700">
            <h3 class="text-lg font-black uppercase tracking-tight flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                Reject Registration
            </h3>
            <button onclick="closeRejectModal()" class="text-red-300 hover:text-red-700 font-bold text-2xl transition-all">&times;</button>
        </div>

        <form id="rejectForm" method="POST" action="">
            @csrf
            <div class="p-6">
                <p class="text-sm text-slate-600 font-medium mb-4">Ita-type mo ang rason kung bakit rejected si <strong id="rejectUserName" class="text-slate-900"></strong>. Ipapadala ito via SMS.</p>
                
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Rason ng Rejection</label>
                <input type="text" name="rejection_reason" maxlength="60" required placeholder="Hal: Malabo ang ID, paki-upload ng maayos." class="w-full border-2 border-slate-100 p-3 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none text-slate-900 font-bold placeholder-slate-300 transition-all">
                <p class="text-[9px] text-slate-400 mt-2 text-right font-black italic">Limit: 60 characters</p>
            </div>

            <div class="bg-slate-50 p-4 border-t border-slate-100 flex gap-3">
                <button type="button" onclick="closeRejectModal()" class="flex-1 bg-white border border-slate-200 text-slate-500 font-black text-[10px] uppercase tracking-widest py-3 rounded-xl hover:bg-slate-100 transition-all">Kanselahin</button>
                <button type="submit" class="flex-1 bg-red-600 text-white font-black text-[10px] uppercase tracking-widest py-3 rounded-xl hover:bg-red-700 transition-all active:scale-95 shadow-md">I-Submit Reject</button>
            </div>
        </form>
    </div>
</div>

@endsection
