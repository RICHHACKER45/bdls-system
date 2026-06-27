import React, { useState, useEffect } from 'react';
import { Head, useForm, usePage, Link, router } from '@inertiajs/react';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, PieChart, Pie, Cell, ResponsiveContainer } from 'recharts';
import AdminLayout from '@/Layouts/AdminLayout';
import axios from 'axios';

const Pagination = ({ links }) => {
    if (!links || links.length <= 3) return null;
    return (
        <div className="mt-4 flex flex-wrap gap-1">
            {links.map((link, i) => (
                <Link
                    key={i}
                    href={link.url || '#'}
                    preserveScroll
                    className={`rounded border px-3 py-1 text-sm transition-all ${
                        link.active
                            ? 'bg-slate-900 text-white'
                            : 'bg-white text-slate-500 hover:bg-slate-100'
                    } ${!link.url ? 'cursor-not-allowed opacity-50' : ''}`}
                    dangerouslySetInnerHTML={{ __html: link.label }}
                />
            ))}
        </div>
    );
};

export default function AdminDashboard() {
    // Idagdag ito malapit sa ibang useEffect sa Admin-Dashboard.jsx
    useEffect(() => {
        if (window.Echo) {
            // Makikinig ang Admin Portal sa public channel
            window.Echo.channel('admin.updates').listen('AdminDashboardUpdated', (e) => {
                // SILENT REFRESH: Kukuha ng bagong data ang Inertia nang walang screen refresh o loading UI!
                router.reload({
                    only: ['activeQueue', 'receivedQueue', 'auditLogs', 'notificationLogs'],
                    preserveScroll: true,
                    preserveState: true, // <-- ITO ANG MAGIC: Hindi mawawala ang tinatype o nakabukas na modal ng Admin
                });
            });
        }

        // Cleanup para hindi magdoble ang listener kapag umalis sa page
        return () => {
            if (window.Echo) window.Echo.leaveChannel('admin.updates');
        };
    }, []);
    const {
        activeQueue = { data: [], links: [] },
        receivedQueue = { data: [], links: [] },
        documents = [],
        auditLogs = { data: [], links: [] },
        analyticsSummary = {},
        filters = {},
        auth,
        flash = {},
        errors = {},
    } = usePage().props;

    const [activeTab, setActiveTab] = useState('queue');
    const [queueSubTab, setQueueSubTab] = useState('queue-active');

    const [statusModal, setStatusModal] = useState({
        isOpen: false,
        requestId: null,
        nextStatus: '',
        label: '',
    });

    const [pdfModalOpen, setPdfModalOpen] = useState(false);
    const [logbookModalOpen, setLogbookModalOpen] = useState(false);
    const [logbookUrl, setLogbookUrl] = useState('');

    const [localToast, setLocalToast] = useState({ visible: false, message: '' });
    const triggerToast = (msg) => {
        setLocalToast({ visible: true, message: msg });
        setTimeout(() => setLocalToast({ visible: false, message: '' }), 5000);
    };

    // ==========================================
    // BUG FIX 1: Separated Transform and Post!
    // ==========================================
    const statusForm = useForm({ status: '' });
    const submitStatus = (e) => {
        e.preventDefault();
        statusForm.transform((data) => ({ ...data, status: statusModal.nextStatus }));
        statusForm.post(route('admin.request.update_status', statusModal.requestId), {
            preserveScroll: true,
            onSuccess: () =>
                setStatusModal({ isOpen: false, requestId: null, nextStatus: '', label: '' }),
            onError: (errs) => {
                if (Object.keys(errs).length > 0) triggerToast(Object.values(errs));
            },
        });
    };

    const announcementForm = useForm({ message_body: '' });
    const [isLinkDetected, setIsLinkDetected] = useState(false);

    const handleAnnouncementChange = (e) => {
        const val = e.target.value;
        announcementForm.setData('message_body', val);
        setIsLinkDetected(/(http|https|www\.)/i.test(val));
        announcementForm.clearErrors('message_body');
    };

    const charCount = announcementForm.data.message_body.length;
    const credits = charCount > 160 ? Math.ceil(charCount / 153) : 1;
    const currentHour = new Date().getHours();
    const isCurfew = currentHour >= 21 || currentHour < 7;

    const submitAnnouncement = (e) => {
        e.preventDefault();
        announcementForm.post(route('admin.announcements.broadcast'), {
            preserveScroll: true,
            onSuccess: () => announcementForm.reset(),
        });
    };

    const walkinSearchForm = useForm({ contact_number: flash?.walkin_search_number || '' });
    const submitWalkinSearch = (e) => {
        e.preventDefault();
        walkinSearchForm.post(route('admin.walkin.search'), { preserveScroll: true });
    };

    const walkinStoreForm = useForm({
        contact_number: '',
        is_new_user: '1',
        document_type_id: '',
        purpose: '',
        first_name: '',
        last_name: '',
        sex: '',
        date_of_birth: '',
        house_number: '',
        purok_street: '',
    });

    useEffect(() => {
        if (flash?.walkin_searched) {
            walkinStoreForm.setData((data) => ({
                ...data,
                contact_number: flash?.walkin_search_number || '',
                is_new_user: flash?.walkin_user ? '0' : '1',
            }));
        }
    }, [flash?.walkin_searched, flash?.walkin_search_number, flash?.walkin_user]);

    const submitWalkinStore = (e) => {
        e.preventDefault();
        walkinStoreForm.post(route('admin.walkin.store'), {
            preserveScroll: true,
            onSuccess: () => {
                walkinStoreForm.reset();
                walkinSearchForm.reset();
                setActiveTab('queue');
            },
        });
    };

    const passwordForm = useForm({ current_password: '', password: '', password_confirmation: '' });
    const submitPasswordUpdate = (e) => {
        e.preventDefault();
        passwordForm.post(route('password.update'), {
            preserveScroll: true,
            onSuccess: () => passwordForm.reset(),
        });
    };

    const [reportMonth, setReportMonth] = useState('all');
    const [reportYear, setReportYear] = useState(new Date().getFullYear().toString());

    // --- SEARCH STATE FOR LOGS ---
    const [auditSearch, setAuditSearch] = useState(filters?.audit_search || '');

    const handleAuditSearch = (e) => {
        e.preventDefault();
        router.get(route('admin.dashboard'), { audit_search: auditSearch }, { 
            preserveState: true, 
            preserveScroll: true,
            only: ['auditLogs', 'filters'] 
        });
    };
    // ==========================================
    // BUG FIX 5: Asynchronous Iframe Rendering Check!
    // ==========================================
    const openLogbook = async () => {
        setLogbookModalOpen(true);
        // Hintaying ma-render ang Iframe bago hanapin (100ms)
        setTimeout(() => {
            const url = route('admin.queue.print_logbook');
            document.getElementById('logbookViewerFrame').src = url;
            setLogbookUrl(url + '?download=1');
        }, 100);
    };

    const submitGeneratePdf = (e) => {
        e.preventDefault();
        const formTarget = e.target;
        setPdfModalOpen(true);
        // Hintaying lumitaw ang Iframe modal bago i-submit
        setTimeout(() => {
            formTarget.submit();
        }, 100);
    };

    const closePdfModal = () => {
        setPdfModalOpen(false);
        document.getElementById('pdfViewerFrame').src = 'about:blank';
    };

    const closeLogbookModal = () => {
        setLogbookModalOpen(false);
        document.getElementById('logbookViewerFrame').src = 'about:blank';
    };

    const handleSearchSort = (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        router.get(route('admin.dashboard'), Object.fromEntries(formData.entries()), {
            preserveState: true,
        });
    };

    const urlParams = new URLSearchParams(window.location.search);
    const searchParam = urlParams.get('search') || '';
    const sortParam = urlParams.get('sort') || 'latest';

    return (
        <AdminLayout activeTab={activeTab} setActiveTab={setActiveTab}>
            <Head title="Admin Dashboard - BDLS" />

            <div className="pointer-events-none fixed top-24 left-1/2 z-[60] flex w-full max-w-md -translate-x-1/2 transform flex-col gap-3 px-4">
                <div
                    className={`pointer-events-auto flex items-center gap-4 rounded-xl border-l-4 border-red-500 bg-slate-900 px-6 py-4 text-white shadow-2xl transition-all duration-500 ${localToast.visible ? 'translate-y-0 opacity-100' : '-translate-y-20 opacity-0'}`}
                >
                    <svg
                        className="h-6 w-6 shrink-0 text-red-500"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                        ></path>
                    </svg>
                    <div>
                        <p className="text-sm font-bold">Error</p>
                        <p className="text-sm font-medium">{localToast.message}</p>
                    </div>
                </div>
            </div>

            {/* --- TAB 2: QUEUE & PROCESSING --- */}
            {activeTab === 'queue' && (
                <div className="animate-in fade-in duration-500">
                    <div className="mt-6 mb-6 flex gap-2 overflow-x-auto pb-2">
                        <button
                            onClick={() => setQueueSubTab('queue-active')}
                            className={`rounded-full px-5 py-2 text-sm font-bold whitespace-nowrap transition-all ${queueSubTab === 'queue-active' ? 'border border-slate-900 bg-slate-900 text-white' : 'border border-transparent bg-slate-200 text-slate-700 hover:bg-slate-300'}`}
                        >
                            Active Queue ({activeQueue.data?.length || 0})
                        </button>
                        <button
                            onClick={() => setQueueSubTab('queue-received')}
                            className={`rounded-full px-5 py-2 text-sm font-bold whitespace-nowrap transition-all ${queueSubTab === 'queue-received' ? 'border border-red-300 bg-red-100 text-red-700' : 'border border-transparent bg-slate-200 text-slate-700 hover:bg-slate-300'}`}
                        >
                            Received History ({receivedQueue.data?.length || 0})
                        </button>
                    </div>

                    {queueSubTab === 'queue-active' && (
                        <div>
                            <div className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                                <table className="w-full border-collapse text-left">
                                    <thead>
                                        <tr className="border-b border-slate-200 bg-slate-50 text-[10px] tracking-[0.15em] text-slate-500 uppercase">
                                            <th className="p-4 font-black">Queue #</th>
                                            <th className="p-4 font-black">Residente</th>
                                            <th className="p-4 font-black">Dokumento</th>
                                            <th className="p-4 font-black">Status</th>
                                            <th className="p-4 text-right font-black">Aksyon</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-slate-100">
                                        {activeQueue.data && activeQueue.data.length > 0 ? (
                                            activeQueue.data.map((queue) => {
                                                const rawStatus = queue.status.toLowerCase();
                                                let nextStatus = '';
                                                let btnLabel = '';
                                                const interviewDocs = [1 - 8];
                                                if (rawStatus === 'pending') {
                                                    nextStatus = 'processing';
                                                    btnLabel = 'Process Request';
                                                } else if (rawStatus === 'processing') {
                                                    if (
                                                        interviewDocs.includes(
                                                            queue.document_type_id
                                                        )
                                                    ) {
                                                        nextStatus = 'for_interview';
                                                        btnLabel = 'Set for Interview';
                                                    } else {
                                                        nextStatus = 'released';
                                                        btnLabel = 'Release Document';
                                                    }
                                                } else if (rawStatus === 'for_interview') {
                                                    nextStatus = 'released';
                                                    btnLabel = 'Release Document';
                                                } else if (rawStatus === 'released') {
                                                    nextStatus = 'received';
                                                    btnLabel = 'Mark as Received';
                                                }

                                                let badgeClass = '';
                                                if (rawStatus === 'pending')
                                                    badgeClass =
                                                        'bg-yellow-100 text-yellow-700 border border-yellow-200';
                                                if (rawStatus === 'processing')
                                                    badgeClass =
                                                        'bg-blue-100 text-blue-700 border border-blue-200';
                                                if (rawStatus === 'for_interview')
                                                    badgeClass =
                                                        'bg-purple-100 text-purple-700 border border-purple-200';
                                                if (rawStatus === 'released')
                                                    badgeClass =
                                                        'bg-orange-100 text-orange-700 border border-orange-200';
                                                if (
                                                    rawStatus === 'rejected' ||
                                                    rawStatus === 'canceled'
                                                )
                                                    badgeClass =
                                                        'bg-red-100 text-red-700 border border-red-200';

                                                return (
                                                    <tr
                                                        key={queue.id}
                                                        className="transition-colors hover:bg-slate-50"
                                                    >
                                                        <td className="p-4 text-xl font-black tracking-tighter text-slate-900">
                                                            {queue.queue_number}
                                                        </td>
                                                        <td className="p-4">
                                                            <p className="mb-1 text-sm leading-none font-bold text-slate-900 uppercase">
                                                                {queue.user?.last_name},{' '}
                                                                {queue.user?.first_name}
                                                            </p>
                                                            <p className="font-mono text-[10px] tracking-tight text-slate-500">
                                                                {queue.user?.contact_number}
                                                            </p>
                                                        </td>
                                                        <td className="p-4 text-xs font-bold text-slate-700 uppercase">
                                                            {queue.document_type?.name ?? 'N/A'}
                                                        </td>
                                                        <td className="p-4">
                                                            <span
                                                                className={`rounded-full px-3 py-1 text-[9px] font-black tracking-widest uppercase shadow-sm ${badgeClass}`}
                                                            >
                                                                {rawStatus.replace('_', ' ')}
                                                            </span>
                                                        </td>
                                                        <td className="flex justify-end gap-2 p-4 text-right">
                                                            {btnLabel && (
                                                                <button
                                                                    onClick={() =>
                                                                        setStatusModal({
                                                                            isOpen: true,
                                                                            requestId: queue.id,
                                                                            nextStatus,
                                                                            label: btnLabel,
                                                                        })
                                                                    }
                                                                    className="rounded-lg bg-slate-900 px-4 py-2 text-[10px] font-black tracking-widest text-white uppercase shadow-sm transition-all hover:bg-slate-800 active:scale-95"
                                                                >
                                                                    {btnLabel}
                                                                </button>
                                                            )}
                                                            {(rawStatus === 'pending' ||
                                                                rawStatus === 'processing') && (
                                                                <button
                                                                    onClick={() =>
                                                                        setStatusModal({
                                                                            isOpen: true,
                                                                            requestId: queue.id,
                                                                            nextStatus: 'rejected',
                                                                            label: 'Reject Request',
                                                                        })
                                                                    }
                                                                    className="rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-[10px] font-black tracking-widest text-red-600 uppercase shadow-sm transition-all hover:bg-red-100 active:scale-95"
                                                                >
                                                                    Reject
                                                                </button>
                                                            )}
                                                            {!btnLabel &&
                                                                rawStatus !== 'pending' &&
                                                                rawStatus !== 'processing' && (
                                                                    <span className="mt-2 text-xs font-bold text-slate-400 italic">
                                                                        No Action
                                                                    </span>
                                                                )}
                                                        </td>
                                                    </tr>
                                                );
                                            })
                                        ) : (
                                            <tr>
                                                <td
                                                    colSpan="5"
                                                    className="p-12 text-center font-bold text-slate-400 italic"
                                                >
                                                    Walang aktibong nakapila.
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>
                            <Pagination links={activeQueue.links} />
                        </div>
                    )}

                    {queueSubTab === 'queue-received' && (
                        <div>
                            <div className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                                <div className="flex items-center justify-between border-b border-slate-100 bg-slate-50 p-4">
                                    <h3 className="text-sm font-bold tracking-widest text-slate-800 uppercase">
                                        Released Records
                                    </h3>
                                    <button
                                        onClick={openLogbook}
                                        className="flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-[10px] font-black tracking-widest text-white uppercase shadow-sm transition-all hover:bg-slate-800 active:scale-95"
                                    >
                                        <svg
                                            className="h-4 w-4"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth="2"
                                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"
                                            ></path>
                                        </svg>
                                        Tingnan ang Logbook
                                    </button>
                                </div>
                                <table className="w-full border-collapse text-left">
                                    <thead>
                                        <tr className="border-b border-slate-200 bg-slate-50 text-[10px] tracking-[0.15em] text-slate-500 uppercase">
                                            <th className="p-4 font-black">Queue #</th>
                                            <th className="p-4 font-black">Residente</th>
                                            <th className="p-4 font-black">Dokumento</th>
                                            <th className="p-4 font-black">Date Released</th>
                                            <th className="p-4 text-right font-black">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-slate-100">
                                        {receivedQueue.data && receivedQueue.data.length > 0 ? (
                                            receivedQueue.data.map((queue) => (
                                                <tr key={queue.id} className="bg-slate-50/50">
                                                    <td className="p-4 text-xl font-black tracking-tighter text-slate-400">
                                                        {queue.queue_number}
                                                    </td>
                                                    <td className="p-4 opacity-75">
                                                        <p className="mb-1 text-sm leading-none font-bold text-slate-900 uppercase">
                                                            {queue.user?.last_name},{' '}
                                                            {queue.user?.first_name}
                                                        </p>
                                                        <p className="font-mono text-[10px] text-slate-500">
                                                            {queue.user?.contact_number}
                                                        </p>
                                                    </td>
                                                    <td className="p-4 text-xs font-bold text-slate-600 uppercase">
                                                        {queue.document_type?.name ?? 'N/A'}
                                                    </td>
                                                    <td className="p-4 text-xs font-bold text-slate-500">
                                                        {queue.released_at
                                                            ? new Date(
                                                                  queue.released_at
                                                              ).toLocaleString()
                                                            : 'N/A'}
                                                    </td>
                                                    <td className="p-4 text-right">
                                                        <span
                                                            className={`rounded-full border px-3 py-1 text-[9px] font-black tracking-widest uppercase ${queue.status === 'received' ? 'border-green-200 bg-green-100 text-green-700' : 'border-red-200 bg-red-100 text-red-700'}`}
                                                        >
                                                            {queue.status}
                                                        </span>
                                                    </td>
                                                </tr>
                                            ))
                                        ) : (
                                            <tr>
                                                <td
                                                    colSpan="5"
                                                    className="p-12 text-center font-bold text-slate-400 italic"
                                                >
                                                    Walang record ng release history.
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>
                            <Pagination links={receivedQueue.links} />
                        </div>
                    )}
                </div>
            )}

            {/* --- TAB 3: WALK-IN --- */}
            {activeTab === 'walkin' && (
                <div className="animate-in fade-in duration-500">
                    <div className="mx-auto max-w-3xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">
                        <div className="mb-6 text-center">
                            <div className="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-900">
                                <svg
                                    className="h-7 w-7"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                    ></path>
                                </svg>
                            </div>
                            <h2 className="text-2xl font-black tracking-tight text-slate-900 uppercase">
                                Walk-in Search
                            </h2>
                            <p className="mt-1 text-sm font-medium text-slate-500">
                                Hanapin ang contact number bago gumawa ng request.
                            </p>
                        </div>

                        <form
                            onSubmit={submitWalkinSearch}
                            className="flex flex-col gap-3 sm:flex-row"
                        >
                            <div className="flex-1">
                                <input
                                    type="text"
                                    value={walkinSearchForm.data.contact_number}
                                    onChange={(e) =>
                                        walkinSearchForm.setData(
                                            'contact_number',
                                            e.target.value.replace(/[^0-9]/g, '')
                                        )
                                    }
                                    required
                                    placeholder="09XXXXXXXXX"
                                    maxLength="11"
                                    className="w-full rounded-lg border border-slate-300 px-5 py-3 text-center font-mono text-xl font-bold tracking-widest transition-all outline-none focus:ring-2 focus:ring-slate-900 sm:text-left"
                                />
                            </div>
                            <button
                                type="submit"
                                disabled={walkinSearchForm.processing}
                                className="shrink-0 rounded-lg bg-slate-900 px-8 py-3 text-xs font-black tracking-widest whitespace-nowrap text-white uppercase shadow-sm transition-all hover:bg-slate-800 active:scale-95 disabled:opacity-50"
                            >
                                I-Search
                            </button>
                        </form>

                        {flash?.walkin_searched && (
                            <div className="mt-8 border-t border-slate-100 pt-6">
                                {flash?.walkin_user ? (
                                    <form
                                        onSubmit={submitWalkinStore}
                                        className="rounded-xl border border-green-200 bg-green-50 p-5"
                                    >
                                        <div className="mb-4">
                                            <p className="mb-1 text-[10px] font-black tracking-widest text-green-600 uppercase">
                                                Record Found
                                            </p>
                                            <p className="mb-1 text-lg leading-none font-bold text-slate-900 uppercase">
                                                {flash.walkin_user.last_name},{' '}
                                                {flash.walkin_user.first_name}
                                            </p>
                                            <p className="text-xs font-bold text-slate-500">
                                                {flash.walkin_user.sex} | {flash.walkin_user.age}{' '}
                                                YRS OLD
                                            </p>
                                        </div>
                                        <div className="flex flex-col gap-3 sm:flex-row">
                                            <select
                                                value={walkinStoreForm.data.document_type_id}
                                                onChange={(e) =>
                                                    walkinStoreForm.setData(
                                                        'document_type_id',
                                                        e.target.value
                                                    )
                                                }
                                                required
                                                className="min-w-0 flex-1 rounded-lg border border-green-300 bg-white px-4 py-3 text-sm font-bold text-slate-700 outline-none focus:ring-2 focus:ring-green-600"
                                            >
                                                <option value="">-- Piliin ang Dokumento --</option>
                                                {documents.map((doc) => (
                                                    <option key={doc.id} value={doc.id}>
                                                        {doc.name}
                                                    </option>
                                                ))}
                                            </select>
                                            <input
                                                type="text"
                                                value={walkinStoreForm.data.purpose}
                                                onChange={(e) =>
                                                    walkinStoreForm.setData(
                                                        'purpose',
                                                        e.target.value
                                                    )
                                                }
                                                placeholder="Layunin (Purpose)"
                                                required
                                                className="min-w-0 flex-1 rounded-lg border border-green-300 bg-white px-4 py-3 text-sm font-bold text-slate-700 outline-none focus:ring-2 focus:ring-green-600"
                                            />
                                            <button
                                                type="submit"
                                                disabled={walkinStoreForm.processing}
                                                className="shrink-0 rounded-lg bg-green-600 px-6 py-3 text-[10px] font-black tracking-widest whitespace-nowrap text-white uppercase shadow-sm transition-all hover:bg-green-700 active:scale-95 disabled:opacity-50"
                                            >
                                                Create Request
                                            </button>
                                        </div>
                                    </form>
                                ) : (
                                    <form
                                        onSubmit={submitWalkinStore}
                                        className="rounded-xl border border-amber-200 bg-amber-50 p-5 md:p-6"
                                    >
                                        <p className="mb-3 text-[10px] font-black tracking-widest text-amber-600 uppercase">
                                            Walang Record: I-rehistro bilang Walk-in
                                        </p>
                                        <div className="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                            <input
                                                type="text"
                                                value={walkinStoreForm.data.first_name}
                                                onChange={(e) =>
                                                    walkinStoreForm.setData(
                                                        'first_name',
                                                        e.target.value
                                                    )
                                                }
                                                placeholder="First Name *"
                                                required
                                                className="w-full rounded-lg border border-amber-300 bg-white px-4 py-2.5 text-sm font-bold outline-none focus:ring-2 focus:ring-amber-600"
                                            />
                                            <input
                                                type="text"
                                                value={walkinStoreForm.data.last_name}
                                                onChange={(e) =>
                                                    walkinStoreForm.setData(
                                                        'last_name',
                                                        e.target.value
                                                    )
                                                }
                                                placeholder="Last Name *"
                                                required
                                                className="w-full rounded-lg border border-amber-300 bg-white px-4 py-2.5 text-sm font-bold outline-none focus:ring-2 focus:ring-amber-600"
                                            />
                                            <select
                                                value={walkinStoreForm.data.sex}
                                                onChange={(e) =>
                                                    walkinStoreForm.setData('sex', e.target.value)
                                                }
                                                required
                                                className="w-full rounded-lg border border-amber-300 bg-white px-4 py-2.5 text-sm font-bold outline-none focus:ring-2 focus:ring-amber-600"
                                            >
                                                <option value="">-- Kasarian * --</option>
                                                <option value="Male">Lalaki</option>
                                                <option value="Female">Babae</option>
                                            </select>
                                            <div className="flex items-center rounded-lg border border-amber-300 bg-white px-3 focus-within:ring-2 focus-within:ring-amber-600">
                                                <span className="mr-2 text-xs font-bold tracking-widest text-slate-400 uppercase">
                                                    DOB*
                                                </span>
                                                <input
                                                    type="date"
                                                    value={walkinStoreForm.data.date_of_birth}
                                                    onChange={(e) =>
                                                        walkinStoreForm.setData(
                                                            'date_of_birth',
                                                            e.target.value
                                                        )
                                                    }
                                                    required
                                                    className="w-full bg-transparent py-2.5 text-sm font-bold outline-none"
                                                />
                                            </div>
                                            <input
                                                type="text"
                                                value={walkinStoreForm.data.house_number}
                                                onChange={(e) =>
                                                    walkinStoreForm.setData(
                                                        'house_number',
                                                        e.target.value
                                                    )
                                                }
                                                placeholder="House No. *"
                                                required
                                                className="w-full rounded-lg border border-amber-300 bg-white px-4 py-2.5 text-sm font-bold outline-none focus:ring-2 focus:ring-amber-600"
                                            />
                                            <input
                                                type="text"
                                                value={walkinStoreForm.data.purok_street}
                                                onChange={(e) =>
                                                    walkinStoreForm.setData(
                                                        'purok_street',
                                                        e.target.value
                                                    )
                                                }
                                                placeholder="Purok/Street *"
                                                required
                                                className="w-full rounded-lg border border-amber-300 bg-white px-4 py-2.5 text-sm font-bold outline-none focus:ring-2 focus:ring-amber-600"
                                            />
                                        </div>
                                        <div className="mt-2 flex flex-col gap-3 border-t border-amber-200 pt-4 sm:flex-row">
                                            <select
                                                value={walkinStoreForm.data.document_type_id}
                                                onChange={(e) =>
                                                    walkinStoreForm.setData(
                                                        'document_type_id',
                                                        e.target.value
                                                    )
                                                }
                                                required
                                                className="min-w-0 flex-1 rounded-lg border border-amber-300 bg-white px-4 py-3 text-sm font-bold text-slate-700 outline-none focus:ring-2 focus:ring-amber-600"
                                            >
                                                <option value="">-- Piliin ang Dokumento --</option>
                                                {documents.map((doc) => (
                                                    <option key={doc.id} value={doc.id}>
                                                        {doc.name}
                                                    </option>
                                                ))}
                                            </select>
                                            <input
                                                type="text"
                                                value={walkinStoreForm.data.purpose}
                                                onChange={(e) =>
                                                    walkinStoreForm.setData(
                                                        'purpose',
                                                        e.target.value
                                                    )
                                                }
                                                placeholder="Layunin (Purpose)"
                                                required
                                                className="min-w-0 flex-1 rounded-lg border border-amber-300 bg-white px-4 py-3 text-sm font-bold text-slate-700 outline-none focus:ring-2 focus:ring-amber-600"
                                            />
                                            <button
                                                type="submit"
                                                disabled={walkinStoreForm.processing}
                                                className="shrink-0 rounded-lg bg-amber-600 px-6 py-3 text-[10px] font-black tracking-widest whitespace-nowrap text-white uppercase shadow-sm transition-all hover:bg-amber-700 active:scale-95 disabled:opacity-50"
                                            >
                                                Register & Create
                                            </button>
                                        </div>
                                    </form>
                                )}
                            </div>
                        )}
                    </div>
                </div>
            )}

            {/* --- TAB 4: ANNOUNCEMENTS --- */}
            {activeTab === 'announcements' && (
                <div className="animate-in fade-in duration-500">
                    <div className="mx-auto mt-8 max-w-3xl">
                        <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">
                            <div className="mb-2 flex items-center gap-3">
                                <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-900 text-white">
                                    <svg
                                        className="h-5 w-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth="2"
                                            d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"
                                        ></path>
                                    </svg>
                                </div>
                                <h2 className="text-2xl font-black tracking-tight text-slate-900 uppercase">
                                    Broadcast Announcement
                                </h2>
                            </div>
                            <p className="mb-6 border-b border-slate-100 pb-6 text-sm font-medium text-slate-500">
                                Magpadala ng text blast sa lahat ng verified residents ng Barangay.{' '}
                                <br />
                                <span className="font-bold text-amber-600">
                                    Ayon sa NTC: Bawal ang links at bawal mag-send mula 9:00 PM
                                    hanggang 7:00 AM.
                                </span>
                            </p>

                            {isCurfew || errors.curfew ? (
                                <div className="mb-6 rounded-r-xl border-l-4 border-red-500 bg-red-50 p-4 shadow-sm">
                                    <div className="mb-1 flex items-center gap-2 text-xs font-black tracking-widest text-red-700 uppercase">
                                        <svg
                                            className="h-5 w-5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth="2"
                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                                            ></path>
                                        </svg>
                                        NTC SMS Curfew Active
                                    </div>
                                    <p className="text-sm font-bold text-red-600">
                                        Pansamantalang naka-disable ang Broadcast (9:00 PM - 7:00
                                        AM) upang sumunod sa Anti-Spam rules. Subukan muli bukas.
                                    </p>
                                </div>
                            ) : null}

                            <form onSubmit={submitAnnouncement}>
                                <div className="mb-4">
                                    <label className="mb-2 block text-[10px] font-black tracking-widest text-slate-400 uppercase">
                                        Mensahe (Message Body){' '}
                                        <span className="text-red-500">*</span>
                                    </label>
                                    <textarea
                                        value={announcementForm.data.message_body}
                                        onChange={handleAnnouncementChange}
                                        rows="5"
                                        required
                                        disabled={isCurfew}
                                        placeholder={
                                            isCurfew
                                                ? 'Naka-disable ang pag-type tuwing curfew...'
                                                : 'I-type ang iyong anunsyo dito...'
                                        }
                                        className={`w-full rounded-xl border px-4 py-3 ${announcementForm.errors.message_body || isLinkDetected ? 'border-red-500 bg-red-50 ring-1 ring-red-500' : 'border-slate-300 bg-slate-50'} resize-none font-medium text-slate-800 transition-all outline-none focus:ring-2 focus:ring-slate-900 ${isCurfew ? 'cursor-not-allowed bg-slate-100 opacity-60' : ''}`}
                                    ></textarea>

                                    {announcementForm.errors.message_body && (
                                        <p className="mt-2 text-xs font-bold text-red-600">
                                            {announcementForm.errors.message_body}
                                        </p>
                                    )}

                                    <div className="mt-2 flex items-start justify-between">
                                        <p
                                            className={`mt-1 text-xs font-bold text-red-600 ${isLinkDetected ? 'block' : 'hidden'}`}
                                        >
                                            ⚠️ Bawal mag-send ng links (http/www).
                                        </p>
                                        <p
                                            className={`mt-1 ml-auto text-xs font-bold ${charCount > 160 ? 'text-amber-600' : 'text-slate-500'}`}
                                        >
                                            Characters: {charCount}/160 (Est. {credits} Credit/s per
                                            user)
                                        </p>
                                    </div>
                                </div>

                                <div className="flex justify-end pt-2">
                                    <button
                                        type="submit"
                                        disabled={
                                            isCurfew ||
                                            isLinkDetected ||
                                            announcementForm.processing
                                        }
                                        className={`flex items-center gap-2 rounded-xl bg-slate-900 px-8 py-3.5 text-xs font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-slate-800 ${isCurfew || isLinkDetected || announcementForm.processing ? 'cursor-not-allowed opacity-50 hover:bg-slate-900 active:scale-100' : 'active:scale-95'}`}
                                    >
                                        {announcementForm.processing
                                            ? 'Sending...'
                                            : 'Send Broadcast'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            )}

            {/* --- TAB 5: AUDIT LOGS --- */}
            {activeTab === 'audit' && (
                <div className="animate-in fade-in duration-500">
                    <div>
                            <div className="block overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                                <div className="flex flex-col items-start justify-between gap-4 border-b border-slate-200 bg-slate-50 p-6 sm:flex-row sm:items-center">
                                    <h2 className="text-xl font-black tracking-tight text-slate-900 uppercase">
                                        System Audit Logs
                                    </h2>
                                    <form onSubmit={handleAuditSearch} className="flex w-full sm:w-auto items-center gap-2">
                                        <input 
                                            type="text" 
                                            placeholder="I-search ang logs..." 
                                            value={auditSearch}
                                            onChange={(e) => setAuditSearch(e.target.value)}
                                            className="w-full sm:w-64 rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 outline-none focus:border-slate-500 focus:ring-2 focus:ring-slate-500"
                                        />
                                        <button type="submit" className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-black text-white shadow-sm transition-all hover:bg-slate-800">
                                            Hanapin
                                        </button>
                                    </form>
                                </div>
                                <div className="max-h-[600px] overflow-x-auto overflow-y-auto">
                                    <table className="relative w-full border-collapse text-left">
                                        <thead className="sticky top-0 z-10">
                                            <tr className="border-b border-slate-200 bg-slate-100 text-[10px] tracking-[0.15em] text-slate-500 uppercase">
                                                <th className="p-4 font-black">Petsa & Oras</th>
                                                <th className="p-4 font-black">Admin</th>
                                                <th className="p-4 font-black">Aksyon</th>
                                                <th className="w-1/2 p-4 font-black">Detalye</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-slate-100">
                                            {auditLogs.data && auditLogs.data.length > 0 ? (
                                                auditLogs.data.map((log) => (
                                                    <tr
                                                        key={log.id}
                                                        className="transition-colors hover:bg-slate-50"
                                                    >
                                                        <td className="p-4 text-xs font-bold text-slate-600">
                                                            {new Date(
                                                                log.created_at
                                                            ).toLocaleString()}
                                                        </td>
                                                        <td className="p-4 text-xs font-bold text-slate-900 uppercase">
                                                            {log.admin?.first_name || 'System'}{' '}
                                                            {log.admin?.last_name || ''}
                                                        </td>
                                                        <td className="p-4">
                                                            <span className="rounded bg-slate-900 px-2 py-1 text-[9px] font-black tracking-widest text-white uppercase shadow-sm">
                                                                {log.action}
                                                            </span>
                                                        </td>
                                                        <td className="p-4 text-xs font-medium text-slate-600">
                                                            {log.description}
                                                        </td>
                                                    </tr>
                                                ))
                                            ) : (
                                                <tr>
                                                    <td
                                                        colSpan="4"
                                                        className="p-12 text-center font-bold text-slate-400 italic"
                                                    >
                                                        Wala pang naitalang galaw sa system.
                                                    </td>
                                                </tr>
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <Pagination links={auditLogs.links} />
                        </div>
                    </div>
            )}

            {/* --- TAB 5.5: LIVE ANALYTICS --- */}
            {activeTab === 'analytics' && (
                <div className="animate-in fade-in duration-500">
                    <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div className="flex flex-col items-start justify-between gap-4 border-b border-slate-200 bg-slate-50 p-6 sm:flex-row sm:items-center">
                            <h2 className="text-xl font-black tracking-tight text-slate-900 uppercase">
                                Live System Analytics
                            </h2>
                            {/* FALLBACK: PDF Generation Form */}
                            <form
                                method="POST"
                                action={route('admin.reports.generate')}
                                target="pdfViewerFrame"
                                onSubmit={submitGeneratePdf}
                                className="flex items-center gap-2"
                            >
                                <input type="hidden" name="_token" value={usePage().props.csrf_token} />
                                <input type="hidden" name="report_month" value={reportMonth} />
                                <input type="hidden" name="report_year" value={reportYear} />
                                <button
                                    type="submit"
                                    className="flex items-center gap-2 rounded-xl bg-slate-900 px-5 py-2.5 text-[10px] font-black tracking-widest text-white uppercase shadow-sm transition-all hover:bg-slate-800 active:scale-95"
                                >
                                    <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    I-Print as PDF
                                </button>
                            </form>
                        </div>

                        <div className="p-6">
                            {/* LIVE FILTERS */}
                            <div className="mb-8 flex flex-col items-end gap-4 rounded-xl border border-slate-100 bg-slate-50 p-5 sm:flex-row">
                                <div className="w-full sm:w-1/3">
                                    <label className="mb-2 block text-[10px] font-black tracking-widest text-slate-500 uppercase">Piliin ang Buwan</label>
                                    <select value={reportMonth} onChange={(e) => setReportMonth(e.target.value)} className="w-full cursor-pointer rounded-lg border border-slate-300 bg-white px-4 py-2.5 font-bold text-slate-700 outline-none focus:ring-2 focus:ring-red-600">
                                        <option value="all">Buong Taon (All Months)</option>
                                        {['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'].map((m, i) => (
                                            <option key={i} value={(i + 1).toString().padStart(2, '0')}>{m}</option>
                                        ))}
                                    </select>
                                </div>
                                <div className="w-full sm:w-1/3">
                                    <label className="mb-2 block text-[10px] font-black tracking-widest text-slate-500 uppercase">Piliin ang Taon</label>
                                    <select value={reportYear} onChange={(e) => setReportYear(e.target.value)} className="w-full cursor-pointer rounded-lg border border-slate-300 bg-white px-4 py-2.5 font-bold text-slate-700 outline-none focus:ring-2 focus:ring-red-600">
                                        {Array.from({ length: new Date().getFullYear() - 2023 }, (_, i) => new Date().getFullYear() - i).map((y) => (
                                            <option key={y} value={y}>{y}</option>
                                        ))}
                                    </select>
                                </div>
                                <div className="w-full sm:w-1/3">
                                    <button
                                        onClick={() => {
                                            router.get(route('admin.dashboard'), { analytics_month: reportMonth, analytics_year: reportYear, search: searchParam, sort: sortParam }, { preserveState: true, preserveScroll: true, only: ['analyticsSummary', 'filters'] });
                                        }}
                                        className="w-full rounded-lg bg-red-600 px-6 py-3 text-xs font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-red-700 active:scale-95"
                                    >
                                        I-Filter ang Data
                                    </button>
                                </div>
                            </div>

                            {/* DASHBOARD STATS CARDS */}
                            <div className="mb-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
                                <div className="rounded-xl border border-slate-100 bg-white p-5 shadow-sm text-center transition-all hover:-translate-y-1 hover:shadow-md">
                                    <p className="text-[10px] font-black tracking-widest text-slate-400 uppercase">Total Requests</p>
                                    <p className="mt-2 text-4xl font-black text-slate-900">{analyticsSummary?.total || 0}</p>
                                </div>
                                <div className="rounded-xl border border-green-100 bg-green-50 p-5 shadow-sm text-center transition-all hover:-translate-y-1 hover:shadow-md">
                                    <p className="text-[10px] font-black tracking-widest text-green-600 uppercase">Released</p>
                                    <p className="mt-2 text-4xl font-black text-green-700">{analyticsSummary?.released || 0}</p>
                                </div>
                                <div className="rounded-xl border border-blue-100 bg-blue-50 p-5 shadow-sm text-center transition-all hover:-translate-y-1 hover:shadow-md">
                                    <p className="text-[10px] font-black tracking-widest text-blue-600 uppercase">Processing</p>
                                    <p className="mt-2 text-4xl font-black text-blue-700">{analyticsSummary?.processing || 0}</p>
                                </div>
                                <div className="rounded-xl border border-red-100 bg-red-50 p-5 shadow-sm text-center transition-all hover:-translate-y-1 hover:shadow-md">
                                    <p className="text-[10px] font-black tracking-widest text-red-600 uppercase">Rejected / Canceled</p>
                                    <p className="mt-2 text-4xl font-black text-red-700">{analyticsSummary?.rejected || 0}</p>
                                </div>
                            </div>

                            {/* RECHARTS GRAPHS */}
                            <div className="grid grid-cols-1 gap-8 lg:grid-cols-2">
                                {/* Bar Chart: Online vs Walkin */}
                                <div className="rounded-xl border border-slate-100 bg-white p-6 shadow-sm">
                                    <h3 className="mb-6 text-xs font-black tracking-widest text-slate-500 uppercase text-center">Service Channel Usage</h3>
                                    <div className="h-64 w-full">
                                        <ResponsiveContainer width="100%" height="100%">
                                            <BarChart data={[
                                                { name: 'Online', count: analyticsSummary?.online || 0 },
                                                { name: 'Walk-in', count: analyticsSummary?.walkin || 0 }
                                            ]} margin={{ top: 10, right: 30, left: 0, bottom: 0 }}>
                                                <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#e2e8f0" />
                                                <XAxis dataKey="name" tick={{ fontSize: 12, fill: '#64748b', fontWeight: 'bold' }} axisLine={false} tickLine={false} />
                                                <YAxis allowDecimals={false} tick={{ fontSize: 12, fill: '#64748b' }} axisLine={false} tickLine={false} />
                                                <Tooltip cursor={{ fill: '#f8fafc' }} contentStyle={{ borderRadius: '12px', border: 'none', boxShadow: '0 4px 6px -1px rgb(0 0 0 / 0.1)', fontWeight: 'bold' }} />
                                                <Bar dataKey="count" radius={[1]} maxBarSize={60}>
                                                    {
                                                        [
                                                            { name: 'Online', count: analyticsSummary?.online || 0 },
                                                            { name: 'Walk-in', count: analyticsSummary?.walkin || 0 }
                                                        ].map((entry, index) => (
                                                            <Cell key={`cell-${index}`} fill={index === 0 ? '#ef4444' : '#0f172a'} />
                                                        ))
                                                    }
                                                </Bar>
                                            </BarChart>
                                        </ResponsiveContainer>
                                    </div>
                                </div>

                                {/* Pie Chart: Status Distribution */}
                                <div className="rounded-xl border border-slate-100 bg-white p-6 shadow-sm">
                                    <h3 className="mb-6 text-xs font-black tracking-widest text-slate-500 uppercase text-center">Document Status Distribution</h3>
                                    <div className="h-64 w-full">
                                        <ResponsiveContainer width="100%" height="100%">
                                            <PieChart>
                                                <Pie
                                                    data={[
                                                        { name: 'Pending', value: analyticsSummary?.pending || 0, color: '#f59e0b' },
                                                        { name: 'Processing', value: analyticsSummary?.processing || 0, color: '#3b82f6' },
                                                        { name: 'Released', value: analyticsSummary?.released || 0, color: '#22c55e' },
                                                        { name: 'Rejected', value: analyticsSummary?.rejected || 0, color: '#ef4444' },
                                                    ].filter(d => d.value > 0)}
                                                    cx="50%"
                                                    cy="50%"
                                                    innerRadius={65}
                                                    outerRadius={90}
                                                    paddingAngle={5}
                                                    dataKey="value"
                                                    stroke="none"
                                                >
                                                    {
                                                        [
                                                            { name: 'Pending', value: analyticsSummary?.pending || 0, color: '#f59e0b' },
                                                            { name: 'Processing', value: analyticsSummary?.processing || 0, color: '#3b82f6' },
                                                            { name: 'Released', value: analyticsSummary?.released || 0, color: '#22c55e' },
                                                            { name: 'Rejected', value: analyticsSummary?.rejected || 0, color: '#ef4444' },
                                                        ].filter(d => d.value > 0).map((entry, index) => (
                                                            <Cell key={`cell-${index}`} fill={entry.color} />
                                                        ))
                                                    }
                                                </Pie>
                                                <Tooltip contentStyle={{ borderRadius: '12px', border: 'none', boxShadow: '0 4px 6px -1px rgb(0 0 0 / 0.1)', fontWeight: 'bold' }} />
                                                <Legend iconType="circle" wrapperStyle={{ fontSize: '12px', fontWeight: 'bold', paddingTop: '20px' }} />
                                            </PieChart>
                                        </ResponsiveContainer>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            )}
            {/* --- TAB 6: SETTINGS (PHASE 5 COMPLETED) --- */}
            {activeTab === 'settings' && (
                <div className="animate-in fade-in duration-500">
                    <div className="mx-auto mt-8 max-w-2xl">
                        <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">
                            <div className="mb-6 flex items-center gap-3 border-b border-slate-100 pb-4">
                                <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-white shadow-sm">
                                    <svg
                                        className="h-5 w-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                        ></path>
                                    </svg>
                                </div>
                                <h2 className="text-2xl font-black tracking-tight text-slate-900 uppercase">
                                    Security Settings
                                </h2>
                            </div>
                            <p className="mb-6 text-sm font-medium text-slate-500">
                                Mahalaga: Palitan agad ang iyong default password upang maiwasan ang
                                unauthorized access sa Admin Portal.
                            </p>

                            <form onSubmit={submitPasswordUpdate} className="space-y-5">
                                <div>
                                    <label className="mb-2 block text-[10px] font-black tracking-widest text-slate-400 uppercase">
                                        Kasalukuyang Password{' '}
                                        <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="password"
                                        value={passwordForm.data.current_password}
                                        onChange={(e) =>
                                            passwordForm.setData('current_password', e.target.value)
                                        }
                                        required
                                        className={`w-full rounded-xl border bg-slate-50 px-4 py-3 font-bold text-slate-700 focus:ring-2 focus:ring-slate-900 focus:outline-none ${passwordForm.errors.current_password ? 'border-red-500 ring-1 ring-red-500' : 'border-slate-300'}`}
                                    />
                                    {passwordForm.errors.current_password && (
                                        <p className="mt-1 text-xs text-red-500">
                                            {passwordForm.errors.current_password}
                                        </p>
                                    )}
                                </div>
                                <div className="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                    <div>
                                        <label className="mb-2 block text-[10px] font-black tracking-widest text-slate-400 uppercase">
                                            Bagong Password <span className="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="password"
                                            value={passwordForm.data.password}
                                            onChange={(e) =>
                                                passwordForm.setData('password', e.target.value)
                                            }
                                            required
                                            minLength="8"
                                            className={`w-full rounded-xl border bg-slate-50 px-4 py-3 font-bold text-slate-700 focus:ring-2 focus:ring-slate-900 focus:outline-none ${passwordForm.errors.password ? 'border-red-500 ring-1 ring-red-500' : 'border-slate-300'}`}
                                        />
                                        {passwordForm.errors.password && (
                                            <p className="mt-1 text-xs text-red-500">
                                                {passwordForm.errors.password}
                                            </p>
                                        )}
                                    </div>
                                    <div>
                                        <label className="mb-2 block text-[10px] font-black tracking-widest text-slate-400 uppercase">
                                            I-type Ulit (Confirm){' '}
                                            <span className="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="password"
                                            value={passwordForm.data.password_confirmation}
                                            onChange={(e) =>
                                                passwordForm.setData(
                                                    'password_confirmation',
                                                    e.target.value
                                                )
                                            }
                                            required
                                            minLength="8"
                                            className={`w-full rounded-xl border bg-slate-50 px-4 py-3 font-bold text-slate-700 focus:ring-2 focus:ring-slate-900 focus:outline-none ${passwordForm.errors.password_confirmation ? 'border-red-500 ring-1 ring-red-500' : 'border-slate-300'}`}
                                        />
                                    </div>
                                </div>
                                <div className="flex justify-end border-t border-slate-100 pt-4">
                                    <button
                                        type="submit"
                                        disabled={passwordForm.processing}
                                        className="flex items-center gap-2 rounded-xl bg-slate-900 px-8 py-3.5 text-xs font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-slate-800 active:scale-95 disabled:opacity-50"
                                    >
                                        <svg
                                            className="h-4 w-4"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth="2"
                                                d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"
                                            ></path>
                                        </svg>
                                        {passwordForm.processing
                                            ? 'Sinasave...'
                                            : 'I-save ang Bagong Password'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            )}

            {/* --- MODALS --- */}

            {/* Status Modal */}
            {statusModal.isOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/80 p-4 backdrop-blur-sm">
                    <div className="w-full max-w-sm transform overflow-hidden rounded-2xl border border-slate-100 bg-white p-6 shadow-2xl transition-all">
                        <div className="mb-6 flex flex-col items-center text-center">
                            <div className="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-900">
                                <svg
                                    className="h-8 w-8"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                    ></path>
                                </svg>
                            </div>
                            <h3 className="text-xl font-black tracking-tight text-slate-900 uppercase">
                                {statusModal.nextStatus === 'rejected' ? (
                                    <span className="text-red-600">Reject Request?</span>
                                ) : (
                                    <>
                                        Move to{' '}
                                        <span className="text-blue-600">{statusModal.label}</span>?
                                    </>
                                )}
                            </h3>
                            <p className="mt-2 px-4 text-sm font-medium text-slate-500">
                                Sigurado ka bang gusto mong i-update ang status ng request na ito?
                            </p>
                        </div>
                        <form onSubmit={submitStatus}>
                            <div className="flex flex-col gap-3">
                                <button
                                    type="button"
                                    onClick={() =>
                                        setStatusModal({
                                            isOpen: false,
                                            requestId: null,
                                            nextStatus: '',
                                            label: '',
                                        })
                                    }
                                    className="w-full rounded-xl border border-slate-200 bg-white py-3 text-[10px] font-black tracking-widest text-slate-500 uppercase shadow-sm transition-all hover:bg-slate-50 hover:text-slate-900 active:scale-95"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    disabled={statusForm.processing}
                                    className={`w-full rounded-xl py-3 text-xs font-black tracking-widest text-white uppercase shadow-md transition-all active:scale-95 disabled:opacity-50 ${statusModal.nextStatus === 'rejected' ? 'bg-red-600 hover:bg-red-700' : 'bg-slate-900 hover:bg-slate-800'}`}
                                >
                                    {statusForm.processing
                                        ? 'Updating...'
                                        : statusModal.nextStatus === 'rejected'
                                          ? 'Reject Request'
                                          : 'Confirm Update'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* PDF Viewers */}
            {pdfModalOpen && (
                <div className="fixed inset-0 z-[9] flex items-center justify-center bg-slate-900/90 p-4 backdrop-blur-sm transition-opacity sm:p-8">
                    <div className="flex h-[90vh] w-full max-w-5xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
                        <div className="flex items-center justify-between border-b border-slate-200 bg-slate-50 p-4">
                            <h2 className="flex items-center gap-2 text-lg font-black tracking-tight text-slate-900 uppercase">
                                <svg
                                    className="h-5 w-5 text-red-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                    ></path>
                                </svg>
                                System Analytics Report
                            </h2>
                            <button
                                onClick={closePdfModal}
                                className="text-3xl leading-none font-bold text-slate-400 transition-all hover:text-red-600"
                            >
                                &times;
                            </button>
                        </div>
                        <div className="relative w-full flex-1 bg-slate-200">
                            {/* SPA iframe trick to load Laravel PDF directly */}
                            <iframe
                                name="pdfViewerFrame"
                                id="pdfViewerFrame"
                                className="relative z-10 h-full w-full bg-white"
                            ></iframe>
                        </div>
                        <div className="flex items-center justify-between border-t border-slate-200 bg-slate-50 p-4">
                            {/* Form para i-force download ang current generated PDF parameters */}
                            <form
                                method="POST"
                                action={route('admin.reports.generate')}
                                target="_blank"
                            >
                                <input
                                    type="hidden"
                                    name="_token"
                                    value={usePage().props.csrf_token}
                                />
                                <input type="hidden" name="report_month" value={reportMonth} />
                                <input type="hidden" name="report_year" value={reportYear} />
                                <input type="hidden" name="is_download" value="1" />
                                <button
                                    type="submit"
                                    className="flex items-center gap-2 rounded-xl bg-red-600 px-6 py-3 text-[10px] font-black tracking-widest text-white uppercase shadow-sm transition-all hover:bg-red-700 active:scale-95"
                                >
                                    <svg
                                        className="h-4 w-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                                        ></path>
                                    </svg>
                                    I-Download ang PDF
                                </button>
                            </form>
                            <button
                                onClick={closePdfModal}
                                className="rounded-xl bg-slate-900 px-8 py-3 text-[10px] font-black tracking-widest text-white uppercase shadow-sm transition-all hover:bg-slate-800 active:scale-95"
                            >
                                Isara
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {logbookModalOpen && (
                <div className="fixed inset-0 z-[9] flex items-center justify-center bg-slate-900/90 p-4 backdrop-blur-sm transition-opacity sm:p-8">
                    <div className="flex h-[90vh] w-full max-w-5xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
                        <div className="flex items-center justify-between border-b border-slate-200 bg-slate-50 p-4">
                            <h2 className="flex items-center gap-2 text-lg font-black tracking-tight text-slate-900 uppercase">
                                <svg
                                    className="h-5 w-5 text-red-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                    ></path>
                                </svg>
                                Official Release Logbook
                            </h2>
                            <button
                                onClick={closeLogbookModal}
                                className="text-3xl leading-none font-bold text-slate-400 transition-all hover:text-red-600"
                            >
                                &times;
                            </button>
                        </div>
                        <div className="relative w-full flex-1 bg-slate-200">
                            <iframe
                                name="logbookViewerFrame"
                                id="logbookViewerFrame"
                                className="relative z-10 h-full w-full bg-white"
                            ></iframe>
                        </div>
                        <div className="flex items-center justify-between border-t border-slate-200 bg-slate-50 p-4">
                            <a
                                href={logbookUrl}
                                download
                                className="flex items-center gap-2 rounded-xl bg-red-600 px-6 py-3 text-[10px] font-black tracking-widest text-white uppercase shadow-sm transition-all hover:bg-red-700 active:scale-95"
                            >
                                <svg
                                    className="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                                    ></path>
                                </svg>
                                I-Download ang Logbook
                            </a>
                            <button
                                onClick={closeLogbookModal}
                                className="rounded-xl bg-slate-900 px-8 py-3 text-[10px] font-black tracking-widest text-white uppercase shadow-sm transition-all hover:bg-slate-800 active:scale-95"
                            >
                                Isara
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}
