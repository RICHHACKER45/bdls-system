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

    <!-- SUB-TAB CONTENT: APPROVED (TASK 3: Expandable Accordion) -->
    <div id="sub-approved" class="sub-tab-content hidden space-y-3">
        @forelse($approvedAccounts as $user)
        <details class="group bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
            <summary class="flex justify-between items-center p-4 cursor-pointer hover:bg-slate-50 list-none transition-all">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                    <span class="font-black text-slate-900 uppercase text-sm">{{ $user->last_name }}, {{ $user->first_name }}</span>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $user->contact_number }}</span>
                    <svg class="w-5 h-5 text-slate-400 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </div>
            </summary>
            <div class="p-5 border-t border-slate-50 bg-slate-50/30 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 text-xs">
                <div>
                    <p class="text-slate-400 font-black uppercase tracking-widest mb-1">Contact Details</p>
                    <p class="font-bold text-slate-900">{{ $user->contact_number }}</p>
                    <p class="font-medium text-slate-500">{{ $user->email ?? 'No email provided' }}</p>
                </div>
                <div>
                    <p class="text-slate-400 font-black uppercase tracking-widest mb-1">Personal Info</p>
                    <p class="font-bold text-slate-900 uppercase">{{ $user->sex }} | {{ $user->age }} YRS OLD</p>
                    <p class="font-medium text-slate-500 italic">DOB: {{ $user->date_of_birth->format('M d, Y') }}</p>
                </div>
                <div>
                    <p class="text-slate-400 font-black uppercase tracking-widest mb-1">Address</p>
                    <p class="font-bold text-slate-900 uppercase">{{ $user->house_number }} {{ $user->purok_street }}</p>
                </div>
                <div class="lg:col-span-3 pt-2 border-t border-slate-100 flex justify-between items-center">
                    <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">Terms Accepted: {{ $user->terms_accepted_at ? $user->terms_accepted_at->format('M d, Y h:i A') : 'N/A' }}</p>
                    <div class="flex gap-2">
                        <img src="{{ asset('storage/' . $user->id_photo_path) }}" class="w-8 h-8 rounded border border-slate-200 object-cover cursor-pointer" onclick="openModal(this.src, 'ID')">
                        <img src="{{ asset('storage/' . $user->selfie_photo_path) }}" class="w-8 h-8 rounded border border-slate-200 object-cover cursor-pointer" onclick="openModal(this.src, 'Selfie')">
                    </div>
                </div>
            </div>
        </details>
        @empty
        <p class="text-slate-500 text-center py-10 font-bold italic">Walang approved accounts.</p>
        @endforelse
    </div>

    <!-- SUB-TAB CONTENT: REJECTED / LOCKED -->
    <div id="sub-rejected" class="sub-tab-content hidden grid grid-cols-1 xl:grid-cols-2 gap-6">
        @forelse($rejectedAccounts as $user)
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 border-l-4 border-l-red-500 flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight leading-tight">{{ $user->last_name }}, {{ $user->first_name }}</h3>
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">{{ $user->contact_number }} | Attempts: {{ $user->rejection_count }}/5</p>
                    </div>
                    <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-[9px] font-black uppercase tracking-widest shadow-sm">Rejected</span>
                </div>
                
                <div class="bg-red-50 p-3 rounded-lg border border-red-100 mb-4">
                    <p class="text-[9px] font-black text-red-400 uppercase tracking-widest mb-1 italic">Reason for Rejection</p>
                    <p class="text-xs font-bold text-red-700 leading-snug">{{ $user->rejection_reason }}</p>
                </div>
            </div>

            <!-- TASK 1: Replace buttons with Delete Account Modal Trigger -->
            <button type="button" onclick="openDeleteModal('{{ $user->id }}')" class="w-full bg-red-600 text-white font-black text-[10px] uppercase tracking-widest py-2.5 rounded-lg hover:bg-red-700 transition-all active:scale-95 shadow-md">
                Delete Account
            </button>
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
            Received History ({{ $receivedQueue->count() }})
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
    @php
        // THE LARAVEL WAY FIX: I-force sa lowercase ang pagbasa para hindi maguluhan ang logic
        $rawStatus = strtolower($queue->status);
        $nextStatus = ''; $btnLabel = '';
        
        // Mga Dokumento na kailangan ng "Probing Interview" (Face-to-Face)
        $interviewDocs = [1-8];
        
        if($rawStatus == 'pending') { $nextStatus = 'processing'; $btnLabel = 'Process Request'; }
        elseif($rawStatus == 'processing') {
            if(in_array($queue->document_type_id, $interviewDocs)) { 
                $nextStatus = 'for_interview'; $btnLabel = 'Set for Interview'; 
            } else { 
                $nextStatus = 'released'; $btnLabel = 'Release Document'; 
            }
        }
        elseif($rawStatus == 'for_interview') { $nextStatus = 'released'; $btnLabel = 'Release Document'; }
        elseif($rawStatus == 'released') { $nextStatus = 'received'; $btnLabel = 'Mark as Received'; }
    @endphp
    <tr class="hover:bg-slate-50 transition-colors">
        <td class="p-4 font-black text-slate-900 text-xl tracking-tighter">{{ $queue->queue_number }}</td>
        <td class="p-4">
            <p class="font-bold text-slate-900 uppercase leading-none mb-1 text-sm">{{ $queue->user->last_name }}, {{ $queue->user->first_name }}</p>
            <p class="text-[10px] text-slate-500 font-mono tracking-tight">{{ $queue->user->contact_number }}</p>
        </td>
        <td class="p-4 font-bold text-slate-700 text-xs uppercase">{{ $queue->documentType->name ?? 'N/A' }}</td>
        <td class="p-4">
            <!-- FIXED STATUS COLORS -->
            <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest shadow-sm
                {{ $rawStatus == 'pending' ? 'bg-yellow-100 text-yellow-700 border border-yellow-200' : '' }}
                {{ $rawStatus == 'processing' ? 'bg-blue-100 text-blue-700 border border-blue-200' : '' }}
                {{ $rawStatus == 'for_interview' ? 'bg-purple-100 text-purple-700 border border-purple-200' : '' }}
                {{ $rawStatus == 'released' ? 'bg-orange-100 text-orange-700 border border-orange-200' : '' }}
            ">
                {{ str_replace('_', ' ', $rawStatus) }}
            </span>
        </td>
        <td class="p-4 text-right">
            <!-- FIXED ONE-WAY BUTTON -->
            @if($btnLabel !== '')
            <button type="button" onclick="openStatusModal('{{ $queue->id }}', '{{ $nextStatus }}', '{{ $btnLabel }}')" class="bg-slate-900 hover:bg-slate-800 text-white font-black text-[10px] uppercase tracking-widest px-4 py-2 rounded-lg transition-all active:scale-95 shadow-sm">
                {{ $btnLabel }}
            </button>
            @else
            <span class="text-xs text-slate-400 font-bold italic">No Action</span>
            @endif
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
        <!-- THE FIX: Logbook Print Header -->
        <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-widest">Released Records</h3>
            <a href="{{ route('admin.queue.print_logbook') }}" target="_blank" class="bg-slate-900 hover:bg-slate-800 text-white font-black text-[10px] uppercase tracking-widest px-4 py-2 rounded-lg transition-all active:scale-95 shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Print Logbook
            </a>
        </div>
    
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
                    <td class="p-4 font-black text-slate-400 text-xl tracking-tighter">{{ $queue->queue_number }}</td>
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


