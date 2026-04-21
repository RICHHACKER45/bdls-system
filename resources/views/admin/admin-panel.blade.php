@extends ('admin.layouts.admin')

@section ('content')
    <!-- ===================================== -->
    <!-- TAB 1: PENDING REGISTRATIONS & ACCOUNTS -->
    <!-- ===================================== -->
    <div id="tab-pending" class="tab-content {{ session('active_tab', 'pending') == 'pending' ? 'block' : 'hidden' }}">
        <!-- TASK 1: Fix mobile search bar horizontal scroll issue -->
        <div class="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <form method="GET" action="{{ route('admin.dashboard') }}" class="flex w-full min-w-0 flex-wrap gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Maghanap ng pangalan o number..." class="min-w-[200px] flex-1 rounded-lg border border-slate-300 px-4 py-2 outline-none focus:ring-2 focus:ring-slate-900 focus:outline-none" />
                <select name="sort" onchange="this.form.submit()" class="rounded-lg border border-slate-300 bg-slate-50 px-4 py-2 focus:outline-none">
                    <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Pinakabago</option>
                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Pinakaluma</option>
                </select>
                <button type="submit" class="shrink-0 rounded-lg bg-slate-900 px-6 py-2 font-bold text-white transition-all hover:bg-slate-800 active:scale-95">Search</button>
            </form>
        </div>

        <div class="mb-6 flex gap-2 overflow-x-auto pb-2">
            <button onclick="showSubTab('sub-pending', this)" class="sub-tab-btn rounded-full bg-slate-900 px-5 py-2 text-sm font-bold whitespace-nowrap text-white transition-all">Under Review ({{ $pendingAccounts->count() }})</button>
            <button onclick="showSubTab('sub-approved', this)" class="sub-tab-btn rounded-full bg-slate-200 px-5 py-2 text-sm font-bold whitespace-nowrap text-slate-700 transition-all hover:bg-slate-300">Approved ({{ $approvedAccounts->count() }})</button>
            <button onclick="showSubTab('sub-rejected', this)" class="sub-tab-btn rounded-full bg-slate-200 px-5 py-2 text-sm font-bold whitespace-nowrap text-slate-700 transition-all hover:bg-red-200 hover:text-red-700">Rejected / Locked ({{ $rejectedAccounts->count() }})</button>
        </div>

        <!-- SUB-TAB CONTENT: PENDING -->
        <div id="sub-pending" class="sub-tab-content grid grid-cols-1 gap-6 xl:grid-cols-2">
            @forelse ($pendingAccounts as $user)
                <div class="flex flex-col gap-5 rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-all hover:shadow-md sm:flex-row">
                    <!-- TASK 1: Clickable Images -->
                    <div class="flex shrink-0 gap-2 sm:flex-col">
                        <div class="group relative">
                            <img src="{{ asset('storage/' . $user->id_photo_path) }}" class="h-20 w-20 cursor-pointer rounded-lg border border-slate-200 object-cover transition-all group-hover:opacity-75 sm:h-24 sm:w-24" onclick="openModal(this.src, 'Valid ID')" />
                            <span class="absolute right-1 bottom-1 rounded bg-slate-900/60 px-1 text-[8px] font-bold text-white">ID</span>
                        </div>
                        <div class="group relative">
                            <img src="{{ asset('storage/' . $user->selfie_photo_path) }}" class="h-20 w-20 cursor-pointer rounded-lg border border-slate-200 object-cover transition-all group-hover:opacity-75 sm:h-24 sm:w-24" onclick="openModal(this.src, 'Selfie')" />
                            <span class="absolute right-1 bottom-1 rounded bg-slate-900/60 px-1 text-[8px] font-bold text-white">SELFIE</span>
                        </div>
                    </div>

                    <div class="flex flex-1 flex-col justify-between">
                        <div>
                            <!-- TASK 1: Format exactly as requested -->
                            <h3 class="mb-1 text-xl leading-tight font-black tracking-tight text-slate-900 uppercase">{{ $user->last_name }}, {{ $user->first_name }}, {{ $user->middle_name }}, {{ $user->suffix }}</h3>
                            <p class="mb-3 flex flex-wrap gap-x-2 gap-y-1 text-[11px] font-bold tracking-widest text-slate-500 uppercase">
                                <span>{{ $user->sex }}</span>
                                <span class="text-slate-300">|</span>
                                <span>{{ $user->age }} YRS OLD</span>
                                <span class="text-slate-300">|</span>
                                <span>DOB: {{ $user->date_of_birth->format('M d, Y') }}</span>
                                <span class="text-slate-300">|</span>
                                <span class="font-mono text-slate-900">{{ $user->contact_number }}</span>
                            </p>
                        </div>

                        <div class="flex gap-2">
                            <form action="{{ route('admin.approve_account', $user->id) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit" class="w-full rounded-lg bg-slate-900 py-2.5 text-[10px] font-black tracking-widest text-white uppercase shadow-sm transition-all hover:bg-slate-800 active:scale-95">Approve</button>
                            </form>
                            <button type="button" onclick="openRejectModal('{{ $user->id }}', '{{ $user->first_name }} {{ $user->last_name }}')" class="flex-1 rounded-lg border border-red-200 bg-red-50 py-2.5 text-[10px] font-black tracking-widest text-red-600 uppercase transition-all hover:bg-red-100 active:scale-95">Reject</button>
                        </div>
                    </div>
                </div>
            @empty
                <p class="col-span-full py-10 text-center font-bold text-slate-500 italic">Walang pending registrations.</p>
            @endforelse
        </div>

        <!-- SUB-TAB CONTENT: APPROVED (TASK 3: Expandable Accordion) -->
        <div id="sub-approved" class="sub-tab-content hidden space-y-3">
            @forelse ($approvedAccounts as $user)
                <details class="group overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <summary class="flex cursor-pointer list-none items-center justify-between p-4 transition-all hover:bg-slate-50">
                        <div class="flex items-center gap-3">
                            <div class="h-2 w-2 rounded-full bg-green-500"></div>
                            <span class="text-sm font-black text-slate-900 uppercase">{{ $user->last_name }}, {{ $user->first_name }}</span>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="text-[10px] font-bold tracking-widest text-slate-400 uppercase">{{ $user->contact_number }}</span>
                            <svg class="h-5 w-5 text-slate-400 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </summary>
                    <div class="grid grid-cols-1 gap-6 border-t border-slate-50 bg-slate-50/30 p-5 text-xs md:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <p class="mb-1 font-black tracking-widest text-slate-400 uppercase">Contact Details</p>
                            <p class="font-bold text-slate-900">{{ $user->contact_number }}</p>
                            <p class="font-medium text-slate-500">{{ $user->email ?? 'No email provided' }}</p>
                        </div>
                        <div>
                            <p class="mb-1 font-black tracking-widest text-slate-400 uppercase">Personal Info</p>
                            <p class="font-bold text-slate-900 uppercase">{{ $user->sex }} | {{ $user->age }} YRS OLD</p>
                            <p class="font-medium text-slate-500 italic">DOB: {{ $user->date_of_birth->format('M d, Y') }}</p>
                        </div>
                        <div>
                            <p class="mb-1 font-black tracking-widest text-slate-400 uppercase">Address</p>
                            <p class="font-bold text-slate-900 uppercase">{{ $user->house_number }} {{ $user->purok_street }}</p>
                        </div>
                        <!-- THE FIX: Idinagdag ang Delete Button sa Approved Accounts -->
                        <div class="flex items-center justify-between border-t border-slate-100 pt-2 lg:col-span-3">
                            <p class="text-[9px] font-bold tracking-widest text-slate-400 uppercase">Terms Accepted: {{ $user->terms_accepted_at ? $user->terms_accepted_at->format('M d, Y h:i A') : 'N/A' }}</p>
                            <div class="flex items-center gap-3">
                                <!-- Images -->
                                <div class="flex gap-2">
                                    <img src="{{ asset('storage/' . $user->id_photo_path) }}" class="h-8 w-8 cursor-pointer rounded border border-slate-200 object-cover" onclick="openModal(this.src, 'ID')" />
                                    <img src="{{ asset('storage/' . $user->selfie_photo_path) }}" class="h-8 w-8 cursor-pointer rounded border border-slate-200 object-cover" onclick="openModal(this.src, 'Selfie')" />
                                </div>

                                <!-- THE NEW RED DELETE BUTTON & SUSPEND BUTTON -->
                                <div class="flex items-center gap-2 border-l border-slate-200 pl-3">
                                    <!-- THE FIX: 1-Week Penalty Modal Trigger -->
                                    <button type="button" onclick="openSuspendModal('{{ $user->id }}', '{{ $user->first_name }} {{ $user->last_name }}')" class="rounded border border-amber-200 bg-amber-50 px-3 py-1.5 text-[9px] font-black tracking-widest text-amber-600 uppercase shadow-sm transition-all hover:bg-amber-100 active:scale-95">Suspend</button>

                                    <!-- Hard Delete Button -->
                                    <button type="button" onclick="openDeleteModal('{{ $user->id }}')" class="flex items-center gap-1 rounded border border-red-200 bg-red-50 px-3 py-1.5 text-[9px] font-black tracking-widest text-red-600 uppercase shadow-sm transition-all hover:bg-red-100 active:scale-95">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </details>
            @empty
                <p class="py-10 text-center font-bold text-slate-500 italic">Walang approved accounts.</p>
            @endforelse
        </div>

        <!-- SUB-TAB CONTENT: REJECTED / LOCKED -->
        <div id="sub-rejected" class="sub-tab-content grid hidden grid-cols-1 gap-6 xl:grid-cols-2">
            @forelse ($rejectedAccounts as $user)
                <div class="flex flex-col justify-between rounded-xl border border-l-4 border-slate-200 border-l-red-500 bg-white p-5 shadow-sm">
                    <div>
                        <div class="mb-4 flex items-start justify-between">
                            <div>
                                <h3 class="text-lg leading-tight font-black tracking-tight text-slate-900 uppercase">{{ $user->last_name }}, {{ $user->first_name }}</h3>
                                <p class="text-[10px] font-bold tracking-widest text-slate-500 uppercase">{{ $user->contact_number }} | Attempts: {{ $user->rejection_count }}/5</p>
                            </div>
                            <span class="rounded bg-red-100 px-2 py-1 text-[9px] font-black tracking-widest text-red-700 uppercase shadow-sm">Rejected</span>
                        </div>

                        <div class="mb-4 rounded-lg border border-red-100 bg-red-50 p-3">
                            <p class="mb-1 text-[9px] font-black tracking-widest text-red-400 uppercase italic">Reason for Rejection</p>
                            <p class="text-xs leading-snug font-bold text-red-700">{{ $user->rejection_reason }}</p>
                        </div>
                    </div>

                    <!-- TASK 1: Replace buttons with Delete Account Modal Trigger -->
                    <button type="button" onclick="openDeleteModal('{{ $user->id }}')" class="w-full rounded-lg bg-red-600 py-2.5 text-[10px] font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-red-700 active:scale-95">Delete Account</button>
                </div>
            @empty
                <p class="col-span-full py-10 text-center font-bold text-slate-500 italic">Walang rejected accounts.</p>
            @endforelse
        </div>
    </div>
    <!-- ===================================== -->
    <!-- TAB 2: QUEUE & PROCESSING MODULE -->
    <!-- ===================================== -->
    <div id="tab-queue" class="tab-content {{ session('active_tab') == 'queue' ? 'block' : 'hidden' }}">
        <!-- TASK 2: Horizontal sub-tab for Active vs Received -->
        <div class="mt-6 mb-6 flex gap-2 overflow-x-auto pb-2">
            <button onclick="showSubTab('queue-active', this)" class="sub-tab-btn rounded-full border border-slate-900 bg-slate-900 px-5 py-2 text-sm font-bold whitespace-nowrap text-white transition-all">Active Queue ({{ $activeQueue->count() }})</button>
            <button onclick="showSubTab('queue-received', this)" class="sub-tab-btn rounded-full border border-transparent bg-slate-200 px-5 py-2 text-sm font-bold whitespace-nowrap text-slate-700 transition-all hover:bg-slate-300">Received History ({{ $receivedQueue->count() }})</button>
        </div>

        <!-- ACTIVE QUEUE TABLE -->
        <div id="queue-active" class="sub-tab-content overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-[10px] tracking-[0.15em] text-slate-500 uppercase">
                        <th class="p-4 font-black">Queue #</th>
                        <th class="p-4 font-black">Residente</th>
                        <th class="p-4 font-black">Dokumento</th>
                        <th class="p-4 font-black">Status</th>
                        <th class="p-4 text-right font-black">Aksyon</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($activeQueue as $queue)
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
                        <tr class="transition-colors hover:bg-slate-50">
                            <td class="p-4 text-xl font-black tracking-tighter text-slate-900">{{ $queue->queue_number }}</td>
                            <td class="p-4">
                                <p class="mb-1 text-sm leading-none font-bold text-slate-900 uppercase">{{ $queue->user->last_name }}, {{ $queue->user->first_name }}</p>
                                <p class="font-mono text-[10px] tracking-tight text-slate-500">{{ $queue->user->contact_number }}</p>
                            </td>
                            <td class="p-4 text-xs font-bold text-slate-700 uppercase">{{ $queue->documentType->name ?? 'N/A' }}</td>
                            <td class="p-4">
                                <!-- FIXED STATUS COLORS -->
                                <span
                                    class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest shadow-sm
                {{ $rawStatus == 'pending' ? 'bg-yellow-100 text-yellow-700 border border-yellow-200' : '' }}
                {{ $rawStatus == 'processing' ? 'bg-blue-100 text-blue-700 border border-blue-200' : '' }}
                {{ $rawStatus == 'for_interview' ? 'bg-purple-100 text-purple-700 border border-purple-200' : '' }}
                {{ $rawStatus == 'released' ? 'bg-orange-100 text-orange-700 border border-orange-200' : '' }}
                {{ $rawStatus == 'rejected' || $rawStatus == 'canceled' ? 'bg-red-100 text-red-700 border border-red-200' : '' }}
            "
                                >
                                    {{ str_replace('_', ' ', $rawStatus) }}
                                </span>
                            </td>
                            <td class="flex justify-end gap-2 p-4 text-right">
                                <!-- PRIMARY ACTION (Process/Release) -->
                                @if ($btnLabel !== '')
                                    <button type="button" onclick="openStatusModal('{{ $queue->id }}', '{{ $nextStatus }}', '{{ $btnLabel }}')" class="rounded-lg bg-slate-900 px-4 py-2 text-[10px] font-black tracking-widest text-white uppercase shadow-sm transition-all hover:bg-slate-800 active:scale-95">{{ $btnLabel }}</button>
                                @endif

                                <!-- DANGER ACTION (Reject) - Lilitaw lang kung pending o processing pa -->
                                @if ($rawStatus === 'pending' || $rawStatus === 'processing')
                                    <button type="button" onclick="openStatusModal('{{ $queue->id }}', 'rejected', 'Reject Request')" class="rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-[10px] font-black tracking-widest text-red-600 uppercase shadow-sm transition-all hover:bg-red-100 active:scale-95">Reject</button>
                                @endif

                                <!-- NO ACTION STATE -->
                                @if ($btnLabel === '' && $rawStatus !== 'pending' && $rawStatus !== 'processing')
                                    <span class="mt-2 text-xs font-bold text-slate-400 italic">No Action</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-12 text-center font-bold text-slate-400 italic">Walang aktibong nakapila.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- RECEIVED HISTORY TABLE -->
        <div id="queue-received" class="sub-tab-content hidden overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <!-- THE FIX: Logbook Print Header -->
            <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 p-4">
                <h3 class="text-sm font-bold tracking-widest text-slate-800 uppercase">Released Records</h3>
                <!-- THE FIX: Binago from <a> tag papuntang Modal Trigger Button -->
                <button onclick="openLogbookModal('{{ route('admin.queue.print_logbook') }}')" class="flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-[10px] font-black tracking-widest text-white uppercase shadow-sm transition-all hover:bg-slate-800 active:scale-95">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Tingnan ang Logbook
                </button>
            </div>

            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-[10px] tracking-[0.15em] text-slate-500 uppercase">
                        <th class="p-4 font-black">Queue #</th>
                        <th class="p-4 font-black">Residente</th>
                        <th class="p-4 font-black">Dokumento</th>
                        <th class="p-4 font-black">Date Released</th>
                        <th class="p-4 text-right font-black">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($receivedQueue as $queue)
                        <tr class="bg-slate-50/50">
                            <td class="p-4 text-xl font-black tracking-tighter text-slate-400">{{ $queue->queue_number }}</td>
                            <td class="p-4 opacity-75">
                                <p class="mb-1 text-sm leading-none font-bold text-slate-900 uppercase">{{ $queue->user->last_name }}, {{ $queue->user->first_name }}</p>
                                <p class="font-mono text-[10px] text-slate-500">{{ $queue->user->contact_number }}</p>
                            </td>
                            <td class="p-4 text-xs font-bold text-slate-600 uppercase">{{ $queue->documentType->name ?? 'N/A' }}</td>
                            <td class="p-4 text-xs font-bold text-slate-500">{{ $queue->released_at ? $queue->released_at->format('M d, Y h:i A') : 'N/A' }}</td>
                            <td class="p-4 text-right">
                                <span class="rounded-full border border-green-200 bg-green-100 px-3 py-1 text-[9px] font-black tracking-widest text-green-700 uppercase"> Received </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-12 text-center font-bold text-slate-400 italic">Walang record ng release history.</td>
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
        <div class="mx-auto max-w-3xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">
            <div class="mb-6 text-center">
                <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-900">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <h2 class="text-2xl font-black tracking-tight text-slate-900 uppercase">Walk-in Search</h2>
                <p class="mt-1 text-sm font-medium text-slate-500">Hanapin ang contact number bago gumawa ng request.</p>
            </div>

            <form action="{{ route('admin.walkin.search') }}" method="POST" class="flex flex-col gap-3 sm:flex-row">
                @csrf
                <div class="flex-1">
                    <!-- THE UI FIX: Binawasan ang tracking para hindi maghiwalay ang numbers at ginawang text-xl -->
                    <input type="text" name="contact_number" value="{{ session('walkin_search_number') }}" required placeholder="09XXXXXXXXX" oninput="this.value = this.value.replace(/[^0-9]/g, '')" maxlength="11" class="w-full rounded-lg border border-slate-300 px-5 py-3 text-center font-mono text-xl font-bold tracking-widest transition-all outline-none focus:ring-2 focus:ring-slate-900 sm:text-left" />
                </div>
                <button type="submit" class="shrink-0 rounded-lg bg-slate-900 px-8 py-3 text-xs font-black tracking-widest whitespace-nowrap text-white uppercase shadow-sm transition-all hover:bg-slate-800 active:scale-95">I-Search</button>
            </form>

            <!-- SEARCH RESULTS & FORMS -->
            @if (session('walkin_searched'))
                <div class="mt-8 border-t border-slate-100 pt-6">
                    @if (session('walkin_user'))
                        <!-- FOUND: Existing Resident Form -->
                        <form action="{{ route('admin.walkin.store') }}" method="POST" class="rounded-xl border border-green-200 bg-green-50 p-5">
                            @csrf
                            <input type="hidden" name="contact_number" value="{{ session('walkin_search_number') }}" />
                            <input type="hidden" name="is_new_user" value="0" />

                            <div class="mb-4">
                                <p class="mb-1 text-[10px] font-black tracking-widest text-green-600 uppercase">Record Found</p>
                                <p class="mb-1 text-lg leading-none font-bold text-slate-900 uppercase">{{ session('walkin_user')->last_name }}, {{ session('walkin_user')->first_name }}</p>
                                <p class="text-xs font-bold text-slate-500">{{ session('walkin_user')->sex }} | {{ session('walkin_user')->age }} YRS OLD</p>
                            </div>

                            <div class="flex flex-col gap-3 sm:flex-row">
                                <select name="document_type_id" required class="min-w-0 flex-1 rounded-lg border border-green-300 bg-white px-4 py-3 text-sm font-bold text-slate-700 outline-none focus:ring-2 focus:ring-green-600">
                                    <option value="">-- Piliin ang Dokumento --</option>
                                    @foreach ($documents as $doc)
                                        <option value="{{ $doc->id }}">{{ $doc->name }}</option>
                                    @endforeach
                                </select>
                                <input type="text" name="purpose" placeholder="Layunin (Purpose)" required class="min-w-0 flex-1 rounded-lg border border-green-300 bg-white px-4 py-3 text-sm font-bold text-slate-700 outline-none focus:ring-2 focus:ring-green-600" />
                                <!-- THE UI FIX: Nagdagdag ng shrink-0 at whitespace-nowrap para hindi mapisa ang button -->
                                <button type="submit" class="shrink-0 rounded-lg bg-green-600 px-6 py-3 text-[10px] font-black tracking-widest whitespace-nowrap text-white uppercase shadow-sm transition-all hover:bg-green-700 active:scale-95">Create Request</button>
                            </div>
                        </form>
                    @else
                        <!-- NOT FOUND: New Shadow Profile Form (Streamlined for fast typing) -->
                        <form action="{{ route('admin.walkin.store') }}" method="POST" class="rounded-xl border border-amber-200 bg-amber-50 p-5 md:p-6">
                            @csrf
                            <input type="hidden" name="contact_number" value="{{ session('walkin_search_number') }}" />
                            <input type="hidden" name="is_new_user" value="1" />

                            <p class="mb-3 text-[10px] font-black tracking-widest text-amber-600 uppercase">Walang Record: I-rehistro bilang Walk-in</p>

                            <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <input type="text" name="first_name" placeholder="First Name *" required class="w-full rounded-lg border border-amber-300 bg-white px-4 py-2.5 text-sm font-bold outline-none focus:ring-2 focus:ring-amber-600" />
                                <input type="text" name="last_name" placeholder="Last Name *" required class="w-full rounded-lg border border-amber-300 bg-white px-4 py-2.5 text-sm font-bold outline-none focus:ring-2 focus:ring-amber-600" />
                                <select name="sex" required class="w-full rounded-lg border border-amber-300 bg-white px-4 py-2.5 text-sm font-bold outline-none focus:ring-2 focus:ring-amber-600">
                                    <option value="">-- Kasarian * --</option>
                                    <option value="Male">Lalaki</option>
                                    <option value="Female">Babae</option>
                                </select>
                                <div class="flex items-center rounded-lg border border-amber-300 bg-white px-3 focus-within:ring-2 focus-within:ring-amber-600">
                                    <span class="mr-2 text-xs font-bold tracking-widest text-slate-400 uppercase">DOB*</span>
                                    <input type="date" name="date_of_birth" required class="w-full bg-transparent py-2.5 text-sm font-bold outline-none" />
                                </div>
                                <input type="text" name="house_number" placeholder="House No. *" required class="w-full rounded-lg border border-amber-300 bg-white px-4 py-2.5 text-sm font-bold outline-none focus:ring-2 focus:ring-amber-600" />
                                <input type="text" name="purok_street" placeholder="Purok/Street *" required class="w-full rounded-lg border border-amber-300 bg-white px-4 py-2.5 text-sm font-bold outline-none focus:ring-2 focus:ring-amber-600" />
                            </div>

                            <div class="mt-2 flex flex-col gap-3 border-t border-amber-200 pt-4 sm:flex-row">
                                <select name="document_type_id" required class="min-w-0 flex-1 rounded-lg border border-amber-300 bg-white px-4 py-3 text-sm font-bold text-slate-700 outline-none focus:ring-2 focus:ring-amber-600">
                                    <option value="">-- Piliin ang Dokumento --</option>
                                    @foreach ($documents as $doc)
                                        <option value="{{ $doc->id }}">{{ $doc->name }}</option>
                                    @endforeach
                                </select>
                                <input type="text" name="purpose" placeholder="Layunin (Purpose)" required class="min-w-0 flex-1 rounded-lg border border-amber-300 bg-white px-4 py-3 text-sm font-bold text-slate-700 outline-none focus:ring-2 focus:ring-amber-600" />
                                <!-- THE UI FIX: Nagdagdag ng shrink-0 at whitespace-nowrap -->
                                <button type="submit" class="shrink-0 rounded-lg bg-amber-600 px-6 py-3 text-[10px] font-black tracking-widest whitespace-nowrap text-white uppercase shadow-sm transition-all hover:bg-amber-700 active:scale-95">Register & Create</button>
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
        <div class="mx-auto mt-8 max-w-3xl">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">
                <div class="mb-2 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-900 text-white">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                    </div>
                    <h2 class="text-2xl font-black tracking-tight text-slate-900 uppercase">Broadcast Announcement</h2>
                </div>

                <p class="mb-6 border-b border-slate-100 pb-6 text-sm font-medium text-slate-500">Magpadala ng text blast sa lahat ng verified residents ng Barangay. <br /><span class="font-bold text-amber-600">Ayon sa NTC: Bawal ang links at bawal mag-send mula 9:00 PM hanggang 7:00 AM.</span></p>

                @php
                $currentHour = (int) now()->format('H');
                $isCurfew = ($currentHour >= 21 || $currentHour < 7);
            @endphp

                @if ($isCurfew || $errors->has('curfew'))
                    <div class="mb-6 rounded-r-xl border-l-4 border-red-500 bg-red-50 p-4 shadow-sm">
                        <div class="mb-1 flex items-center gap-2 text-xs font-black tracking-widest text-red-700 uppercase">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            NTC SMS Curfew Active
                        </div>
                        <p class="text-sm font-bold text-red-600">Pansamantalang naka-disable ang Broadcast (9:00 PM - 7:00 AM) upang sumunod sa Anti-Spam rules. Subukan muli bukas.</p>
                    </div>
                @endif

                <form action="{{ route('admin.announcements.broadcast') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="mb-2 block text-[10px] font-black tracking-widest text-slate-400 uppercase">Mensahe (Message Body) <span class="text-red-500">*</span></label>
                        <textarea name="message_body" id="announcement_text" rows="5" required {{ $isCurfew ? 'disabled' : '' }} placeholder="{{ $isCurfew ? 'Naka-disable ang pag-type tuwing curfew...' : 'I-type ang iyong anunsyo dito...' }}" class="w-full px-4 py-3 rounded-xl border @error('message_body') border-red-500 bg-red-50 @else border-slate-300 bg-slate-50 @enderror focus:ring-2 focus:ring-slate-900 outline-none transition-all font-medium text-slate-800 resize-none {{ $isCurfew ? 'opacity-60 cursor-not-allowed bg-slate-100' : '' }}" oninput="updateCharCount(this)">{{ old('message_body') }}</textarea>

                        @error ('message_body')
                            <p class="mt-2 text-xs font-bold text-red-600">{{ $message }}</p>
                        @enderror

                        <div class="mt-2 flex items-start justify-between">
                            <p id="char_warning" class="mt-1 hidden text-xs font-bold text-red-600">⚠️ Bawal mag-send ng links (http/www).</p>
                            <p id="char_counter" class="mt-1 ml-auto text-xs font-bold text-slate-500">Characters: 0/160 (Est. 1 Credit per user)</p>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" id="broadcastBtn" {{ $isCurfew ? 'disabled' : '' }} class="bg-slate-900 hover:bg-slate-800 text-white font-black text-xs uppercase tracking-widest px-8 py-3.5 rounded-xl transition-all active:scale-95 shadow-md flex items-center gap-2 {{ $isCurfew ? 'opacity-50 cursor-not-allowed hover:bg-slate-900 active:scale-100' : '' }}"> Send Broadcast</button>
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
            if (/(http|https|www\.)/i.test(textarea.value)) {
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
            if (length > 160) {
                credits = Math.ceil(length / 153);
            }

            counter.innerText = `Characters: ${length} (Est. ${credits} Credit/s per user)`;

            if (length > 160) {
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
        <div class="mt-6 mb-6 flex gap-2 overflow-x-auto pb-2">
            <button onclick="showSubTab('sub-audit-trail', this)" class="sub-tab-btn rounded-full border border-slate-900 bg-slate-900 px-5 py-2 text-sm font-bold whitespace-nowrap text-white transition-all">System Audit Trail</button>
            <button onclick="showSubTab('sub-notif-history', this)" class="sub-tab-btn rounded-full border border-transparent bg-slate-200 px-5 py-2 text-sm font-bold whitespace-nowrap text-slate-700 transition-all hover:bg-slate-300">Notification History</button>
            <button onclick="showSubTab('sub-generate-pdf', this)" class="sub-tab-btn rounded-full border border-transparent bg-slate-200 px-5 py-2 text-sm font-bold whitespace-nowrap text-slate-700 transition-all hover:bg-slate-300">Generate Analytics</button>
        </div>

        <!-- 1. SYSTEM AUDIT TRAIL -->
        <div id="sub-audit-trail" class="sub-tab-content block overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-slate-50 p-6">
                <h2 class="text-xl font-black tracking-tight text-slate-900 uppercase">System Audit Logs</h2>
            </div>
            <div class="max-h-[600px] overflow-x-auto overflow-y-auto">
                <table class="relative w-full border-collapse text-left">
                    <thead class="sticky top-0 z-10">
                        <tr class="border-b border-slate-200 bg-slate-100 text-[10px] tracking-[0.15em] text-slate-500 uppercase">
                            <th class="p-4 font-black">Petsa & Oras</th>
                            <th class="p-4 font-black">Admin</th>
                            <th class="p-4 font-black">Aksyon</th>
                            <th class="w-1/2 p-4 font-black">Detalye</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($auditLogs as $log)
                            <tr class="transition-colors hover:bg-slate-50">
                                <td class="p-4 text-xs font-bold text-slate-600">{{ \Carbon\Carbon::parse($log->created_at)->format('M d, Y h:i A') }}</td>
                                <td class="p-4 text-xs font-bold text-slate-900 uppercase">{{ $log->admin->first_name ?? 'System' }} {{ $log->admin->last_name ?? '' }}</td>
                                <td class="p-4">
                                    <span class="rounded bg-slate-900 px-2 py-1 text-[9px] font-black tracking-widest text-white uppercase shadow-sm">{{ $log->action }}</span>
                                </td>
                                <td class="p-4 text-xs font-medium text-slate-600">{{ $log->description }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-12 text-center font-bold text-slate-400 italic">Wala pang naitalang galaw sa system.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2. NOTIFICATION HISTORY -->
        <div id="sub-notif-history" class="sub-tab-content hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-slate-50 p-6">
                <h2 class="text-xl font-black tracking-tight text-slate-900 uppercase">Notification History</h2>
            </div>
            <div class="max-h-[600px] overflow-x-auto overflow-y-auto">
                <table class="relative w-full border-collapse text-left">
                    <thead class="sticky top-0 z-10">
                        <tr class="border-b border-slate-200 bg-slate-100 text-[10px] tracking-[0.15em] text-slate-500 uppercase">
                            <th class="p-4 font-black">Petsa & Oras</th>
                            <th class="p-4 font-black">Residente</th>
                            <th class="p-4 font-black">Channel</th>
                            <th class="w-2/5 p-4 font-black">Mensahe</th>
                            <th class="p-4 text-right font-black">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($notificationLogs as $notif)
                            <tr class="transition-colors hover:bg-slate-50">
                                <td class="p-4 text-xs font-bold text-slate-600">{{ \Carbon\Carbon::parse($notif->created_at)->format('M d, Y h:i A') }}</td>
                                <td class="p-4">
                                    <p class="text-xs font-bold text-slate-900 uppercase">{{ $notif->user->first_name ?? 'N/A' }} {{ $notif->user->last_name ?? '' }}</p>
                                    <p class="font-mono text-[9px] text-slate-500">{{ $notif->recipient_contact }}</p>
                                </td>
                                <td class="p-4">
                                    @if (strtolower($notif->channel) == 'sms')
                                        <span class="rounded border border-blue-200 bg-blue-100 px-2 py-1 text-[9px] font-black tracking-widest text-blue-700 uppercase shadow-sm">SMS</span>
                                    @else
                                        <span class="rounded border border-purple-200 bg-purple-100 px-2 py-1 text-[9px] font-black tracking-widest text-purple-700 uppercase shadow-sm">EMAIL</span>
                                    @endif
                                </td>
                                <td class="line-clamp-2 p-4 text-[11px] font-medium text-slate-600" title="{{ $notif->message_content }}">{{ $notif->message_content }}</td>
                                <td class="p-4 text-right">
                                    @if (str_contains(strtolower($notif->status), 'sent'))
                                        <span class="rounded bg-green-100 px-2 py-1 text-[9px] font-black tracking-widest text-green-700 uppercase">{{ $notif->status }}</span>
                                    @else
                                        <span class="rounded bg-red-100 px-2 py-1 text-[9px] font-black tracking-widest text-red-700 uppercase" title="{{ $notif->provider_response }}">{{ $notif->status }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-12 text-center font-bold text-slate-400 italic">Walang record ng notifications.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 3. GENERATE ANALYTICS (CUSTOM DROPDOWNS & MODAL TARGET) -->
        <div id="sub-generate-pdf" class="sub-tab-content hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-slate-50 p-6">
                <h2 class="text-xl font-black tracking-tight text-slate-900 uppercase">Generate System Analytics</h2>
            </div>
            <div class="mx-auto my-8 max-w-2xl p-8">
                <!-- PANSININ ANG TARGET: Tumuturo ito sa pangalan ng iframe sa loob ng modal natin -->
                <form action="{{ route('admin.reports.generate') }}" method="POST" target="pdfViewerFrame" onsubmit="openPdfModal()" class="flex flex-col gap-6">
                    @csrf
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <label class="mb-2 block text-[10px] font-black tracking-widest text-slate-400 uppercase">Piliin ang Buwan <span class="text-red-500">*</span></label>
                            <select name="report_month" required class="w-full cursor-pointer rounded-lg border border-slate-300 bg-white px-4 py-2.5 font-bold text-slate-700 outline-none focus:ring-2 focus:ring-slate-900">
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
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <label class="mb-2 block text-[10px] font-black tracking-widest text-slate-400 uppercase">Piliin ang Taon <span class="text-red-500">*</span></label>
                            <select name="report_year" required class="w-full cursor-pointer rounded-lg border border-slate-300 bg-white px-4 py-2.5 font-bold text-slate-700 outline-none focus:ring-2 focus:ring-slate-900">
                                @for ($y = date('Y'); $y >= 2024; $y--)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 py-4 text-xs font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-slate-800 active:scale-95">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        Tingnan ang Analytics (In-App)
                    </button>
                </form>
            </div>
        </div>
    </div>
    <!-- ===================================== -->
    <!-- IN-APP PDF VIEWER MODAL (SPA Illusion) -->
    <!-- ===================================== -->
    <div id="pdfModal" class="fixed inset-0 z-[200] hidden items-center justify-center bg-slate-900/90 p-4 backdrop-blur-sm transition-opacity sm:p-8">
        <div class="flex h-[90vh] w-full max-w-5xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 p-4">
                <h2 class="flex items-center gap-2 text-lg font-black tracking-tight text-slate-900 uppercase">
                    <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    System Analytics Report
                </h2>
                <button onclick="closePdfModal()" class="text-3xl leading-none font-bold text-slate-400 transition-all hover:text-red-600">&times;</button>
            </div>
            <!-- Modal Body: IFRAME -->
            <div class="relative w-full flex-1 bg-slate-200">
                <!-- Loading spinner -->
                <div class="absolute inset-0 -z-10 flex items-center justify-center">
                    <svg class="h-10 w-10 animate-spin text-slate-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <iframe name="pdfViewerFrame" id="pdfViewerFrame" onload="hideGlobalLoader()" class="relative z-10 h-full w-full bg-white"></iframe>
            </div>
            <!-- Modal Footer: WITH DOWNLOAD BUTTON -->
            <div class="flex items-center justify-between border-t border-slate-200 bg-slate-50 p-4">
                <button onclick="downloadPdfNow()" class="flex items-center gap-2 rounded-xl bg-red-600 px-6 py-3 text-[10px] font-black tracking-widest text-white uppercase shadow-sm transition-all hover:bg-red-700 active:scale-95">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    I-Download ang PDF
                </button>
                <button onclick="closePdfModal()" class="rounded-xl bg-slate-900 px-8 py-3 text-[10px] font-black tracking-widest text-white uppercase shadow-sm transition-all hover:bg-slate-800 active:scale-95">Isara</button>
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
            form.action = '{{ route('admin.reports.generate') }}';
            form.target = '_blank'; // Magda-download ito sa bagong tab

            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);

            const mInput = document.createElement('input');
            mInput.type = 'hidden';
            mInput.name = 'report_month';
            mInput.value = month;
            form.appendChild(mInput);

            const yInput = document.createElement('input');
            yInput.type = 'hidden';
            yInput.name = 'report_year';
            yInput.value = year;
            form.appendChild(yInput);

            const dlInput = document.createElement('input');
            dlInput.type = 'hidden';
            dlInput.name = 'is_download';
            dlInput.value = '1';
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

        // JS Logic para sa Logbook Modal
        function openLogbookModal(url) {
            const modal = document.getElementById('logbookModal');
            const loader = document.getElementById('global-loader');

            // I-trigger ang loader habang nag-ge-generate pa ng PDF ang server
            if (loader) {
                loader.classList.remove('hidden');
                loader.classList.add('flex');
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';

            // I-assign ang URL sa iframe para mag-load
            document.getElementById('logbookViewerFrame').src = url;
        }

        function closeLogbookModal() {
            const modal = document.getElementById('logbookModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto';
            document.getElementById('logbookViewerFrame').src = 'about:blank';
        }
    </script>
    <!-- TASK 2: UPDATED STATUS CONFIRMATION MODAL -->
    <div id="statusModal" class="fixed inset-0 z-[100] flex hidden items-center justify-center bg-slate-900/80 p-4 backdrop-blur-sm">
        <div class="w-full max-w-sm transform overflow-hidden rounded-2xl border border-slate-100 bg-white p-6 shadow-2xl transition-all">
            <div class="mb-6 flex flex-col items-center text-center">
                <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-900">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                </div>
                <h3 id="statusModalTitle" class="text-xl font-black tracking-tight text-slate-900 uppercase">Move to <span id="nextStatusLabel" class="text-blue-600">...</span>?</h3>
                <p class="mt-2 px-4 text-sm font-medium text-slate-500">Sigurado ka bang gusto mong i-update ang status ng request na ito?</p>
            </div>

            <form id="statusForm" method="POST">
                @csrf
                <input type="hidden" name="status" id="targetStatusInput" />

                <!-- THE FIX: Binaliktad ang pwesto ng buttons at nilagyan ng ID ang Submit -->
                <div class="flex flex-col gap-3">
                    <button type="button" onclick="closeStatusModal()" class="w-full rounded-xl border border-slate-200 bg-white py-3 text-[10px] font-black tracking-widest text-slate-500 uppercase shadow-sm transition-all hover:bg-slate-50 hover:text-slate-900 active:scale-95">Kanselahin (Cancel)</button>
                    <button type="submit" id="statusSubmitBtn" class="w-full rounded-xl bg-slate-900 py-3 text-xs font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-slate-800 active:scale-95">Confirm Update</button>
                </div>
            </form>
        </div>
    </div>
    <!-- TASK 1: DELETE ACCOUNT MODAL -->
    <div id="deleteModal" class="fixed inset-0 z-[110] flex hidden items-center justify-center bg-slate-900/80 p-4 backdrop-blur-sm">
        <div class="w-full max-w-sm transform overflow-hidden rounded-2xl border border-red-100 bg-white p-6 shadow-2xl transition-all">
            <div class="mb-6 flex flex-col items-center text-center">
                <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-red-100 text-red-600">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </div>
                <h3 class="text-xl font-black tracking-tight text-slate-900 uppercase">Delete Account?</h3>
                <p class="mt-2 px-4 text-sm font-medium text-slate-500">Warning: Ang lahat ng data pati litrato ay permanenteng mawawala. Hindi na ito maibabalik.</p>
            </div>

            <form id="deleteForm" method="POST">
                @csrf
                @method ('DELETE')
                <div class="flex flex-col gap-3">
                    <button type="submit" class="w-full rounded-xl bg-red-600 py-3 text-xs font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-red-700 active:scale-95">Delete Permanently</button>
                    <button type="button" onclick="closeDeleteModal()" class="w-full py-2 text-[10px] font-black tracking-widest text-slate-400 uppercase transition-all hover:text-slate-600">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <!-- IMAGE VIEWER MODAL -->
    <div id="imageModal" class="fixed inset-0 z-[120] flex hidden items-center justify-center bg-slate-900/90 p-4 backdrop-blur-sm">
        <div class="relative w-full max-w-4xl overflow-hidden rounded-xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 p-4">
                <h3 id="modalTitle" class="text-lg font-black tracking-tighter text-slate-900 uppercase">Preview</h3>
                <button onclick="closeModal()" class="text-2xl leading-none font-bold text-slate-400 transition-all hover:text-red-600">&times;</button>
            </div>
            <div class="flex justify-center bg-slate-200 p-4">
                <img id="modalImg" src="" class="max-h-[65vh] rounded object-contain shadow-lg" />
            </div>
            <div class="border-t border-slate-200 bg-slate-50 p-4 text-right">
                <button onclick="closeModal()" class="rounded-lg bg-slate-900 px-10 py-3 text-[10px] font-black tracking-widest text-white uppercase transition-all hover:bg-slate-800 active:scale-95">Isara</button>
            </div>
        </div>
    </div>
    <!-- REJECT REASON MODAL -->
    <div id="rejectModal" class="fixed inset-0 z-[110] hidden items-center justify-center bg-slate-900/80 p-4 backdrop-blur-sm">
        <div class="w-full max-w-md transform overflow-hidden rounded-2xl border border-red-100 bg-white shadow-2xl transition-all">
            <div class="flex items-center justify-between border-b border-red-100 bg-red-50 p-4 text-red-700">
                <h3 class="flex items-center gap-2 text-lg font-black tracking-tight uppercase">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Reject Registration
                </h3>
                <button onclick="closeRejectModal()" class="text-2xl font-bold text-red-300 transition-all hover:text-red-700">&times;</button>
            </div>

            <form id="rejectForm" method="POST" action="">
                @csrf
                <div class="p-6">
                    <p class="mb-4 text-sm font-medium text-slate-600">Ita-type mo ang rason kung bakit rejected si <strong id="rejectUserName" class="text-slate-900"></strong>. Ipapadala ito via SMS.</p>

                    <label class="mb-2 block text-[10px] font-black tracking-[0.2em] text-slate-400 uppercase">Rason ng Rejection</label>
                    <input type="text" name="rejection_reason" maxlength="60" required placeholder="Hal: Malabo ang ID, paki-upload ng maayos." class="w-full rounded-xl border-2 border-slate-100 p-3 font-bold text-slate-900 placeholder-slate-300 transition-all outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500" />
                    <p class="mt-2 text-right text-[9px] font-black text-slate-400 italic">Limit: 60 characters</p>
                </div>

                <div class="flex gap-3 border-t border-slate-100 bg-slate-50 p-4">
                    <button type="button" onclick="closeRejectModal()" class="flex-1 rounded-xl border border-slate-200 bg-white py-3 text-[10px] font-black tracking-widest text-slate-500 uppercase transition-all hover:bg-slate-100">Kanselahin</button>
                    <button type="submit" class="flex-1 rounded-xl bg-red-600 py-3 text-[10px] font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-red-700 active:scale-95">I-Submit Reject</button>
                </div>
            </form>
        </div>
    </div>
    <!-- ===================================== -->
    <!-- IN-APP LOGBOOK VIEWER MODAL           -->
    <!-- ===================================== -->
    <div id="logbookModal" class="fixed inset-0 z-[200] hidden items-center justify-center bg-slate-900/90 p-4 backdrop-blur-sm transition-opacity sm:p-8">
        <div class="flex h-[90vh] w-full max-w-5xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 p-4">
                <h2 class="flex items-center gap-2 text-lg font-black tracking-tight text-slate-900 uppercase">
                    <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Official Release Logbook
                </h2>
                <button onclick="closeLogbookModal()" class="text-3xl leading-none font-bold text-slate-400 transition-all hover:text-red-600">&times;</button>
            </div>
            <!-- Modal Body: IFRAME -->
            <div class="relative w-full flex-1 bg-slate-200">
                <div class="absolute inset-0 -z-10 flex items-center justify-center">
                    <svg class="h-10 w-10 animate-spin text-slate-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <iframe name="logbookViewerFrame" id="logbookViewerFrame" onload="hideGlobalLoader()" class="relative z-10 h-full w-full bg-white"></iframe>
            </div>
            <!-- Modal Footer: WITH DOWNLOAD BUTTON -->
            <div class="flex items-center justify-between border-t border-slate-200 bg-slate-50 p-4">
                <a href="{{ route('admin.queue.print_logbook') }}?download=1" class="flex items-center gap-2 rounded-xl bg-red-600 px-6 py-3 text-[10px] font-black tracking-widest text-white uppercase shadow-sm transition-all hover:bg-red-700 active:scale-95">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    I-Download ang Logbook
                </a>
                <button onclick="closeLogbookModal()" class="rounded-xl bg-slate-900 px-8 py-3 text-[10px] font-black tracking-widest text-white uppercase shadow-sm transition-all hover:bg-slate-800 active:scale-95">Isara</button>
            </div>
        </div>
    </div>
    <!-- TASK 1: SUSPEND ACCOUNT MODAL (7-Day Penalty) -->
    <div id="suspendModal" class="fixed inset-0 z-[200] flex hidden items-center justify-center bg-slate-900/80 p-4 backdrop-blur-sm transition-opacity">
        <div class="w-full max-w-sm transform overflow-hidden rounded-2xl border border-amber-100 bg-white p-6 shadow-2xl transition-all">
            <div class="mb-6 flex flex-col items-center text-center">
                <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h3 class="text-xl font-black tracking-tight text-slate-900 uppercase">Suspend Account?</h3>
                <p class="mt-2 px-4 text-sm font-medium text-slate-500">Sigurado ka bang gusto mong patawan ng 7-araw na penalty si <strong id="suspendUserName" class="text-slate-900"></strong>?</p>
            </div>

            <form id="suspendForm" method="POST">
                @csrf
                <div class="flex flex-col gap-3">
                    <button type="submit" class="w-full rounded-xl bg-amber-600 py-3 text-xs font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-amber-700 active:scale-95">Ipataw ang Penalty</button>
                    <button type="button" onclick="closeSuspendModal()" class="w-full py-2 text-[10px] font-black tracking-widest text-slate-400 uppercase transition-all hover:text-slate-600">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <!-- ===================================== -->
    <!-- TAB 6: ACCOUNT SETTINGS               -->
    <!-- ===================================== -->
    <div id="tab-settings" class="tab-content {{ session('active_tab') == 'settings' ? 'block' : 'hidden' }}">
        <div class="mx-auto mt-8 max-w-2xl">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">
                <div class="mb-6 flex items-center gap-3 border-b border-slate-100 pb-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-white shadow-sm">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </div>
                    <h2 class="text-2xl font-black tracking-tight text-slate-900 uppercase">Security Settings</h2>
                </div>

                <p class="mb-6 text-sm font-medium text-slate-500">Mahalaga: Palitan agad ang iyong default password upang maiwasan ang unauthorized access sa Admin Portal.</p>

                @if ($errors->has('current_password') || $errors->has('password'))
                    <div class="mb-6 rounded-r-xl border-l-4 border-red-500 bg-red-50 p-4">
                        <p class="text-sm font-bold text-red-700">{{ $errors->first() }}</p>
                    </div>
                @endif

                <form action="{{ route('password.update') }}" method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <label class="mb-2 block text-[10px] font-black tracking-widest text-slate-400 uppercase">Kasalukuyang Password <span class="text-red-500">*</span></label>
                        <input type="password" name="current_password" required class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 font-bold text-slate-700 focus:ring-2 focus:ring-slate-900 focus:outline-none" />
                    </div>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-[10px] font-black tracking-widest text-slate-400 uppercase">Bagong Password <span class="text-red-500">*</span></label>
                            <input type="password" name="password" required minlength="8" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 font-bold text-slate-700 focus:ring-2 focus:ring-slate-900 focus:outline-none" />
                        </div>
                        <div>
                            <label class="mb-2 block text-[10px] font-black tracking-widest text-slate-400 uppercase">I-type Ulit (Confirm) <span class="text-red-500">*</span></label>
                            <input type="password" name="password_confirmation" required minlength="8" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 font-bold text-slate-700 focus:ring-2 focus:ring-slate-900 focus:outline-none" />
                        </div>
                    </div>
                    <div class="flex justify-end border-t border-slate-100 pt-4">
                        <button type="submit" class="flex items-center gap-2 rounded-xl bg-slate-900 px-8 py-3.5 text-xs font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-slate-800 active:scale-95">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path></svg>
                            I-save ang Bagong Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
