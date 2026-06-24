import React, { useState, useEffect } from 'react';
import { Head, useForm, usePage, Link, router } from '@inertiajs/react';
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
                    only: [
                        'pendingAccounts',
                        'approvedAccounts',
                        'rejectedAccounts',
                        'activeQueue',
                        'receivedQueue',
                        'auditLogs',
                        'notificationLogs',
                    ],
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
        pendingAccounts = { data: [], links: [] },
        approvedAccounts = { data: [], links: [] },
        rejectedAccounts = { data: [], links: [] },
        activeQueue = { data: [], links: [] },
        receivedQueue = { data: [], links: [] },
        documents = [],
        auditLogs = { data: [], links: [] },
        notificationLogs = { data: [], links: [] },
        auth,
        flash = {},
        errors = {},
    } = usePage().props;

    const [activeTab, setActiveTab] = useState('pending');
    const [pendingSubTab, setPendingSubTab] = useState('sub-pending');
    const [queueSubTab, setQueueSubTab] = useState('queue-active');
    const [auditSubTab, setAuditSubTab] = useState('sub-audit-trail');

    const [imageModal, setImageModal] = useState({ isOpen: false, src: '', title: '' });
    const [deleteModal, setDeleteModal] = useState({ isOpen: false, userId: null });
    const [rejectModal, setRejectModal] = useState({ isOpen: false, userId: null, userName: '' });
    const [statusModal, setStatusModal] = useState({
        isOpen: false,
        requestId: null,
        nextStatus: '',
        label: '',
    });
    const [suspendModal, setSuspendModal] = useState({ isOpen: false, userId: null, userName: '' });
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

    const rejectForm = useForm({ rejection_reason: '' });
    const submitReject = (e) => {
        e.preventDefault();
        rejectForm.post(route('admin.reject_account', rejectModal.userId), {
            preserveScroll: true,
            onSuccess: () => {
                setRejectModal({ isOpen: false, userId: null, userName: '' });
                rejectForm.reset();
            },
        });
    };

    const deleteForm = useForm({});
    const submitDelete = (e) => {
        e.preventDefault();
        deleteForm.delete(route('admin.delete_account', deleteModal.userId), {
            preserveScroll: true,
            onSuccess: () => setDeleteModal({ isOpen: false, userId: null }),
        });
    };

    const suspendForm = useForm({});
    const submitSuspend = (e) => {
        e.preventDefault();
        suspendForm.post(route('admin.suspend_account', suspendModal.userId), {
            preserveScroll: true,
            onSuccess: () => setSuspendModal({ isOpen: false, userId: null, userName: '' }),
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

            {/* --- TAB 1: PENDING REGISTRATIONS --- */}
            {activeTab === 'pending' && (
                <div className="animate-in fade-in duration-500">
                    <div className="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                        <form
                            onSubmit={handleSearchSort}
                            className="flex w-full min-w-0 flex-wrap gap-2"
                        >
                            <input
                                type="text"
                                name="search"
                                defaultValue={searchParam}
                                placeholder="Maghanap ng pangalan o number..."
                                className="min-w-[200px] flex-1 rounded-lg border border-slate-300 px-4 py-2 outline-none focus:ring-2 focus:ring-slate-900 focus:outline-none"
                            />
                            <select
                                name="sort"
                                defaultValue={sortParam}
                                onChange={(e) =>
                                    e.target.form.dispatchEvent(
                                        new Event('submit', { cancelable: true })
                                    )
                                }
                                className="rounded-lg border border-slate-300 bg-slate-50 px-4 py-2 focus:outline-none"
                            >
                                <option value="latest">Pinakabago</option>
                                <option value="oldest">Pinakaluma</option>
                            </select>
                            <button
                                type="submit"
                                className="shrink-0 rounded-lg bg-slate-900 px-6 py-2 font-bold text-white transition-all hover:bg-slate-800 active:scale-95"
                            >
                                Search
                            </button>
                        </form>
                    </div>

                    <div className="mb-6 flex gap-2 overflow-x-auto pb-2">
                        <button
                            onClick={() => setPendingSubTab('sub-pending')}
                            className={`rounded-full px-5 py-2 text-sm font-bold whitespace-nowrap transition-all ${pendingSubTab === 'sub-pending' ? 'bg-slate-900 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300'}`}
                        >
                            Under Review ({pendingAccounts.data?.length || 0})
                        </button>
                        <button
                            onClick={() => setPendingSubTab('sub-approved')}
                            className={`rounded-full px-5 py-2 text-sm font-bold whitespace-nowrap transition-all ${pendingSubTab === 'sub-approved' ? 'bg-slate-900 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300'}`}
                        >
                            Approved ({approvedAccounts.data?.length || 0})
                        </button>
                        <button
                            onClick={() => setPendingSubTab('sub-rejected')}
                            className={`rounded-full px-5 py-2 text-sm font-bold whitespace-nowrap transition-all ${pendingSubTab === 'sub-rejected' ? 'border border-red-300 bg-red-100 text-red-700' : 'bg-slate-200 text-slate-700 hover:bg-red-200 hover:text-red-700'}`}
                        >
                            Rejected / Locked ({rejectedAccounts.data?.length || 0})
                        </button>
                    </div>

                    {pendingSubTab === 'sub-pending' && (
                        <div>
                            <div className="grid grid-cols-1 gap-6 xl:grid-cols-2">
                                {pendingAccounts.data && pendingAccounts.data.length > 0 ? (
                                    pendingAccounts.data.map((user) => (
                                        <div
                                            key={user.id}
                                            className="flex flex-col gap-5 rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-all hover:shadow-md sm:flex-row"
                                        >
                                            <div className="flex shrink-0 gap-2 sm:flex-col">
                                                <div className="group relative">
                                                    <img
                                                        src={route('secure.file', {
                                                            filepath: user.id_photo_path,
                                                        })}
                                                        className="h-20 w-20 cursor-pointer rounded-lg border border-slate-200 object-cover transition-all group-hover:opacity-75 sm:h-24 sm:w-24"
                                                        onClick={() =>
                                                            setImageModal({
                                                                isOpen: true,
                                                                src: route('secure.file', {
                                                                    filepath: user.id_photo_path,
                                                                }),
                                                                title: 'Valid ID',
                                                            })
                                                        }
                                                    />
                                                    <span className="absolute right-1 bottom-1 rounded bg-slate-900/60 px-1 text-[8px] font-bold text-white">
                                                        ID
                                                    </span>
                                                </div>
                                                <div className="group relative">
                                                    <img
                                                        src={route('secure.file', {
                                                            filepath: user.selfie_photo_path,
                                                        })}
                                                        className="h-20 w-20 cursor-pointer rounded-lg border border-slate-200 object-cover transition-all group-hover:opacity-75 sm:h-24 sm:w-24"
                                                        onClick={() =>
                                                            setImageModal({
                                                                isOpen: true,
                                                                src: route('secure.file', {
                                                                    filepath:
                                                                        user.selfie_photo_path,
                                                                }),
                                                                title: 'Selfie',
                                                            })
                                                        }
                                                    />
                                                    <span className="absolute right-1 bottom-1 rounded bg-slate-900/60 px-1 text-[8px] font-bold text-white">
                                                        SELFIE
                                                    </span>
                                                </div>
                                            </div>
                                            <div className="flex flex-1 flex-col justify-between">
                                                <div>
                                                    <h3 className="mb-1 text-xl leading-tight font-black tracking-tight text-slate-900 uppercase">
                                                        {user.last_name}, {user.first_name},{' '}
                                                        {user.middle_name}, {user.suffix}
                                                    </h3>
                                                    <p className="mb-3 flex flex-wrap gap-x-2 gap-y-1 text-[11px] font-bold tracking-widest text-slate-500 uppercase">
                                                        <span>{user.sex}</span>
                                                        <span className="text-slate-300">|</span>
                                                        <span>{user.age} YRS OLD</span>
                                                        <span className="text-slate-300">|</span>
                                                        <span>
                                                            DOB:{' '}
                                                            {new Date(
                                                                user.date_of_birth
                                                            ).toLocaleDateString('en-US', {
                                                                month: 'short',
                                                                day: '2-digit',
                                                                year: 'numeric',
                                                            })}
                                                        </span>
                                                        <span className="text-slate-300">|</span>
                                                        <span className="font-mono text-slate-900">
                                                            {user.contact_number}
                                                        </span>
                                                    </p>
                                                </div>
                                                <div className="flex gap-2">
                                                    <Link
                                                        href={route(
                                                            'admin.approve_account',
                                                            user.id
                                                        )}
                                                        method="post"
                                                        as="button"
                                                        preserveScroll
                                                        className="w-full rounded-lg bg-slate-900 py-2.5 text-[10px] font-black tracking-widest text-white uppercase shadow-sm transition-all hover:bg-slate-800 active:scale-95"
                                                    >
                                                        Approve
                                                    </Link>
                                                    <button
                                                        onClick={() =>
                                                            setRejectModal({
                                                                isOpen: true,
                                                                userId: user.id,
                                                                userName: `${user.first_name} ${user.last_name}`,
                                                            })
                                                        }
                                                        className="flex-1 rounded-lg border border-red-200 bg-red-50 py-2.5 text-[10px] font-black tracking-widest text-red-600 uppercase transition-all hover:bg-red-100 active:scale-95"
                                                    >
                                                        Reject
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    ))
                                ) : (
                                    <p className="col-span-full py-10 text-center font-bold text-slate-500 italic">
                                        Walang pending registrations.
                                    </p>
                                )}
                            </div>
                            <Pagination links={pendingAccounts.links} />
                        </div>
                    )}

                    {pendingSubTab === 'sub-approved' && (
                        <div>
                            <div className="space-y-3">
                                {approvedAccounts.data && approvedAccounts.data.length > 0 ? (
                                    approvedAccounts.data.map((user) => (
                                        <details
                                            key={user.id}
                                            className="group overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
                                        >
                                            <summary className="flex cursor-pointer list-none items-center justify-between p-4 transition-all hover:bg-slate-50">
                                                <div className="flex items-center gap-3">
                                                    <div className="h-2 w-2 rounded-full bg-green-500"></div>
                                                    <span className="text-sm font-black text-slate-900 uppercase">
                                                        {user.last_name}, {user.first_name}
                                                    </span>
                                                </div>
                                                <div className="flex items-center gap-4">
                                                    <span className="text-[10px] font-bold tracking-widest text-slate-400 uppercase">
                                                        {user.contact_number}
                                                    </span>
                                                    <svg
                                                        className="h-5 w-5 text-slate-400 transition-transform group-open:rotate-180"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path
                                                            strokeLinecap="round"
                                                            strokeLinejoin="round"
                                                            strokeWidth="2"
                                                            d="M19 9l-7 7-7-7"
                                                        ></path>
                                                    </svg>
                                                </div>
                                            </summary>
                                            <div className="grid grid-cols-1 gap-6 border-t border-slate-50 bg-slate-50/30 p-5 text-xs md:grid-cols-2 lg:grid-cols-3">
                                                <div>
                                                    <p className="mb-1 font-black tracking-widest text-slate-400 uppercase">
                                                        Contact Details
                                                    </p>
                                                    <p className="font-bold text-slate-900">
                                                        {user.contact_number}
                                                    </p>
                                                    <p className="font-medium text-slate-500">
                                                        {user.email || 'Walang email na nilagay.'}
                                                    </p>
                                                </div>
                                                <div>
                                                    <p className="mb-1 font-black tracking-widest text-slate-400 uppercase">
                                                        Personal Info
                                                    </p>
                                                    <p className="font-bold text-slate-900 uppercase">
                                                        {user.sex} | {user.age} YRS OLD
                                                    </p>
                                                    <p className="font-medium text-slate-500 italic">
                                                        DOB:{' '}
                                                        {new Date(
                                                            user.date_of_birth
                                                        ).toLocaleDateString('en-US', {
                                                            month: 'short',
                                                            day: '2-digit',
                                                            year: 'numeric',
                                                        })}
                                                    </p>
                                                </div>
                                                <div>
                                                    <p className="mb-1 font-black tracking-widest text-slate-400 uppercase">
                                                        Address
                                                    </p>
                                                    <p className="font-bold text-slate-900 uppercase">
                                                        {user.house_number} {user.purok_street}
                                                    </p>
                                                </div>
                                                <div className="flex items-center justify-between border-t border-slate-100 pt-2 lg:col-span-3">
                                                    <p className="text-[9px] font-bold tracking-widest text-slate-400 uppercase">
                                                        Terms Accepted:{' '}
                                                        {user.terms_accepted_at
                                                            ? new Date(
                                                                  user.terms_accepted_at
                                                              ).toLocaleString()
                                                            : 'N/A'}
                                                    </p>
                                                    <div className="flex items-center gap-3">
                                                        <div className="flex gap-2">
                                                            <img
                                                                src={route('secure.file', {
                                                                    filepath: user.id_photo_path,
                                                                })}
                                                                className="h-20 w-20 cursor-pointer rounded-lg border border-slate-200 object-cover transition-all group-hover:opacity-75 sm:h-24 sm:w-24"
                                                                onClick={() =>
                                                                    setImageModal({
                                                                        isOpen: true,
                                                                        src: route('secure.file', {
                                                                            filepath:
                                                                                user.id_photo_path,
                                                                        }),
                                                                        title: 'Valid ID',
                                                                    })
                                                                }
                                                            />
                                                            <img
                                                                src={route('secure.file', {
                                                                    filepath:
                                                                        user.selfie_photo_path,
                                                                })}
                                                                className="h-20 w-20 cursor-pointer rounded-lg border border-slate-200 object-cover transition-all group-hover:opacity-75 sm:h-24 sm:w-24"
                                                                onClick={() =>
                                                                    setImageModal({
                                                                        isOpen: true,
                                                                        src: route('secure.file', {
                                                                            filepath:
                                                                                user.selfie_photo_path,
                                                                        }),
                                                                        title: 'Selfie',
                                                                    })
                                                                }
                                                            />
                                                        </div>
                                                        <div className="flex items-center gap-2 border-l border-slate-200 pl-3">
                                                            <button
                                                                onClick={() =>
                                                                    setSuspendModal({
                                                                        isOpen: true,
                                                                        userId: user.id,
                                                                        userName: `${user.first_name} ${user.last_name}`,
                                                                    })
                                                                }
                                                                className="rounded border border-amber-200 bg-amber-50 px-3 py-1.5 text-[9px] font-black tracking-widest text-amber-600 uppercase shadow-sm transition-all hover:bg-amber-100 active:scale-95"
                                                            >
                                                                Suspend
                                                            </button>
                                                            <button
                                                                onClick={() =>
                                                                    setDeleteModal({
                                                                        isOpen: true,
                                                                        userId: user.id,
                                                                    })
                                                                }
                                                                className="flex items-center gap-1 rounded border border-red-200 bg-red-50 px-3 py-1.5 text-[9px] font-black tracking-widest text-red-600 uppercase shadow-sm transition-all hover:bg-red-100 active:scale-95"
                                                            >
                                                                <svg
                                                                    className="h-3 w-3"
                                                                    fill="none"
                                                                    stroke="currentColor"
                                                                    viewBox="0 0 24 24"
                                                                >
                                                                    <path
                                                                        strokeLinecap="round"
                                                                        strokeLinejoin="round"
                                                                        strokeWidth="2"
                                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                                    ></path>
                                                                </svg>
                                                                Delete
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </details>
                                    ))
                                ) : (
                                    <p className="py-10 text-center font-bold text-slate-500 italic">
                                        Walang approved accounts.
                                    </p>
                                )}
                            </div>
                            <Pagination links={approvedAccounts.links} />
                        </div>
                    )}

                    {pendingSubTab === 'sub-rejected' && (
                        <div>
                            <div className="grid grid-cols-1 gap-6 xl:grid-cols-2">
                                {rejectedAccounts.data && rejectedAccounts.data.length > 0 ? (
                                    rejectedAccounts.data.map((user) => (
                                        <div
                                            key={user.id}
                                            className="flex flex-col justify-between rounded-xl border border-l-4 border-slate-200 border-l-red-500 bg-white p-5 shadow-sm"
                                        >
                                            <div>
                                                <div className="mb-4 flex items-start justify-between">
                                                    <div>
                                                        <h3 className="text-lg leading-tight font-black tracking-tight text-slate-900 uppercase">
                                                            {user.last_name}, {user.first_name}
                                                        </h3>
                                                        <p className="text-[10px] font-bold tracking-widest text-slate-500 uppercase">
                                                            {user.contact_number} | Attempts:{' '}
                                                            {user.rejection_count}/5
                                                        </p>
                                                    </div>
                                                    <span className="rounded bg-red-100 px-2 py-1 text-[9px] font-black tracking-widest text-red-700 uppercase shadow-sm">
                                                        Rejected
                                                    </span>
                                                </div>
                                                <div className="mb-4 rounded-lg border border-red-100 bg-red-50 p-3">
                                                    <p className="mb-1 text-[9px] font-black tracking-widest text-red-400 uppercase italic">
                                                        Rason ng Rejection
                                                    </p>
                                                    <p className="text-xs leading-snug font-bold text-red-700">
                                                        {user.rejection_reason}
                                                    </p>
                                                </div>
                                            </div>
                                            <button
                                                onClick={() =>
                                                    setDeleteModal({
                                                        isOpen: true,
                                                        userId: user.id,
                                                    })
                                                }
                                                className="w-full rounded-lg bg-red-600 py-2.5 text-[10px] font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-red-700 active:scale-95"
                                            >
                                                Delete Account
                                            </button>
                                        </div>
                                    ))
                                ) : (
                                    <p className="col-span-full py-10 text-center font-bold text-slate-500 italic">
                                        Walang rejected accounts.
                                    </p>
                                )}
                            </div>
                            <Pagination links={rejectedAccounts.links} />
                        </div>
                    )}
                </div>
            )}

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
                    <div className="mt-6 mb-6 flex gap-2 overflow-x-auto pb-2">
                        <button
                            onClick={() => setAuditSubTab('sub-audit-trail')}
                            className={`rounded-full px-5 py-2 text-sm font-bold whitespace-nowrap transition-all ${auditSubTab === 'sub-audit-trail' ? 'border border-slate-900 bg-slate-900 text-white' : 'border border-transparent bg-slate-200 text-slate-700 hover:bg-slate-300'}`}
                        >
                            System Audit Trail
                        </button>
                        <button
                            onClick={() => setAuditSubTab('sub-notif-history')}
                            className={`rounded-full px-5 py-2 text-sm font-bold whitespace-nowrap transition-all ${auditSubTab === 'sub-notif-history' ? 'border border-slate-900 bg-slate-900 text-white' : 'border border-transparent bg-slate-200 text-slate-700 hover:bg-slate-300'}`}
                        >
                            Notification History
                        </button>
                        <button
                            onClick={() => setAuditSubTab('sub-generate-pdf')}
                            className={`rounded-full px-5 py-2 text-sm font-bold whitespace-nowrap transition-all ${auditSubTab === 'sub-generate-pdf' ? 'border border-slate-900 bg-slate-900 text-white' : 'border border-transparent bg-slate-200 text-slate-700 hover:bg-slate-300'}`}
                        >
                            Generate Analytics
                        </button>
                    </div>

                    {auditSubTab === 'sub-audit-trail' && (
                        <div>
                            <div className="block overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                                <div className="border-b border-slate-200 bg-slate-50 p-6">
                                    <h2 className="text-xl font-black tracking-tight text-slate-900 uppercase">
                                        System Audit Logs
                                    </h2>
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
                    )}

                    {auditSubTab === 'sub-notif-history' && (
                        <div>
                            <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                                <div className="border-b border-slate-200 bg-slate-50 p-6">
                                    <h2 className="text-xl font-black tracking-tight text-slate-900 uppercase">
                                        Notification History
                                    </h2>
                                </div>
                                <div className="max-h-[600px] overflow-x-auto overflow-y-auto">
                                    <table className="relative w-full border-collapse text-left">
                                        <thead className="sticky top-0 z-10">
                                            <tr className="border-b border-slate-200 bg-slate-100 text-[10px] tracking-[0.15em] text-slate-500 uppercase">
                                                <th className="p-4 font-black">Petsa & Oras</th>
                                                <th className="p-4 font-black">Residente</th>
                                                <th className="p-4 font-black">Channel</th>
                                                <th className="w-2/5 p-4 font-black">Mensahe</th>
                                                <th className="p-4 text-right font-black">
                                                    Status
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-slate-100">
                                            {notificationLogs.data &&
                                            notificationLogs.data.length > 0 ? (
                                                notificationLogs.data.map((notif) => (
                                                    <tr
                                                        key={notif.id}
                                                        className="transition-colors hover:bg-slate-50"
                                                    >
                                                        <td className="p-4 text-xs font-bold text-slate-600">
                                                            {new Date(
                                                                notif.created_at
                                                            ).toLocaleString()}
                                                        </td>
                                                        <td className="p-4">
                                                            <p className="text-xs font-bold text-slate-900 uppercase">
                                                                {notif.user?.first_name || 'N/A'}{' '}
                                                                {notif.user?.last_name || ''}
                                                            </p>
                                                            <p className="font-mono text-[9px] text-slate-500">
                                                                {notif.recipient_contact}
                                                            </p>
                                                        </td>
                                                        <td className="p-4">
                                                            <span
                                                                className={`rounded border px-2 py-1 text-[9px] font-black tracking-widest uppercase shadow-sm ${notif.channel?.toLowerCase() === 'sms' ? 'border-blue-200 bg-blue-100 text-blue-700' : 'border-purple-200 bg-purple-100 text-purple-700'}`}
                                                            >
                                                                {notif.channel?.toUpperCase()}
                                                            </span>
                                                        </td>
                                                        <td
                                                            className="line-clamp-2 p-4 text-[11px] font-medium text-slate-600"
                                                            title={notif.message_content}
                                                        >
                                                            {notif.message_content}
                                                        </td>
                                                        <td className="p-4 text-right">
                                                            <span
                                                                className={`rounded px-2 py-1 text-[9px] font-black tracking-widest uppercase ${notif.status?.toLowerCase().includes('sent') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`}
                                                                title={notif.provider_response}
                                                            >
                                                                {notif.status}
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
                                                        Walang record ng notifications.
                                                    </td>
                                                </tr>
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <Pagination links={notificationLogs.links} />
                        </div>
                    )}

                    {auditSubTab === 'sub-generate-pdf' && (
                        <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <div className="border-b border-slate-200 bg-slate-50 p-6">
                                <h2 className="text-xl font-black tracking-tight text-slate-900 uppercase">
                                    Generate System Analytics
                                </h2>
                            </div>
                            <div className="mx-auto my-8 max-w-2xl p-8">
                                {/* BUG FIX 3: SPA Form Pointing to Iframe with proper React timing */}
                                <form
                                    method="POST"
                                    action={route('admin.reports.generate')}
                                    target="pdfViewerFrame"
                                    onSubmit={submitGeneratePdf}
                                    className="flex flex-col gap-6"
                                >
                                    <input
                                        type="hidden"
                                        name="_token"
                                        value={usePage().props.csrf_token}
                                    />
                                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div className="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                            <label className="mb-2 block text-[10px] font-black tracking-widest text-slate-400 uppercase">
                                                Piliin ang Buwan{' '}
                                                <span className="text-red-500">*</span>
                                            </label>
                                            <select
                                                name="report_month"
                                                value={reportMonth}
                                                onChange={(e) => setReportMonth(e.target.value)}
                                                required
                                                className="w-full cursor-pointer rounded-lg border border-slate-300 bg-white px-4 py-2.5 font-bold text-slate-700 outline-none focus:ring-2 focus:ring-slate-900"
                                            >
                                                <option value="all">Buong Taon (All Months)</option>
                                                {[
                                                    'January',
                                                    'February',
                                                    'March',
                                                    'April',
                                                    'May',
                                                    'June',
                                                    'July',
                                                    'August',
                                                    'September',
                                                    'October',
                                                    'November',
                                                    'December',
                                                ].map((m, i) => (
                                                    <option
                                                        key={i}
                                                        value={(i + 1).toString().padStart(2, '0')}
                                                    >
                                                        {m}
                                                    </option>
                                                ))}
                                            </select>
                                        </div>
                                        <div className="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                            <label className="mb-2 block text-[10px] font-black tracking-widest text-slate-400 uppercase">
                                                Piliin ang Taon{' '}
                                                <span className="text-red-500">*</span>
                                            </label>
                                            <select
                                                name="report_year"
                                                value={reportYear}
                                                onChange={(e) => setReportYear(e.target.value)}
                                                required
                                                className="w-full cursor-pointer rounded-lg border border-slate-300 bg-white px-4 py-2.5 font-bold text-slate-700 outline-none focus:ring-2 focus:ring-slate-900"
                                            >
                                                {Array.from(
                                                    { length: new Date().getFullYear() - 2023 },
                                                    (_, i) => new Date().getFullYear() - i
                                                ).map((y) => (
                                                    <option key={y} value={y}>
                                                        {y}
                                                    </option>
                                                ))}
                                            </select>
                                        </div>
                                    </div>
                                    <button
                                        type="submit"
                                        className="flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 py-4 text-xs font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-slate-800 active:scale-95"
                                    >
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
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                            ></path>
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                            ></path>
                                        </svg>
                                        Tingnan ang Analytics (In-App)
                                    </button>
                                </form>
                            </div>
                        </div>
                    )}
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
            {/* Image Modal */}
            {imageModal.isOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/90 p-4 backdrop-blur-sm">
                    <div className="relative w-full max-w-4xl overflow-hidden rounded-xl bg-white shadow-2xl">
                        <div className="flex items-center justify-between border-b border-slate-200 bg-slate-50 p-4">
                            <h3 className="text-lg font-black tracking-tighter text-slate-900 uppercase">
                                {imageModal.title}
                            </h3>
                            <button
                                onClick={() => setImageModal({ isOpen: false, src: '', title: '' })}
                                className="text-2xl leading-none font-bold text-slate-400 transition-all hover:text-red-600"
                            >
                                &times;
                            </button>
                        </div>
                        <div className="flex justify-center bg-slate-200 p-4">
                            <img
                                src={imageModal.src}
                                className="max-h-[65vh] rounded object-contain shadow-lg"
                            />
                        </div>
                        <div className="border-t border-slate-200 bg-slate-50 p-4 text-right">
                            <button
                                onClick={() => setImageModal({ isOpen: false, src: '', title: '' })}
                                className="rounded-lg bg-slate-900 px-10 py-3 text-[10px] font-black tracking-widest text-white uppercase transition-all hover:bg-slate-800 active:scale-95"
                            >
                                Isara
                            </button>
                        </div>
                    </div>
                </div>
            )}

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

            {/* Reject Account Modal */}
            {rejectModal.isOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/80 p-4 backdrop-blur-sm">
                    <div className="w-full max-w-md transform overflow-hidden rounded-2xl border border-red-100 bg-white shadow-2xl transition-all">
                        <div className="flex items-center justify-between border-b border-red-100 bg-red-50 p-4 text-red-700">
                            <h3 className="flex items-center gap-2 text-lg font-black tracking-tight uppercase">
                                <svg
                                    className="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="3"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                                    ></path>
                                </svg>
                                Reject Registration
                            </h3>
                            <button
                                onClick={() =>
                                    setRejectModal({ isOpen: false, userId: null, userName: '' })
                                }
                                className="text-2xl font-bold text-red-300 transition-all hover:text-red-700"
                            >
                                &times;
                            </button>
                        </div>
                        <form onSubmit={submitReject}>
                            <div className="p-6">
                                <p className="mb-4 text-sm font-medium text-slate-600">
                                    Ita-type mo ang rason kung bakit rejected si{' '}
                                    <strong className="text-slate-900">
                                        {rejectModal.userName}
                                    </strong>
                                    . Ipapadala ito via SMS.
                                </p>
                                <label className="mb-2 block text-[10px] font-black tracking-[0.2em] text-slate-400 uppercase">
                                    Rason ng Rejection
                                </label>
                                <input
                                    type="text"
                                    value={rejectForm.data.rejection_reason}
                                    onChange={(e) =>
                                        rejectForm.setData('rejection_reason', e.target.value)
                                    }
                                    maxLength="60"
                                    required
                                    placeholder="Hal: Malabo ang ID, paki-upload ng maayos."
                                    className="w-full rounded-xl border-2 border-slate-100 p-3 font-bold text-slate-900 placeholder-slate-300 transition-all outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500"
                                />
                                <p className="mt-2 text-right text-[9px] font-black text-slate-400 italic">
                                    Limit: 60 characters
                                </p>
                            </div>
                            <div className="flex gap-3 border-t border-slate-100 bg-slate-50 p-4">
                                <button
                                    type="button"
                                    onClick={() =>
                                        setRejectModal({
                                            isOpen: false,
                                            userId: null,
                                            userName: '',
                                        })
                                    }
                                    className="flex-1 rounded-xl border border-slate-200 bg-white py-3 text-[10px] font-black tracking-widest text-slate-500 uppercase transition-all hover:bg-slate-100"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    disabled={rejectForm.processing}
                                    className="flex-1 rounded-xl bg-red-600 py-3 text-[10px] font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-red-700 active:scale-95 disabled:opacity-50"
                                >
                                    Reject
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Suspend Account Modal */}
            {suspendModal.isOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/80 p-4 backdrop-blur-sm transition-opacity">
                    <div className="w-full max-w-sm transform overflow-hidden rounded-2xl border border-amber-100 bg-white p-6 shadow-2xl transition-all">
                        <div className="mb-6 flex flex-col items-center text-center">
                            <div className="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-amber-100 text-amber-600">
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
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                    ></path>
                                </svg>
                            </div>
                            <h3 className="text-xl font-black tracking-tight text-slate-900 uppercase">
                                Suspend Account?
                            </h3>
                            <p className="mt-2 px-4 text-sm font-medium text-slate-500">
                                Sigurado ka bang gusto mong patawan ng 7-araw na penalty si{' '}
                                <strong className="text-slate-900">{suspendModal.userName}</strong>?
                            </p>
                        </div>
                        <form onSubmit={submitSuspend}>
                            <div className="flex flex-col gap-3">
                                <button
                                    type="submit"
                                    disabled={suspendForm.processing}
                                    className="w-full rounded-xl bg-amber-600 py-3 text-xs font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-amber-700 active:scale-95 disabled:opacity-50"
                                >
                                    Ipataw ang Penalty
                                </button>
                                <button
                                    type="button"
                                    onClick={() =>
                                        setSuspendModal({
                                            isOpen: false,
                                            userId: null,
                                            userName: '',
                                        })
                                    }
                                    className="w-full py-2 text-[10px] font-black tracking-widest text-slate-400 uppercase transition-all hover:text-slate-600"
                                >
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Delete Account Modal */}
            {deleteModal.isOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/80 p-4 backdrop-blur-sm">
                    <div className="w-full max-w-sm transform overflow-hidden rounded-2xl border border-red-100 bg-white p-6 shadow-2xl transition-all">
                        <div className="mb-6 flex flex-col items-center text-center">
                            <div className="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-red-100 text-red-600">
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
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                    ></path>
                                </svg>
                            </div>
                            <h3 className="text-xl font-black tracking-tight text-slate-900 uppercase">
                                Delete Account?
                            </h3>
                            <p className="mt-2 px-4 text-sm font-medium text-slate-500">
                                Warning: Ang lahat ng data pati litrato ay permanenteng mawawala.
                                Hindi na ito maibabalik.
                            </p>
                        </div>
                        <form onSubmit={submitDelete}>
                            <div className="flex flex-col gap-3">
                                <button
                                    type="submit"
                                    disabled={deleteForm.processing}
                                    className="w-full rounded-xl bg-red-600 py-3 text-xs font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-red-700 active:scale-95 disabled:opacity-50"
                                >
                                    Delete Permanently
                                </button>
                                <button
                                    type="button"
                                    onClick={() => setDeleteModal({ isOpen: false, userId: null })}
                                    className="w-full py-2 text-[10px] font-black tracking-widest text-slate-400 uppercase transition-all hover:text-slate-600"
                                >
                                    Cancel
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