<!-- ===================================== -->
<!-- TAB 3: WALK-IN REQUESTS MODULE        -->
<!-- ===================================== -->
<div id="tab-walkin" class="tab-content {{ session('active_tab') == 'walkin' ? 'block' : 'hidden' }}">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 md:p-8 max-w-3xl mx-auto">
        <div class="text-center mb-6">
            <div class="w-14 h-14 bg-slate-100 text-slate-900 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight uppercase">Walk-in Search</h2>
            <p class="text-sm text-slate-500 font-medium mt-1">Hanapin ang contact number bago gumawa ng request.</p>
        </div>

        <form action="{{ route('admin.walkin.search') }}" method="POST" class="flex flex-col sm:flex-row gap-3">
            @csrf
            <div class="flex-1">
                <!-- THE UI FIX: Binawasan ang tracking para hindi maghiwalay ang numbers at ginawang text-xl -->
                <input type="text" name="contact_number" value="{{ session('walkin_search_number') }}" required placeholder="09XXXXXXXXX" oninput="this.value = this.value.replace(/[^0-9]/g, '');" maxlength="11" class="w-full px-5 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-slate-900 outline-none transition-all font-mono font-bold tracking-widest text-center sm:text-left text-xl">
            </div>
            <button type="submit" class="shrink-0 whitespace-nowrap bg-slate-900 hover:bg-slate-800 text-white font-black text-xs uppercase tracking-widest px-8 py-3 rounded-lg transition-all active:scale-95 shadow-sm">
                I-Search
            </button>
        </form>

        <!-- SEARCH RESULTS & FORMS -->
        @if(session('walkin_searched'))
            <div class="mt-8 pt-6 border-t border-slate-100">
                @if(session('walkin_user'))
                    <!-- FOUND: Existing Resident Form -->
                    <form action="{{ route('admin.walkin.store') }}" method="POST" class="bg-green-50 border border-green-200 p-5 rounded-xl">
                        @csrf
                        <input type="hidden" name="contact_number" value="{{ session('walkin_search_number') }}">
                        <input type="hidden" name="is_new_user" value="0">
                        
                        <div class="mb-4">
                            <p class="text-[10px] font-black text-green-600 uppercase tracking-widest mb-1">Record Found</p>
                            <p class="font-bold text-slate-900 uppercase text-lg leading-none mb-1">{{ session('walkin_user')->last_name }}, {{ session('walkin_user')->first_name }}</p>
                            <p class="text-xs text-slate-500 font-bold">{{ session('walkin_user')->sex }} | {{ session('walkin_user')->age }} YRS OLD</p>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3">
                            <select name="document_type_id" required class="flex-1 min-w-0 px-4 py-3 rounded-lg border border-green-300 focus:ring-2 focus:ring-green-600 outline-none text-sm font-bold text-slate-700 bg-white">
                                <option value="">-- Piliin ang Dokumento --</option>
                                @foreach($documents as $doc)
                                    <option value="{{ $doc->id }}">{{ $doc->name }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="purpose" placeholder="Layunin (Purpose)" required class="flex-1 min-w-0 px-4 py-3 rounded-lg border border-green-300 focus:ring-2 focus:ring-green-600 outline-none text-sm font-bold text-slate-700 bg-white">
                            <!-- THE UI FIX: Nagdagdag ng shrink-0 at whitespace-nowrap para hindi mapisa ang button -->
                            <button type="submit" class="shrink-0 whitespace-nowrap bg-green-600 hover:bg-green-700 text-white font-black text-[10px] uppercase tracking-widest px-6 py-3 rounded-lg shadow-sm transition-all active:scale-95">Create Request</button>
                        </div>
                    </form>
                @else
                    <!-- NOT FOUND: New Shadow Profile Form (Streamlined for fast typing) -->
                    <form action="{{ route('admin.walkin.store') }}" method="POST" class="bg-amber-50 border border-amber-200 p-5 md:p-6 rounded-xl">
                        @csrf
                        <input type="hidden" name="contact_number" value="{{ session('walkin_search_number') }}">
                        <input type="hidden" name="is_new_user" value="1">
                        
                        <p class="text-[10px] font-black text-amber-600 uppercase tracking-widest mb-3">Walang Record: I-rehistro bilang Walk-in</p>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                            <input type="text" name="first_name" placeholder="First Name *" required class="w-full px-4 py-2.5 rounded-lg border border-amber-300 focus:ring-2 focus:ring-amber-600 outline-none text-sm font-bold bg-white">
                            <input type="text" name="last_name" placeholder="Last Name *" required class="w-full px-4 py-2.5 rounded-lg border border-amber-300 focus:ring-2 focus:ring-amber-600 outline-none text-sm font-bold bg-white">
                            <select name="sex" required class="w-full px-4 py-2.5 rounded-lg border border-amber-300 focus:ring-2 focus:ring-amber-600 outline-none text-sm font-bold bg-white">
                                <option value="">-- Kasarian * --</option>
                                <option value="Male">Lalaki</option>
                                <option value="Female">Babae</option>
                            </select>
                            <div class="flex items-center bg-white border border-amber-300 rounded-lg px-3 focus-within:ring-2 focus-within:ring-amber-600">
                                <span class="text-xs font-bold text-slate-400 mr-2 uppercase tracking-widest">DOB*</span>
                                <input type="date" name="date_of_birth" required class="w-full py-2.5 outline-none text-sm font-bold bg-transparent">
                            </div>
                            <input type="text" name="house_number" placeholder="House No. *" required class="w-full px-4 py-2.5 rounded-lg border border-amber-300 focus:ring-2 focus:ring-amber-600 outline-none text-sm font-bold bg-white">
                            <input type="text" name="purok_street" placeholder="Purok/Street *" required class="w-full px-4 py-2.5 rounded-lg border border-amber-300 focus:ring-2 focus:ring-amber-600 outline-none text-sm font-bold bg-white">
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3 border-t border-amber-200 pt-4 mt-2">
                            <select name="document_type_id" required class="flex-1 min-w-0 px-4 py-3 rounded-lg border border-amber-300 focus:ring-2 focus:ring-amber-600 outline-none text-sm font-bold text-slate-700 bg-white">
                                <option value="">-- Piliin ang Dokumento --</option>
                                @foreach($documents as $doc)
                                    <option value="{{ $doc->id }}">{{ $doc->name }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="purpose" placeholder="Layunin (Purpose)" required class="flex-1 min-w-0 px-4 py-3 rounded-lg border border-amber-300 focus:ring-2 focus:ring-amber-600 outline-none text-sm font-bold text-slate-700 bg-white">
                            <!-- THE UI FIX: Nagdagdag ng shrink-0 at whitespace-nowrap -->
                            <button type="submit" class="shrink-0 whitespace-nowrap bg-amber-600 hover:bg-amber-700 text-white font-black text-[10px] uppercase tracking-widest px-6 py-3 rounded-lg shadow-sm transition-all active:scale-95">Register & Create</button>
                        </div>
                    </form>
                @endif
            </div>
        @endif
    </div>
</div>

<!-- ===================================== -->
<!-- TAB 4: ANNOUNCEMENTS MODULE           -->
<!-- ===================================== -->
<div id="tab-announcements" class="tab-content {{ session('active_tab') == 'announcements' ? 'block' : 'hidden' }}">
    <div class="max-w-3xl mx-auto mt-8">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 md:p-8">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 bg-slate-900 text-white rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                </div>
                <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight">Broadcast Announcement</h2>
            </div>
            
            <p class="text-sm text-slate-500 font-medium mb-6 pb-6 border-b border-slate-100">Magpadala ng text blast sa lahat ng verified residents ng Barangay. <br><span class="text-amber-600 font-bold">Ayon sa NTC: Bawal ang links at bawal mag-send mula 9:00 PM hanggang 7:00 AM.</span></p>

            @php
                $currentHour = (int) now()->format('H');
                $isCurfew = ($currentHour >= 21 || $currentHour < 7);
            @endphp

            @if($isCurfew || $errors->has('curfew'))
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-xl shadow-sm">
                    <div class="flex items-center gap-2 text-red-700 font-black mb-1 uppercase tracking-widest text-xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        NTC SMS Curfew Active
                    </div>
                    <p class="text-sm font-bold text-red-600">Pansamantalang naka-disable ang Broadcast (9:00 PM - 7:00 AM) upang sumunod sa Anti-Spam rules. Subukan muli bukas.</p>
                </div>
            @endif

            <form action="{{ route('admin.announcements.broadcast') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Mensahe (Message Body) <span class="text-red-500">*</span></label>
                    <textarea 
                        name="message_body" 
                        id="announcement_text" 
                        rows="5" 
                        required 
                        {{ $isCurfew ? 'disabled' : '' }}
                        placeholder="{{ $isCurfew ? 'Naka-disable ang pag-type tuwing curfew...' : 'I-type ang iyong anunsyo dito...' }}" 
                        class="w-full px-4 py-3 rounded-xl border @error('message_body') border-red-500 bg-red-50 @else border-slate-300 bg-slate-50 @enderror focus:ring-2 focus:ring-slate-900 outline-none transition-all font-medium text-slate-800 resize-none {{ $isCurfew ? 'opacity-60 cursor-not-allowed bg-slate-100' : '' }}"
                        oninput="updateCharCount(this)"
                    >{{ old('message_body') }}</textarea>
                    
                    @error('message_body') 
                        <p class="text-red-600 text-xs mt-2 font-bold">{{ $message }}</p> 
                    @enderror

                    <div class="flex justify-between items-start mt-2">
                        <p id="char_warning" class="text-xs font-bold text-red-600 hidden mt-1">⚠️ Bawal mag-send ng links (http/www).</p>
                        <p id="char_counter" class="text-xs font-bold text-slate-500 ml-auto mt-1">Characters: 0/160 (Est. 1 Credit per user)</p>
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" id="broadcastBtn" {{ $isCurfew ? 'disabled' : '' }} class="bg-slate-900 hover:bg-slate-800 text-white font-black text-xs uppercase tracking-widest px-8 py-3.5 rounded-xl transition-all active:scale-95 shadow-md flex items-center gap-2 {{ $isCurfew ? 'opacity-50 cursor-not-allowed hover:bg-slate-900 active:scale-100' : '' }}">
                        Send Broadcast
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function updateCharCount(textarea) {
        const counter = document.getElementById('char_counter');
        const warning = document.getElementById('char_warning');
        const btn = document.getElementById('broadcastBtn');
        let length = textarea.value.length;

        // Front-end Link Blocker UI
        if(/(http|https|www\.)/i.test(textarea.value)) {
            warning.classList.remove('hidden');
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');
            textarea.classList.add('border-red-500', 'ring-1', 'ring-red-500');
        } else {
            warning.classList.add('hidden');
            btn.disabled = false;
            btn.classList.remove('opacity-50', 'cursor-not-allowed');
            textarea.classList.remove('border-red-500', 'ring-1', 'ring-red-500');
        }

        // Character counting logic based on NTC Guidelines
        let credits = 1;
        if(length > 160) {
            credits = Math.ceil(length / 153);
        }

        counter.innerText = `Characters: ${length} (Est. ${credits} Credit/s per user)`;
        
        if(length > 160) {
            counter.classList.add('text-amber-600');
            counter.classList.remove('text-slate-500');
        } else {
            counter.classList.remove('text-amber-600');
            counter.classList.add('text-slate-500');
        }
    }
</script>

<!-- ===================================== -->
<!-- TAB 5: REPORTS & LOGS (Process 5.0 & 6.0) -->
<!-- ===================================== -->
<div id="tab-audit" class="tab-content {{ session('active_tab') == 'audit' ? 'block' : 'hidden' }}">
    
    <!-- SUB-TAB BUTTONS -->
    <div class="flex gap-2 mb-6 overflow-x-auto pb-2 mt-6">
        <button onclick="showSubTab('sub-audit-trail', this)" class="sub-tab-btn px-5 py-2 rounded-full bg-slate-900 text-white font-bold text-sm whitespace-nowrap transition-all border border-slate-900">
            System Audit Trail
        </button>
        <button onclick="showSubTab('sub-notif-history', this)" class="sub-tab-btn px-5 py-2 rounded-full bg-slate-200 text-slate-700 hover:bg-slate-300 font-bold text-sm whitespace-nowrap transition-all border border-transparent">
            Notification History
        </button>
        <button onclick="showSubTab('sub-generate-pdf', this)" class="sub-tab-btn px-5 py-2 rounded-full bg-slate-200 text-slate-700 hover:bg-slate-300 font-bold text-sm whitespace-nowrap transition-all border border-transparent">
            Generate Analytics
        </button>
    </div>

    <!-- 1. SYSTEM AUDIT TRAIL -->
    <div id="sub-audit-trail" class="sub-tab-content block bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-200 bg-slate-50">
            <h2 class="text-xl font-black text-slate-900 uppercase tracking-tight">System Audit Logs</h2>
        </div>
        <div class="overflow-x-auto max-h-[600px] overflow-y-auto">
            <table class="w-full text-left border-collapse relative">
                <thead class="sticky top-0 z-10">
                    <tr class="bg-slate-100 border-b border-slate-200 text-slate-500 text-[10px] uppercase tracking-[0.15em]">
                        <th class="p-4 font-black">Petsa & Oras</th>
                        <th class="p-4 font-black">Admin</th>
                        <th class="p-4 font-black">Aksyon</th>
                        <th class="p-4 font-black w-1/2">Detalye</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($auditLogs as $log)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 font-bold text-slate-600 text-xs">{{ \Carbon\Carbon::parse($log->created_at)->format('M d, Y h:i A') }}</td>
                        <td class="p-4 font-bold text-slate-900 uppercase text-xs">{{ $log->admin->first_name ?? 'System' }} {{ $log->admin->last_name ?? '' }}</td>
                        <td class="p-4">
                            <span class="px-2 py-1 bg-slate-900 text-white rounded text-[9px] font-black uppercase tracking-widest shadow-sm">{{ $log->action }}</span>
                        </td>
                        <td class="p-4 text-xs font-medium text-slate-600">{{ $log->description }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="p-12 text-center text-slate-400 font-bold italic">Wala pang naitalang galaw sa system.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 2. NOTIFICATION HISTORY -->
    <div id="sub-notif-history" class="sub-tab-content hidden bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-200 bg-slate-50">
            <h2 class="text-xl font-black text-slate-900 uppercase tracking-tight">Notification History</h2>
        </div>
        <div class="overflow-x-auto max-h-[600px] overflow-y-auto">
            <table class="w-full text-left border-collapse relative">
                <thead class="sticky top-0 z-10">
                    <tr class="bg-slate-100 border-b border-slate-200 text-slate-500 text-[10px] uppercase tracking-[0.15em]">
                        <th class="p-4 font-black">Petsa & Oras</th>
                        <th class="p-4 font-black">Residente</th>
                        <th class="p-4 font-black">Channel</th>
                        <th class="p-4 font-black w-2/5">Mensahe</th>
                        <th class="p-4 font-black text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($notificationLogs as $notif)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 font-bold text-slate-600 text-xs">{{ \Carbon\Carbon::parse($notif->created_at)->format('M d, Y h:i A') }}</td>
                        <td class="p-4">
                            <p class="font-bold text-slate-900 text-xs uppercase">{{ $notif->user->first_name ?? 'N/A' }} {{ $notif->user->last_name ?? '' }}</p>
                            <p class="text-[9px] font-mono text-slate-500">{{ $notif->recipient_contact }}</p>
                        </td>
                        <td class="p-4">
                            @if(strtolower($notif->channel) == 'sms')
                                <span class="px-2 py-1 bg-blue-100 text-blue-700 border border-blue-200 rounded text-[9px] font-black uppercase tracking-widest shadow-sm">SMS</span>
                            @else
                                <span class="px-2 py-1 bg-purple-100 text-purple-700 border border-purple-200 rounded text-[9px] font-black uppercase tracking-widest shadow-sm">EMAIL</span>
                            @endif
                        </td>
                        <td class="p-4 text-[11px] font-medium text-slate-600 line-clamp-2" title="{{ $notif->message_content }}">{{ $notif->message_content }}</td>
                        <td class="p-4 text-right">
                            @if(str_contains(strtolower($notif->status), 'sent'))
                                <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-[9px] font-black uppercase tracking-widest">{{ $notif->status }}</span>
                            @else
                                <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-[9px] font-black uppercase tracking-widest" title="{{ $notif->provider_response }}">{{ $notif->status }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="p-12 text-center text-slate-400 font-bold italic">Walang record ng notifications.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 3. GENERATE ANALYTICS (CUSTOM DROPDOWNS & MODAL TARGET) -->
    <div id="sub-generate-pdf" class="sub-tab-content hidden bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-200 bg-slate-50">
            <h2 class="text-xl font-black text-slate-900 uppercase tracking-tight">Generate System Analytics</h2>
        </div>
        <div class="p-8 max-w-2xl mx-auto my-8">
            <!-- PANSININ ANG TARGET: Tumuturo ito sa pangalan ng iframe sa loob ng modal natin -->
            <form action="{{ route('admin.reports.generate') }}" method="POST" target="pdfViewerFrame" onsubmit="openPdfModal()" class="flex flex-col gap-6">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Piliin ang Buwan <span class="text-red-500">*</span></label>
                        <select name="report_month" required class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-slate-900 outline-none font-bold text-slate-700 bg-white cursor-pointer">
                            <option value="all">Buong Taon (All Months)</option>
                            <option value="01">January</option>
                            <option value="02">February</option>
                            <option value="03" selected>March</option>
                            <option value="04">April</option>
                            <option value="05">May</option>
                            <option value="06">June</option>
                            <option value="07">July</option>
                            <option value="08">August</option>
                            <option value="09">September</option>
                            <option value="10">October</option>
                            <option value="11">November</option>
                            <option value="12">December</option>
                        </select>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Piliin ang Taon <span class="text-red-500">*</span></label>
                        <select name="report_year" required class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-slate-900 outline-none font-bold text-slate-700 bg-white cursor-pointer">
                            @for($y = date('Y'); $y >= 2024; $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                
                <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-black text-xs uppercase tracking-widest py-4 rounded-xl transition-all active:scale-95 shadow-md flex justify-center items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    Tingnan ang Analytics (In-App)
                </button>
            </form>
        </div>
    </div>
</div>

<!-- ===================================== -->
<!-- IN-APP PDF VIEWER MODAL (SPA Illusion) -->
<!-- ===================================== -->
<div id="pdfModal" class="hidden fixed inset-0 z-[200] bg-slate-900/90 backdrop-blur-sm justify-center items-center p-4 sm:p-8 transition-opacity">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl h-[90vh] flex flex-col overflow-hidden border border-slate-200">
        <!-- Modal Header -->
        <div class="p-4 border-b border-slate-200 flex justify-between items-center bg-slate-50">
            <h2 class="text-lg font-black text-slate-900 uppercase tracking-tight flex items-center gap-2">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                System Analytics Report
            </h2>
            <button onclick="closePdfModal()" class="text-slate-400 hover:text-red-600 font-bold text-3xl leading-none transition-all">&times;</button>
        </div>
        <!-- Modal Body: IFRAME -->
        <div class="flex-1 w-full bg-slate-200 relative">
            <!-- Loading spinner -->
            <div class="absolute inset-0 flex items-center justify-center -z-10">
                <svg class="w-10 h-10 text-slate-400 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            </div>
            <iframe name="pdfViewerFrame" id="pdfViewerFrame" onload="hideGlobalLoader()" class="w-full h-full bg-white z-10 relative"></iframe>
        </div>
        <!-- Modal Footer: WITH DOWNLOAD BUTTON -->
        <div class="p-4 border-t border-slate-200 bg-slate-50 flex justify-between items-center">
            <button onclick="downloadPdfNow()" class="bg-red-600 hover:bg-red-700 text-white font-black text-[10px] uppercase tracking-widest py-3 px-6 rounded-xl transition-all active:scale-95 flex items-center gap-2 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                I-Download ang PDF
            </button>
            <button onclick="closePdfModal()" class="bg-slate-900 hover:bg-slate-800 text-white font-black text-[10px] uppercase tracking-widest py-3 px-8 rounded-xl transition-all active:scale-95 shadow-sm">Isara</button>
        </div>
    </div>
</div>

<script>
    function openPdfModal() {
        const modal = document.getElementById('pdfModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closePdfModal() {
        const modal = document.getElementById('pdfModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = 'auto';
        document.getElementById('pdfViewerFrame').src = 'about:blank';
    }

    function hideGlobalLoader() {
        const loader = document.getElementById('global-loader');
        if (loader) {
            loader.classList.add('hidden');
            loader.classList.remove('flex');
        }
    }

    // Gagawa ng invisible form para mag-force download ng PDF
    function downloadPdfNow() {
        const month = document.querySelector('select[name="report_month"]').value;
        const year = document.querySelector('select[name="report_year"]').value;

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = "{{ route('admin.reports.generate') }}";
        form.target = "_blank"; // Magda-download ito sa bagong tab

        const csrf = document.createElement('input');
        csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = "{{ csrf_token() }}";
        form.appendChild(csrf);

        const mInput = document.createElement('input');
        mInput.type = 'hidden'; mInput.name = 'report_month'; mInput.value = month;
        form.appendChild(mInput);

        const yInput = document.createElement('input');
        yInput.type = 'hidden'; yInput.name = 'report_year'; yInput.value = year;
        form.appendChild(yInput);

        const dlInput = document.createElement('input');
        dlInput.type = 'hidden'; dlInput.name = 'is_download'; dlInput.value = '1';
        form.appendChild(dlInput);

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }
</script>

<script>
    // JS Logic para sa In-App PDF Modal (SPA Illusion)
    function openPdfModal() {
        const modal = document.getElementById('pdfModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
        // Hahayaan nating lumabas ang global loader (z-[2]) dahil automatic itong tini-trigger ng form submit.
    }

    function closePdfModal() {
        const modal = document.getElementById('pdfModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = 'auto';
        // Clear the iframe para hindi naiipon ang memory at mag-reset
        document.getElementById('pdfViewerFrame').src = 'about:blank';
    }

    // THE FIX 3: Ang function na papatay sa infinite loop ng loader
    function hideGlobalLoader() {
        const loader = document.getElementById('global-loader');
        if (loader) {
            loader.classList.add('hidden');
            loader.classList.remove('flex');
        }
    }
</script>

<!-- TASK 2: UPDATED STATUS CONFIRMATION MODAL -->
<div id="statusModal" class="hidden fixed inset-0 z-[110] bg-slate-900/80 backdrop-blur-sm flex justify-center items-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden p-6 transform transition-all border border-slate-100">
        <div class="flex flex-col items-center text-center mb-6">
            <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4 text-slate-900">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
            </div>
            <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight">Move to <span id="nextStatusLabel" class="text-blue-600">...</span>?</h3>
            <p class="text-sm text-slate-500 font-medium px-4 mt-2">Sigurado ka bang gusto mong i-update ang status ng request na ito?</p>
        </div>

        <form id="statusForm" method="POST">
            @csrf
            <input type="hidden" name="status" id="targetStatusInput">
            <div class="flex flex-col gap-3">
                <button type="submit" class="w-full bg-slate-900 text-white font-black text-xs uppercase tracking-widest py-3 rounded-xl hover:bg-slate-800 transition-all active:scale-95 shadow-md">Confirm Update</button>
                <button type="button" onclick="closeStatusModal()" class="w-full text-slate-400 font-black text-[10px] uppercase tracking-widest py-2 hover:text-slate-600 transition-all">Kanselahin</button>
            </div>
        </form>
    </div>
</div>

<!-- TASK 1: DELETE ACCOUNT MODAL -->
<div id="deleteModal" class="hidden fixed inset-0 z-[110] bg-slate-900/80 backdrop-blur-sm flex justify-center items-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden p-6 transform transition-all border border-red-100">
        <div class="flex flex-col items-center text-center mb-6">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4 text-red-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </div>
            <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight">Delete Account?</h3>
            <p class="text-sm text-slate-500 font-medium px-4 mt-2">Warning: Ang lahat ng data pati litrato ay permanenteng mawawala. Hindi na ito maibabalik.</p>
        </div>

        <form id="deleteForm" method="POST">
            @csrf
            @method('DELETE')
            <div class="flex flex-col gap-3">
                <button type="submit" class="w-full bg-red-600 text-white font-black text-xs uppercase tracking-widest py-3 rounded-xl hover:bg-red-700 transition-all active:scale-95 shadow-md">Delete Permanently</button>
                <button type="button" onclick="closeDeleteModal()" class="w-full text-slate-400 font-black text-[10px] uppercase tracking-widest py-2 hover:text-slate-600 transition-all">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- IMAGE VIEWER MODAL -->
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

<!-- REJECT REASON MODAL -->
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
