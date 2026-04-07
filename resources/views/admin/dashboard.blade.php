@extends('admin.layouts.admin')

@section('content')

<!-- ===================================== -->
<!-- TAB 1: PENDING REGISTRATIONS & ACCOUNTS -->
<!-- ===================================== -->
<div id="tab-pending" class="tab-content block">

    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 mb-6 flex flex-col sm:flex-row gap-4 items-center justify-between">
        <form method="GET" action="{{ route('admin.dashboard') }}" class="flex-1 flex gap-2 w-full">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Maghanap ng pangalan o number..." class="flex-1 border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-slate-900 focus:outline-none">
            <select name="sort" onchange="this.form.submit()" class="border border-slate-300 rounded-lg px-4 py-2 bg-slate-50 focus:outline-none">
                <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Pinakabago</option>
                <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Pinakaluma</option>
            </select>
            <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-bold px-6 py-2 rounded-lg transition-all active:scale-95">Search</button>
        </form>
    </div>

    <div class="flex gap-2 mb-6 overflow-x-auto pb-2">
        <button onclick="showSubTab('sub-pending', this)" class="sub-tab-btn px-5 py-2 rounded-full bg-slate-900 text-white font-bold text-sm whitespace-nowrap transition-all">
            Under Review ({{ $pendingAccounts->count() }})
        </button>
        <button onclick="showSubTab('sub-approved', this)" class="sub-tab-btn px-5 py-2 rounded-full bg-slate-200 text-slate-700 hover:bg-slate-300 font-bold text-sm whitespace-nowrap transition-all">
            Approved ({{ $approvedAccounts->count() }})
        </button>
        <button onclick="showSubTab('sub-locked', this)" class="sub-tab-btn px-5 py-2 rounded-full bg-slate-200 text-slate-700 hover:bg-red-200 hover:text-red-700 font-bold text-sm whitespace-nowrap transition-all">
            Locked / Rejected ({{ $lockedAccounts->count() }})
        </button>
    </div>

    <!-- SUB-TAB CONTENT: PENDING -->
    <div id="sub-pending" class="sub-tab-content grid grid-cols-1 xl:grid-cols-2 gap-6">
        @forelse($pendingAccounts as $user)
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tight">{{ $user->last_name }}, {{ $user->first_name }}</h3>
            <p class="font-mono font-bold text-slate-600 mb-4">{{ $user->contact_number }} | Attempts: {{ $user->rejection_count }}/5</p>

            <div class="flex gap-3 mt-4">
                <form action="{{ route('admin.approve_account', $user->id) }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full bg-slate-900 text-white font-bold py-2 rounded-lg hover:bg-slate-800 transition-all active:scale-95">Approve</button>
                </form>
                <button type="button" onclick="openRejectModal('{{ $user->id }}', '{{ $user->first_name }} {{ $user->last_name }}')" class="flex-1 bg-red-50 text-red-600 border border-red-200 font-bold py-2 rounded-lg hover:bg-red-100 transition-all active:scale-95">Reject</button>
            </div>
        </div>
        @empty
        <p class="col-span-full text-slate-500 text-center py-10 font-bold">Walang pending registrations.</p>
        @endforelse
    </div>

    <!-- SUB-TAB CONTENT: APPROVED -->
    <div id="sub-approved" class="sub-tab-content hidden grid grid-cols-1 gap-4">
        @forelse($approvedAccounts as $user)
        <div class="bg-white p-4 border border-green-200 rounded-lg flex justify-between items-center border-l-4 border-l-green-500">
            <div>
                <p class="font-bold text-slate-900 uppercase">{{ $user->last_name }}, {{ $user->first_name }}</p>
                <p class="text-sm text-slate-500 font-mono">{{ $user->contact_number }}</p>
            </div>
            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold">Verified</span>
        </div>
        @empty
        <p class="text-slate-500 text-center py-10 font-bold">Walang approved accounts.</p>
        @endforelse
    </div>

    <!-- SUB-TAB CONTENT: LOCKED / REJECTED -->
    <div id="sub-locked" class="sub-tab-content hidden grid grid-cols-1 gap-4">
        @forelse($lockedAccounts as $user)
        <div class="bg-white p-4 border border-red-200 rounded-lg flex justify-between items-center border-l-4 border-l-red-500">
            <div>
                <p class="font-bold text-slate-900 uppercase">{{ $user->last_name }}, {{ $user->first_name }}</p>
                <p class="text-sm text-slate-500">Rason: <span class="font-bold text-red-600">{{ $user->rejection_reason }}</span></p>
                <p class="text-xs text-slate-400 mt-1">Locked Until: {{ $user->locked_until ? $user->locked_until->format('M d, Y h:i A') : 'N/A' }}</p>
            </div>
            <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold">Locked (5/5)</span>
        </div>
        @empty
        <p class="text-slate-500 text-center py-10 font-bold">Walang locked o rejected accounts.</p>
        @endforelse
    </div>
</div>

<!-- ===================================== -->
<!-- TAB 2: QUEUE & PROCESSING MODULE -->
<!-- ===================================== -->
<div id="tab-queue" class="tab-content {{ session('active_tab') == 'queue' ? 'block' : 'hidden' }}">
    <div class="mb-4 flex justify-between items-center">
        <h2 class="text-xl font-bold text-slate-800">Live Queue Board</h2>
        <span class="bg-slate-900 text-white px-3 py-1 rounded-full text-sm font-bold">In Line: {{ $activeQueue->count() }}</span>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-xs uppercase tracking-wider">
                    <th class="p-4 font-bold">Queue #</th>
                    <th class="p-4 font-bold">Residente</th>
                    <th class="p-4 font-bold">Dokumento</th>
                    <th class="p-4 font-bold">Status</th>
                    <th class="p-4 font-bold text-right">Aksyon</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($activeQueue as $queue)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="p-4 font-black text-slate-900 text-lg">{{ $queue->queue_number }}</td>
                    <td class="p-4">
                        <p class="font-bold text-slate-900 uppercase">{{ $queue->user->last_name }}, {{ $queue->user->first_name }}</p>
                        <p class="text-xs text-slate-500 font-mono">{{ $queue->user->contact_number }}</p>
                    </td>
                    <td class="p-4 font-bold text-slate-700">{{ $queue->documentType->name ?? 'N/A' }}</td>
                    <td class="p-4">
                        <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-xs font-bold uppercase">{{ $queue->status }}</span>
                    </td>
                    <td class="p-4 text-right">
                        <form action="{{ route('admin.request.update_status', $queue->id) }}" method="POST" class="flex justify-end gap-2">
                            @csrf
                            <select name="status" class="border border-slate-300 rounded-lg px-2 py-1 text-sm font-bold bg-slate-50 focus:outline-none">
                                <option value="pending" {{ $queue->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="received" {{ $queue->status == 'received' ? 'selected' : '' }}>Received</option>
                                <option value="for_interview" {{ $queue->status == 'for_interview' ? 'selected' : '' }}>Interview</option>
                                <option value="processing" {{ $queue->status == 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="released" {{ $queue->status == 'released' ? 'selected' : '' }}>Release</option>
                            </select>
                            <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-bold px-3 py-1 rounded-lg text-sm transition-all active:scale-95">Update</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-8 text-center text-slate-500 font-bold">Walang nakapila ngayon.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="tab-walkin" class="tab-content hidden"><h2 class="text-xl font-bold text-slate-800">Walk-in Requests Module (WIP)</h2></div>
<div id="tab-announcements" class="tab-content hidden"><h2 class="text-xl font-bold text-slate-800">Announcements Module (WIP)</h2></div>
<div id="tab-audit" class="tab-content hidden"><h2 class="text-xl font-bold text-slate-800">System Audit Logs (WIP)</h2></div>

<!-- IMAGE VIEWER MODAL -->
<div id="imageModal" class="hidden fixed inset-0 z-[2] bg-slate-900/90 backdrop-blur-sm flex justify-center items-center p-4">
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

<!-- REJECT REASON MODAL -->
<div id="rejectModal" class="hidden fixed inset-0 z-[1] bg-slate-900/80 backdrop-blur-sm justify-center items-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all">
        <div class="bg-red-50 border-b border-red-100 p-4 flex justify-between items-center">
            <h3 class="text-lg font-bold text-red-700 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                I-reject ang Account
            </h3>
        </div>

        <form id="rejectForm" method="POST" action="">
            @csrf
            <div class="p-6">
                <p class="text-sm text-slate-600 mb-4">Ita-type mo ang rason kung bakit rejected si <strong id="rejectUserName" class="text-slate-900"></strong>. Ipapadala ito sa kanyang SMS.</p>
                
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Rason ng Rejection</label>
                <input type="text" name="rejection_reason" maxlength="60" required placeholder="Hal: Malabo ang ID, paki-upload ng maayos." class="w-full border border-slate-300 p-3 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none text-slate-900 placeholder-slate-400">
                <p class="text-xs text-slate-400 mt-2 text-right">Max: 60 characters</p>
            </div>

            <div class="bg-slate-50 p-4 border-t border-slate-100 flex gap-3">
                <button type="button" onclick="closeRejectModal()" class="flex-1 bg-white border border-slate-300 text-slate-700 font-bold py-2.5 rounded-lg hover:bg-slate-100 active:scale-95 transition-all">Kanselahin</button>
                <button type="submit" class="flex-1 bg-red-600 text-white font-bold py-2.5 rounded-lg hover:bg-red-700 active:scale-95 transition-all shadow-md">I-Submit Reject</button>
            </div>
        </form>
    </div>
</div>
@endsection